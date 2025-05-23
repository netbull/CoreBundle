<?php

namespace NetBull\CoreBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AjaxType extends DynamicType
{
    /**
     * @var int
     */
    protected int $minimumInputLength;

    /**
     * @var int
     */
    protected int $perPage;

    /**
     * @param EntityManagerInterface $em
     * @param RouterInterface $router
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(protected EntityManagerInterface $em, protected RouterInterface $router, ParameterBagInterface $parameterBag)
    {
        parent::__construct($em);

        $this->minimumInputLength = $parameterBag->get('netbull_core.form_types.ajax.minimum_input_length');
        $this->perPage = $parameterBag->get('netbull_core.form_types.ajax.page_limit');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'remote_path' => null,
            'remote_route' => null,
            'remote_params' => [],
            'perPage' => $this->perPage,
            'placeholder' => '',
            'minimum_input_length' => $this->minimumInputLength,
        ]);
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);

        // make variables available to the view
        $view->vars['remote_path'] = ( !$options['remote_route'] ) ? null : $this->router->generate($options['remote_route'], array_merge($options['remote_params'], [ 'perPage' => $options['perPage'] ]));

        $varNames = ['minimum_input_length', 'placeholder'];

        foreach ($varNames as $varName) {
            $view->vars[$varName] = $options[$varName];
        }
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'ajax_type';
    }
}
