<?php

namespace NetBull\CoreBundle\Tests\Form\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use NetBull\CoreBundle\Form\Type\UnorderedCollectionType;
use NetBull\CoreBundle\Tests\Fixtures\Item;
use NetBull\CoreBundle\Tests\Fixtures\ItemDtoType;
use NetBull\CoreBundle\Tests\Fixtures\ItemType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

class ResizeFormListenerTest extends TestCase
{
    private FormFactoryInterface $factory;

    protected function setUp(): void
    {
        $this->factory = Forms::createFormFactory();
    }

    private function createCollectionForm(mixed $data = [], array $options = []): FormInterface
    {
        return $this->factory->create(UnorderedCollectionType::class, $data, $options + [
            'entry_type' => ItemType::class,
            'allow_add' => true,
            'allow_delete' => true,
        ]);
    }

    // ---------------------------------------------------------------- preSetData

    /**
     * preSetData counterpart of the falsy-id fix: initial items without an id
     * used to produce a broken form state (child added under a null name, data
     * entry dropped).
     */
    public function testInitialDataWithoutIdGetsSyntheticKey(): void
    {
        $form = $this->createCollectionForm([
            ['id' => '7', 'label' => 'persisted'],
            ['label' => 'unsaved'],
        ]);

        $this->assertTrue($form->has('7'));
        $this->assertTrue($form->has('__new_0'));
        $this->assertSame('unsaved', $form->get('__new_0')->getData()['label']);
        $this->assertSame([7, '__new_0'], array_keys($form->getData()));
    }

    public function testInitialDataAsTraversableCollectionOfObjectsIsKeyedByPropertyValue(): void
    {
        $form = $this->createCollectionForm(
            new ArrayCollection([new Item('3', 'three'), new Item(null, 'unsaved')]),
            ['entry_type' => ItemDtoType::class, 'data_class' => null],
        );

        $this->assertTrue($form->has('3'));
        $this->assertTrue($form->has('__new_0'));
        $this->assertSame([3, '__new_0'], array_keys($form->getData()));
    }

    public function testInitialDataOfInvalidTypeThrows(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->createCollectionForm('not-a-collection');
    }

    // ------------------------------------------------- preSubmit: synthetic keys

    /**
     * Regression test: items submitted without an "id" used to be silently
     * dropped by preSubmit's falsy-property filter. They must be kept as new
     * entries under synthetic "__new_N" keys.
     */
    public function testSubmittedItemsWithoutIdAreKeptAsNewEntries(): void
    {
        $form = $this->createCollectionForm([
            ['id' => '11', 'label' => 'existing'],
        ]);

        $form->submit([
            ['id' => '11', 'label' => 'existing updated'],
            ['label' => 'added without id'],
            ['id' => 'new_temp-1-Ab3', 'label' => 'added with temp id'],
        ]);

        $this->assertTrue($form->isValid());

        $data = $form->getData();
        $this->assertCount(3, $data);
        $this->assertSame([11, '__new_0', 'new_temp-1-Ab3'], array_keys($data));
        $this->assertSame('existing updated', $data[11]['label']);
        $this->assertSame('added without id', $data['__new_0']['label']);
        $this->assertSame('added with temp id', $data['new_temp-1-Ab3']['label']);
    }

    public function testMultipleIdLessItemsGetDistinctSyntheticKeys(): void
    {
        $form = $this->createCollectionForm();

        $form->submit([
            ['label' => 'first'],
            ['label' => 'second'],
        ]);

        $this->assertTrue($form->isValid());

        $data = $form->getData();
        $this->assertSame(['__new_0', '__new_1'], array_keys($data));
        $this->assertSame('first', $data['__new_0']['label']);
        $this->assertSame('second', $data['__new_1']['label']);
    }

    public function testSyntheticKeyDoesNotLeakIntoTheMappedIdField(): void
    {
        $form = $this->createCollectionForm();

        $form->submit([
            ['label' => 'no id at all'],
        ]);

        $this->assertNull($form->getData()['__new_0']['id']);
    }

