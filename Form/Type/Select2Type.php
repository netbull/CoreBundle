<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
     * Select2Type constructor.
     * @param EntityManagerInterface $em
     * @param RouterInterface $router
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(EntityManagerInterface $em, RouterInterface $router, ParameterBagInterface $parameterBag)
    {
        parent::__construct($em, $router, $parameterBag);

        $this->allowClear = $parameterBag->get('netbull_core.form_types.ajax.allow_clear');
        $this->delay = $parameterBag->get('netbull_core.form_types.ajax.delay');
        $this->language = $parameterBag->get('netbull_core.form_types.ajax.language');
        $this->cache = $parameterBag->get('netbull_core.form_types.ajax.cache');
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
            'allow_clear' => $this->allowClear,
            'delay' => $this->delay,
            'language' => $this->language,
            'cache' => $this->cache,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'select2_type';
    }
}
