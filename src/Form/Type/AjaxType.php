<?php

namespace NetBull\CoreBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class AjaxType extends DynamicType
{
    protected int $minimumInputLength;

    protected int $perPage;

    public function __construct(protected EntityManagerInterface $em, protected RouterInterface $router, ParameterBagInterface $parameterBag)
    {
        parent::__construct($em);

        $this->minimumInputLength = $parameterBag->get('netbull_core.form_types.ajax.minimum_input_length');
        $this->perPage = $parameterBag->get('netbull_core.form_types.ajax.page_limit');
    }

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

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);

        // make variables available to the view
        $view->vars['remote_path'] = (!$options['remote_route']) ? null : $this->router->generate($options['remote_route'], array_merge($options['remote_params'], ['perPage' => $options['perPage']]));

        $varNames = ['minimum_input_length', 'placeholder'];

        foreach ($varNames as $varName) {
            $view->vars[$varName] = $options[$varName];
        }
    }

    public function getBlockPrefix(): string
    {
        return 'ajax_type';
    }
}