    public function testFalsyIdsAreTreatedAsNewEntries(): void
    {
        $form = $this->createCollectionForm();

        $form->submit([
            ['id' => '', 'label' => 'empty string id'],
            ['id' => '0', 'label' => 'zero id'],
        ]);

        $this->assertTrue($form->isValid());

        $data = $form->getData();
        $this->assertSame(['__new_0', '__new_1'], array_keys($data));
        $this->assertSame('empty string id', $data['__new_0']['label']);
        $this->assertSame('zero id', $data['__new_1']['label']);
    }

    // ---------------------------------------------------- preSubmit: id matching

    public function testExistingEntriesAreMatchedByIdAndMissingOnesRemoved(): void
    {
        $form = $this->createCollectionForm([
            ['id' => '1', 'label' => 'one'],
            ['id' => '2', 'label' => 'two'],
        ]);

        $form->submit([
            ['id' => '2', 'label' => 'two updated'],
        ]);

        $this->assertTrue($form->isValid());

        $data = $form->getData();
        $this->assertCount(1, $data);
        $this->assertSame('two updated', $data[2]['label']);
    }

    public function testIntAndStringIdsMatchTheSameEntry(): void
    {
        $form = $this->createCollectionForm([
            ['id' => '5', 'label' => 'five'],
        ]);

        $form->submit([
            ['id' => 5, 'label' => 'five updated'],
        ]);

        $this->assertTrue($form->isValid());

        $data = $form->getData();
        $this->assertCount(1, $data);
        $this->assertSame('five updated', $data[5]['label']);
    }

    public function testSubmittingEmptyDataRemovesAllEntries(): void
    {
        $form = $this->createCollectionForm([
            ['id' => '1', 'label' => 'one'],
            ['id' => '2', 'label' => 'two'],
        ]);

        $form->submit([]);

        $this->assertTrue($form->isValid());
        $this->assertSame([], $form->getData());
    }

    // ----------------------------------------------------- preSubmit: duplicates

    /**
     * Duplicate ids used to silently collapse last-wins. They must now fail
     * validation instead of losing data.
     */
    public function testDuplicateIdsFailValidation(): void
    {
        $form = $this->createCollectionForm();

        $form->submit([
            ['id' => 'new_a', 'label' => 'first'],
            ['id' => 'new_a', 'label' => 'second'],
        ]);

        $this->assertFalse($form->isValid());

        $errors = iterator_to_array($form->getErrors(true));
        $this->assertCount(1, $errors);
        $this->assertSame(
            'Duplicate "id" value "new_a" submitted in the collection.',
            $errors[0]->getMessage(),
        );

        $data = $form->getData();
        $this->assertCount(1, $data);
        $this->assertSame('first', $data['new_a']['label']);
    }

    public function testDuplicateDetectionTreatsIntAndStringIdsAsTheSameKey(): void
    {
        $form = $this->createCollectionForm();

        $form->submit([
            ['id' => 5, 'label' => 'first'],
            ['id' => '5', 'label' => 'second'],
        ]);

        $this->assertFalse($form->isValid());

        $data = $form->getData();
        $this->assertCount(1, $data);
        $this->assertSame('first', $data[5]['label']);
    }

    /**
     * The app relies on the duplicate error bubbling to the root form so the
     * controller's !isValid() check turns it into a 400 response.
     */
    public function testDuplicateErrorBubblesToTheRootForm(): void
    {
        $root = $this->factory->create(FormType::class, ['items' => []]);
        $root->add('items', UnorderedCollectionType::class, [
            'entry_type' => ItemType::class,
            'allow_add' => true,
            'allow_delete' => true,
        ]);

        $root->submit([
            'items' => [
                ['id' => 'dup', 'label' => 'a'],
                ['id' => 'dup', 'label' => 'b'],
            ],
        ]);

        $this->assertFalse($root->isValid());

        $rootErrors = iterator_to_array($root->getErrors(false));
        $this->assertCount(1, $rootErrors);
        $this->assertSame(
            'Duplicate "id" value "dup" submitted in the collection.',
            $rootErrors[0]->getMessage(),
        );
    }

