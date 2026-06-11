<?php

namespace NetBull\CoreBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class Select2Type extends AjaxType
{
    protected bool $allowClear;

    protected int $delay;

    protected string $language;

    protected bool $cache;

    public function __construct(EntityManagerInterface $em, RouterInterface $router, ParameterBagInterface $parameterBag)
    {
        parent::__construct($em, $router, $parameterBag);

        $this->allowClear = $parameterBag->get('netbull_core.form_types.ajax.allow_clear');
        $this->delay = $parameterBag->get('netbull_core.form_types.ajax.delay');
        $this->language = $parameterBag->get('netbull_core.form_types.ajax.language');
        $this->cache = $parameterBag->get('netbull_core.form_types.ajax.cache');
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);

        $varNames = ['minimum_input_length', 'language', 'allow_clear', 'delay', 'cache', 'tags'];

        foreach ($varNames as $varName) {
            $view->vars[$varName] = $options[$varName];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'allow_clear' => $this->allowClear,
            'delay' => $this->delay,
            'language' => $this->language,
            'cache' => $this->cache,
            'tags' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'select2_type';
    }
}
