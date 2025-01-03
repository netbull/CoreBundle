<?php

namespace NetBull\CoreBundle\Form\Type;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use NetBull\CoreBundle\Form\DataTransformer\PhoneNumberToArrayTransformer;
use NetBull\CoreBundle\Form\DataTransformer\PhoneNumberToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneNumberType extends AbstractType
{
	const WIDGET_SINGLE_TEXT = 'single_text';
	const WIDGET_COUNTRY_CHOICE = 'country_choice';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
	public function buildForm(FormBuilderInterface $builder, array $options): void
    {
		if (self::WIDGET_COUNTRY_CHOICE === $options['widget']) {
			$util = PhoneNumberUtil::getInstance();

			$countries = [];

			if (\is_array($options['country_choices'])) {
				foreach ($options['country_choices'] as $country) {
					$code = $util->getCountryCodeForRegion($country);

					if ($code) {
						$countries[$country] = $code;
					}
				}
			}

			if (empty($countries)) {
				foreach ($util->getSupportedRegions() as $country) {
					$countries[$country] = $util->getCountryCodeForRegion($country);
				}
			}

			$countryChoices = [];

			foreach (Countries::getNames() as $region => $name) {
				if (false === isset($countries[$region])) {
					continue;
				}

				$countryChoices[sprintf('%s (+%s)', $name, $countries[$region])] = $region;
			}

			$transformerChoices = array_values($countryChoices);

			$countryOptions = array_replace([
				'error_bubbling' => true,
				'disabled' => $options['disabled'],
				'translation_domain' => $options['translation_domain'],
				'choice_translation_domain' => false,
				'required' => true,
				'choices' => $countryChoices,
				'preferred_choices' => $options['preferred_country_choices'],
			], $options['country_options']);

			if ($options['country_placeholder']) {
				$countryOptions['placeholder'] = $options['country_placeholder'];
			}

			$numberOptions = array_replace([
				'error_bubbling' => true,
				'required' => $options['required'],
				'disabled' => $options['disabled'],
				'translation_domain' => $options['translation_domain'],
			], $options['number_options']);

			$builder
				->add('country', ChoiceType::class, $countryOptions)
				->add('number', TextType::class, $numberOptions)
				->addViewTransformer(new PhoneNumberToArrayTransformer($transformerChoices));
		} else {
			$builder->addViewTransformer(
				new PhoneNumberToStringTransformer($options['default_region'], $options['default_regions'], $options['format'])
			);
		}
	}

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @return void
     */
	public function buildView(FormView $view, FormInterface $form, array $options): void
    {
		$view->vars['type'] = 'tel';
		$view->vars['widget'] = $options['widget'];
	}

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
	public function configureOptions(OptionsResolver $resolver): void
    {
		$resolver->setDefaults([
			'widget' => self::WIDGET_SINGLE_TEXT,
			'compound' => function (Options $options): bool {
				return self::WIDGET_SINGLE_TEXT !== $options['widget'];
			},
			'default_region' => PhoneNumberUtil::UNKNOWN_REGION,
			'default_regions' => [],
			'format' => PhoneNumberFormat::INTERNATIONAL,
			'invalid_message' => 'This value is not a valid phone number.',
			'by_reference' => false,
			'error_bubbling' => false,
			'country_choices' => [],
			'country_placeholder' => false,
			'preferred_country_choices' => [],
			'country_options' => [],
			'number_options' => [],
		]);

		$resolver->setAllowedValues('widget', [
			self::WIDGET_SINGLE_TEXT,
			self::WIDGET_COUNTRY_CHOICE,
		]);

		$resolver->setAllowedTypes('country_options', 'array');
		$resolver->setAllowedTypes('number_options', 'array');
	}

    /**
     * @return string
     */
	public function getName(): string
    {
		return $this->getBlockPrefix();
	}

    /**
     * @return string
     */
	public function getBlockPrefix(): string
    {
		return 'phone_number';
	}
}
