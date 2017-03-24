<?php

namespace Netbull\CoreBundle\Form;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormSerializer
 * @package Netbull\CoreBundle\Form
 */
class FormSerializer
{
    private $trimFields = [ 'form', 'block_prefixes', 'cache_key', 'submitted', 'multipart', 'method', 'action', 'value', 'unique_block_prefix' ];

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
        $type->finishView($formView, $form, $options);

        $fields = $this->getChildren($form, $formView);

        // Get the CSRF token
        if ( $formView->offsetExists('_token') ) {
            $tokenView = $formView->offsetGet('_token');
            $options = $this->extractOptions($tokenView);

            $fields[$options['name']] = [
                'type'      => 'hidden',
                'options'   => $options,
                'value'     => $tokenView->vars['value'],
                'fields'    => []
            ];
        }

        $output = [
            'options'   => $this->extractOptions($formView, true),
            'fields'    => $fields
        ];

        return $output;
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
            $fields[$name] = $this->extractFieldData($childForm, $parent);
        }

        return $fields;
    }

    /**
     * @param FormInterface $form
     * @param FormView      $parent
     * @return array
     */
    private function extractFieldData( FormInterface $form, FormView $parent )
    {
        $type = $form->getConfig()->getType();

        $childFormView = $form->createView($parent);

        $childFields = [];
        if ( $form->count() > 0 ) {
            $childFields = $this->getChildren($form, $childFormView);
        }

        return [
            'type'      => $type->getBlockPrefix(),
            'options'   => $this->extractOptions($childFormView),
            'value'     => $childFormView->vars['value'],
            'fields'    => $childFields
        ];
    }

    /**
     * @param FormView  $view
     * @param bool      $isForm
     * @return array
     */
    private function extractOptions( FormView $view, $isForm = false )
    {
        $options = [];
        foreach ( $view->vars as $name => $option ) {
            if (
                ( !in_array($name, $this->trimFields) || $isForm ) &&
                ( is_string($option) || is_bool($option) || is_int($option) || is_float($option) )
            ) {
                $options[$name] = $option;
            }

            // Field level errors
            if ( 'errors' === $name ) {
                $errors = [];
                foreach ( $option as $error ) {
                    $errors[] = $error;
                }
                $options['errors'] = $errors;
            }
        }

        return $options;
    }
}