    // ------------------------------------------------ allow_add / allow_delete

    /**
     * With allow_add=false (TripType "stops" in the app) a new item must not
     * become an entry; it surfaces as extra data and fails validation instead
     * of being silently dropped.
     */
    public function testNewItemsWithoutAllowAddBecomeExtraDataAndFailValidation(): void
    {
        $factory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory();

        $form = $factory->create(UnorderedCollectionType::class, [['id' => '1', 'label' => 'one']], [
            'entry_type' => ItemType::class,
            'allow_add' => false,
            'allow_delete' => true,
        ]);

        $form->submit([
            ['id' => '1', 'label' => 'one updated'],
            ['label' => 'ghost'],
        ]);

        $this->assertArrayHasKey('__new_0', $form->getExtraData());
        $this->assertFalse($form->isValid());

        $data = $form->getData();
        $this->assertCount(1, $data);
        $this->assertSame('one updated', $data[1]['label']);
    }

    public function testEntriesMissingFromSubmitAreKeptWithoutAllowDelete(): void
    {
        $form = $this->createCollectionForm(
            [['id' => '1', 'label' => 'one'], ['id' => '2', 'label' => 'two']],
            ['allow_delete' => false],
        );

        $form->submit([
            ['id' => '2', 'label' => 'two updated'],
        ]);

        $data = $form->getData();
        $this->assertCount(2, $data);
        $this->assertArrayHasKey(1, $data);
        $this->assertSame('two updated', $data[2]['label']);
    }

    // ------------------------------------------------------ onSubmit: delete_empty

    /**
     * delete_empty needs a callable for compound entry types (same constraint
     * as core CollectionType — Form::isEmpty() never reports a compound entry
     * with array/object data as empty).
     */
    public function testDeleteEmptyCallableRemovesEmptyNewEntries(): void
    {
        $form = $this->createCollectionForm([], [
            'delete_empty' => static fn (?array $item): bool => null === ($item['label'] ?? null),
        ]);

        $form->submit([
            ['label' => 'keep'],
            ['id' => null, 'label' => null],
        ]);

        $this->assertTrue($form->isValid());

        $data = $form->getData();
        $this->assertSame(['__new_0'], array_keys($data));
        $this->assertSame('keep', $data['__new_0']['label']);
    }

    public function testDeleteEmptyAcceptsACustomCallable(): void
    {
        $form = $this->createCollectionForm([], [
            'delete_empty' => static fn (?array $item): bool => null === ($item['label'] ?? null),
        ]);

        $form->submit([
            ['id' => 'x', 'label' => null],
            ['id' => 'y', 'label' => 'kept'],
        ]);

        $this->assertTrue($form->isValid());

        $data = $form->getData();
        $this->assertSame(['y'], array_keys($data));
        $this->assertSame('kept', $data['y']['label']);
    }

    // ------------------------------------------------------------- object entries

    /**
     * Mirrors the Doctrine entity usage: existing objects are updated in
     * place, new entries are instantiated from the entry type's data_class.
     */
    public function testObjectEntriesAreUpdatedInPlaceAndNewOnesInstantiated(): void
    {
        $existing = new Item('9', 'nine');

        $form = $this->createCollectionForm([$existing], ['entry_type' => ItemDtoType::class]);

        $form->submit([
            ['id' => '9', 'label' => 'nine updated'],
            ['label' => 'fresh'],
        ]);

        $this->assertTrue($form->isValid());

        $data = $form->getData();
        $this->assertCount(2, $data);
        $this->assertSame($existing, $data[9]);
        $this->assertSame('nine updated', $existing->getLabel());
        $this->assertInstanceOf(Item::class, $data['__new_0']);
        $this->assertSame('fresh', $data['__new_0']->getLabel());
        $this->assertNull($data['__new_0']->getId());
    }
}
