<?php

namespace NetBull\CoreBundle\Form\EventListener;

use ArrayAccess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Traversable;

class ResizeFormListener implements EventSubscriberInterface
{
	/**
	 * @var string
	 */
    protected string $property;

	/**
	 * @var string
	 */
    protected string $type;

	/**
	 * @var array
	 */
    protected array $options;

	/**
	 * @var bool
	 */
    protected bool $allowAdd;

	/**
	 * @var bool
	 */
    protected bool $allowDelete;

	/**
	 * @var bool|callable
	 */
    private $deleteEmpty;

	/**
	 * @var PropertyAccessor
	 */
	private PropertyAccessor $propertyAccessor;

    /**
     * @param string $property
     * @param string $type
     * @param array $options
     * @param bool $allowAdd
     * @param bool $allowDelete
     * @param bool|callable $deleteEmpty
     */
    public function __construct(string $property, string $type, array $options = [], bool $allowAdd = false, bool $allowDelete = false, $deleteEmpty = false)
    {
        $this->property = $property;
        $this->type = $type;
        $this->allowAdd = $allowAdd;
        $this->allowDelete = $allowDelete;
        $this->options = $options;
        $this->deleteEmpty = $deleteEmpty;
		$this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

	/**s
	 * @return array
	 */
    public static function getSubscribedEvents(): array
	{
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
            FormEvents::SUBMIT => ['onSubmit', 50],
        ];
    }

	/**
	 * @param FormEvent $event
	 * @return void
	 */
    public function preSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = [];
        }

        if (!is_array($data) && !($data instanceof Traversable && $data instanceof ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        // First remove all rows
        foreach ($form as $name => $child) {
            $form->remove($name);
        }

        // Then add all rows again in the correct order
        foreach ($data as $value) {
			$name = $this->getPropertyValue($value);
			$form->add($name, $this->type, array_replace([
                'property_path' => '['.$name.']',
            ], $this->options));
        }

		$transformedData = [];
		foreach ($data as $item) {
			if ($name = $this->getPropertyValue($item)) {
				$transformedData[$name] = $item;
			}
		}
		$event->setData($transformedData);
    }

	/**
	 * @param FormEvent $event
	 * @return void
	 */
    public function preSubmit(FormEvent $event): void
    {
		$form = $event->getForm();
		$data = $event->getData();

        if (!is_array($data)) {
            $data = [];
        }

		$transformedData = [];
		foreach ($data as $item) {
			if ($name = $this->getPropertyValue($item)) {
				$transformedData[$name] = $item;
			}
		}

        // Remove all empty rows
        if ($this->allowDelete) {
            foreach ($form as $name => $child) {
                if (!isset($transformedData[$name])) {
                    $form->remove($name);
                }
            }
        }

        // Add all additional rows
        if ($this->allowAdd) {
            foreach ($transformedData as $name => $value) {
                if (!$form->has($name)) {
                    $form->add($name, $this->type, array_replace([
                        'property_path' => '['.$name.']',
                    ], $this->options));
                }
            }
        }

		$event->setData($transformedData);
    }

	/**
	 * @param FormEvent $event
	 * @return void
	 */
    public function onSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        // At this point, $data is an array or an array-like object that already contains the
        // new entries, which were added by the data mapper. The data mapper ignores existing
        // entries, so we need to manually unset removed entries in the collection.

        if (null === $data) {
            $data = [];
        }

        if (!is_array($data) && !($data instanceof Traversable && $data instanceof ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        if ($this->deleteEmpty) {
            $previousData = $form->getData();
            /** @var FormInterface $child */
            foreach ($form as $name => $child) {
                if (!$child->isValid() || !$child->isSynchronized()) {
                    continue;
                }

                $isNew = !isset($previousData[$name]);
                $isEmpty = is_callable($this->deleteEmpty) ? ($this->deleteEmpty)($child->getData()) : $child->isEmpty();

                // $isNew can only be true if allowAdd is true, so we don't
                // need to check allowAdd again
                if ($isEmpty && ($isNew || $this->allowDelete)) {
                    unset($data[$name]);
                    $form->remove($name);
                }
            }
        }

        // The data mapper only adds, but does not remove items, so do this
        // here
        if ($this->allowDelete) {
            $toDelete = [];

            foreach ($data as $name => $child) {
                if (!$form->has($name)) {
                    $toDelete[] = $name;
                }
            }

            foreach ($toDelete as $name) {
                unset($data[$name]);
            }
        }

        $event->setData($data);
    }

	/**
	 * @param ArrayAccess|array $item
	 * @return mixed|null
	 */
	private function getPropertyValue($item): mixed
    {
		return $this->propertyAccessor->getValue($item, is_array($item) ? '['.$this->property.']' : $this->property);
	}
}
