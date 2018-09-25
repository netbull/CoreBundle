<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use NetBull\CoreBundle\Form\EventListener\TranslationsListener;

/**
 * Regroup by locales, all translations fields
 * Class TranslationsType
 * @package NetBull\CoreBundle\Form\Type
 */
class TranslationsType extends AbstractType
{
    /**
     * @var TranslationsListener
     */
    private $translationsListener;

    /**
     * @var array
     */
    private $locales;

    /**
     * @var array
     */
    private $defaultLocale;

    /**
     * @var array
     */
    private $renderTypes = ['rows', 'tabs', 'tabs-small'];

    /**
     * TranslationsType constructor.
     * @param TranslationsListener  $translationsListener
     * @param array                 $locales
     * @param string                $defaultLocale
     */
    public function __construct(TranslationsListener $translationsListener, array $locales, $defaultLocale)
    {
        $this->translationsListener = $translationsListener;
        $this->locales              = $locales;
        $this->defaultLocale        = $defaultLocale;
    }

    /**
     *
     * @param FormBuilderInterface  $builder
     * @param array                 $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->translationsListener);
    }

    /**
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['default_locale']   = $options['default_locale'];
        $view->vars['required_locales'] = $options['required_locales'];
        $view->vars['render_type']      = (in_array($options['render_type'], $this->renderTypes))?$options['render_type']:'tabs';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'by_reference'      => false,
            'empty_data'        => function () {
                return new ArrayCollection();
            },
            'locales'           => $this->locales,
            'default_locale'    => $this->defaultLocale,
            'required_locales'  => [$this->defaultLocale],
            'fields'            => [],
            'exclude_fields'    => [],
            'render_type'       => 'tabs'
        ]);
    }
}
