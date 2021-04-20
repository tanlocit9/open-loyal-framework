<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Form\Type;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use OpenLoyalty\Bundle\CampaignBundle\Model\CampaignCategory;
use OpenLoyalty\Bundle\SettingsBundle\Service\LocaleProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class CampaignCategoryFormType.
 */
class CampaignCategoryFormType extends AbstractType
{
    /** @var LocaleProviderInterface */
    protected $localeProvider;

    /**
     * CampaignFormType constructor.
     *
     * @param LocaleProviderInterface $localeProvider
     */
    public function __construct(LocaleProviderInterface $localeProvider)
    {
        $this->localeProvider = $localeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('active', CheckboxType::class, [
                'required' => false,
            ])
            ->add('sortOrder', IntegerType::class, [
                'required' => true,
                'constraints' => [new NotBlank(), new Range(['min' => 0])],
            ])
            ->add('translations', TranslationsType::class, [
                'required' => true,
                'fields' => [
                    'name' => [
                        'field_type' => TextType::class,
                        'locale_options' => [
                            $this->localeProvider->getConfigurationDefaultLocale() => ['constraints' => [new NotBlank()]],
                        ],
                    ],
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CampaignCategory::class,
        ]);
    }
}
