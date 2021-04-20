<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\LevelBundle\Form\Type;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use OpenLoyalty\Bundle\SettingsBundle\Service\LocaleProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class LevelFormType.
 */
class LevelFormType extends AbstractType
{
    /**
     * @var LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * LevelFormType constructor.
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
            ->add('translations', TranslationsType::class, [
                'required' => true,
                'fields' => [
                    'name' => [
                        'field_type' => TextType::class,
                        'locale_options' => [
                            $this->localeProvider->getConfigurationDefaultLocale() => ['constraints' => [new NotBlank()]],
                        ],
                    ],
                    'description' => [
                        'field_type' => TextareaType::class,
                    ],
                ],
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
            ])
            ->add('conditionValue', NumberType::class, [
                'required' => true,
                'constraints' => [new NotBlank()],
            ])
            ->add('minOrder', NumberType::class, [
                'required' => false,
            ])
            ->add('reward', RewardFormType::class)
            ->add('specialRewards', CollectionType::class, [
                'entry_type' => SpecialRewardFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'OpenLoyalty\Bundle\LevelBundle\Model\Level',
        ]);
    }
}
