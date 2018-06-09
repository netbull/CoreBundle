<?php

namespace NetBull\CoreBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use NetBull\CoreBundle\Form\TranslationForm;
use NetBull\CoreBundle\Form\Type\TranslationsFieldsType;
use NetBull\CoreBundle\ORM\Translation\TranslationInterface;

/**
 * Class TranslationsListener
 * @package NetBull\CoreBundle\Form\EventListener
 */
class TranslationsListener implements EventSubscriberInterface
{
    private $translationForm;

    /**
     * @param TranslationForm $translationForm
     */
    public function __construct(TranslationForm $translationForm)
    {
        $this->translationForm = $translationForm;
    }

    /**
     * {@inheritdoc}
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        
        $translatableClass = $form->getParent()->getConfig()->getDataClass();
        $translationClass = $this->getTranslationClass($translatableClass);

        $formOptions = $form->getConfig()->getOptions();
        $fieldsOptions = $this->translationForm->getFieldsOptions($translationClass, $formOptions);

        if (isset($formOptions['locales'])) {
            foreach ($formOptions['locales'] as $locale) {
                if (isset($fieldsOptions[$locale])) {
                    $form->add(
                        $locale,
                        TranslationsFieldsType::class,
                        [
                            'data_class'    => $translationClass,
                            'fields'        => $fieldsOptions[$locale],
                            'locale'        => $locale,
                            'required'      => in_array($locale, $formOptions['required_locales'])
                        ]
                    );
                }
            }
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function submit(FormEvent $event)
    {
        $data = $event->getData();

        foreach ($data as $locale => $translation) {
            // Remove useless Translation object
            if (!$translation) {
                $data->removeElement($translation);
            } else {
                $translation->setLocale($locale);
            }

            if ($translation instanceof TranslationInterface) {
                if ($translation->isEmpty()) {
                    $data->removeElement($translation);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA    => 'preSetData',
            FormEvents::SUBMIT          => 'submit',
        ];
    }

    /**
     * @param $translatableClass
     * @return string
     */
    private function getTranslationClass($translatableClass)
    {
        if (method_exists($translatableClass, "getTranslationEntityClass")) {
            return $translatableClass::getTranslationEntityClass();
        }
        
        return $translatableClass .'Translation';
    }
}
