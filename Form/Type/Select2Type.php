<?php

namespace NetBull\CoreBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Select2Type
 * @package NetBull\CoreBundle\Form\Type
 */
class Select2Type extends AjaxType
{
    /**
     * @var boolean
     */
    protected $allowClear;

    /**
     * @var integer
     */
    protected $delay;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var boolean
     */
    protected $cache;

    /**
     * AjaxType constructor.
     * @param EntityManager     $em
     * @param RouterInterface   $router
     * @param                   $minimumInputLength
     * @param                   $perPage
     * @param                   $allowClear
     * @param                   $delay
     * @param                   $language
     * @param                   $cache
     */
    public function __construct( EntityManager $em, RouterInterface $router, $minimumInputLength, $perPage, $allowClear, $delay, $language, $cache )
    {
        parent::__construct($em, $router, $minimumInputLength, $perPage);

        $this->allowClear           = $allowClear;
        $this->delay                = $delay;
        $this->language             = $language;
        $this->cache                = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        $varNames = ['minimum_input_length', 'language', 'allow_clear', 'delay', 'language', 'cache', 'tags'];

        foreach ($varNames as $varName) {
            $view->vars[$varName] = $options[$varName];
        }

        if ($options['multiple']) {
            $view->vars['full_name'] .= '[]';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_clear'           => $this->allowClear,
            'delay'                 => $this->delay,
            'language'              => $this->language,
            'cache'                 => $this->cache,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'select2_type';
    }
}
