<?php

namespace Netbull\CoreBundle\Form;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class FormSerializer
 * @package Netbull\CoreBundle\Form
 */
class FormSerializer
{
    private $trimFields = [
        'form', 'block_prefixes', 'cache_key', 'submitted', 'multipart', 'method', 'action', 'value', 'unique_block_prefix',
        'data', 'clicked'
    ];

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * FormSerializer constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct( TranslatorInterface $translator )
    {
        $this->translator = $translator;
    }

    /**
     * @param FormInterface $form
     * @return string
     */
    public function serialize( FormInterface $form )
    {
        $type = $form->getConfig()->getType();
        $options = $form->getConfig()->getOptions();

        $formView = $type->createView($form);
        $type->buildView($formView, $form, $options);

        $fields = $this->getChildren($form, $formView);

        $type->finishView($formView, $form, $options);

        // Get the CSRF token
        if ( $formView->offsetExists('_token') ) {
            $tokenView = $formView->offsetGet('_token');
            $options = $this->extractOptions($tokenView);

            $fields[$options['name']] = [
                'type'      => 'hidden',
                'options'   => $options,
                'parent'    => true,
                'value'     => $tokenView->vars['value'],
                'fields'    => []
            ];
        }

        $output = [
            'options'   => $this->extractOptions($formView, true),
            'fields'    => $fields,
            'parent'    => false,
        ];

        return json_encode($output);
    }

    /**
     * @param FormInterface $form
     * @param FormView      $parent
     * @return array
     */
    private function getChildren( FormInterface $form, FormView $parent )
    {
        $fields = [];
        foreach ( $form->all() as $name => $childForm ) {
            $fields[$name] = $this->extractFieldData($childForm, $parent, $name);
        }

        return $fields;
    }

    /**
     * @param FormInterface $form
     * @param FormView      $parent
     * @param null          $name
     * @return array
     */
    private function extractFieldData( FormInterface $form, FormView $parent, $name = null )
    {
        $type = $form->getConfig()->getType();

        $childFormView = $form->createView($parent);
        if ( $name ) {
            $parent->children[$name] = $childFormView;
        }

        $childFields = [];
        if ( $form->count() > 0 ) {
            $childFields = $this->getChildren($form, $childFormView);
        }

        $data = [
            'type'      => $type->getBlockPrefix(),
            'parent'    => true,
            'options'   => $this->extractOptions($childFormView),
            'value'     => ( !is_object($childFormView->vars['value']) && !is_callable($childFormView->vars['value']) ) ? $childFormView->vars['value'] : null
        ];

        if ( 0 < count($childFields) ) {
            $data['fields'] = $childFields;
        }

        return $data;
    }

    /**
     * @param FormView  $view
     * @param bool      $isForm
     * @return array
     */
    private function extractOptions( FormView $view, $isForm = false )
    {
        $checkboxRadio = !empty($view->parent) && isset($view->parent->vars['choices']);

        $options = [];
        foreach ( $view->vars as $name => $option ) {
            if (
                ( !in_array($name, $this->trimFields) || $isForm ) &&
                ( is_string($option) || is_bool($option) || is_int($option) || is_float($option) )
            ) {
                $options[$name] = $option;
            }

            // Fix of the shitty bug for choices
            // Radio buttons should have the parent's name
            // Checkboxes should have the parent's name + []
            if ( 'full_name' === $name && $checkboxRadio && isset($view->parent->vars['expanded']) ) {
                $options['full_name'] = $view->parent->vars['full_name'];

                if ( $view->parent->vars['multiple'] ) {
                    $options['full_name'] .= '[]';
                }
            }

            // Field level errors
            if ( 'errors' === $name ) {
                $errors = [];
                foreach ( $option as $error ) {
                    $errors[] = $this->translator->trans($error->getMessage(), $error->getMessageParameters(), 'validators');
                }
                $options['errors'] = $errors;
            }

            // Choice views
            if ( 'choices' === $name || 'preferred_choices' === $name ) {
                $choices = [];
                foreach ( $option as $choice ) {
                    $choices[] = [
                        'label' => $choice->label,
                        'value' => $choice->value,
                    ];
                }
                $options[$name] = $choices;
            }

            // Additional attributes
            if ( 'attr' === $name || 'label_attr' === $name ) {
                $attributes = [];
                foreach ( $option as $attrname => $attrvalue ) {
                    if ( 'class' === $attrname ) {
                        $attrname = 'className';
                    }
                    $attributes[$attrname] = $attrvalue;
                }
                $options[$name] = $attributes;
            }
        }

        return $options;
    }
}
