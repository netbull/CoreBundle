<?php

namespace NetBull\CoreBundle\Form\Type;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AjaxType
 * @package NetBull\CoreBundle\Form\Type
 */
class AjaxType extends DynamicType
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var integer
     */
    protected $minimumInputLength;

    /**
     * @var  integer
     */
    protected $perPage;

    /**
     * AjaxType constructor.
     * @param EntityManager $em
     * @param RouterInterface $router
     * @param $minimumInputLength
     * @param $perPage
     */
    public function __construct(EntityManager $em, RouterInterface $router, $minimumInputLength, $perPage)
    {
        parent::__construct($em);

        $this->router = $router;
        $this->minimumInputLength = $minimumInputLength;
        $this->perPage = $perPage;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
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
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'remote_path'           => null,
            'remote_route'          => null,
            'remote_params'         => [],
            'perPage'               => $this->perPage,
            'placeholder'           => '',
            'minimum_input_length'  => $this->minimumInputLength,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'ajax_type';
    }
}
