<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Form\Type;

use OpenLoyalty\Bundle\SettingsBundle\Form\DataTransformer\ChoicesToJsonSettingDataTransformer;
use OpenLoyalty\Bundle\SettingsBundle\Form\DataTransformer\StringSettingDataTransformer;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SettingsCollectionType.
 */
class SettingsCollectionType extends AbstractType
{
    /**
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * SettingsTextType constructor.
     *
     * @param SettingsManager $settingsManager
     */
    public function __construct(SettingsManager $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (array_key_exists('transformTo', $options)) {
            switch ($options['transformTo']) {
                case 'json':
                    $builder->addModelTransformer(
                        new ChoicesToJsonSettingDataTransformer($builder->getName(), $this->settingsManager)
                    );
                    break;
                case 'string':
                default:
                    $builder->addModelTransformer(
                        new StringSettingDataTransformer($builder->getName(), $this->settingsManager)
                    );
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined('transformTo');
        $resolver->setAllowedTypes('transformTo', ['null', 'string']);
        $resolver->addAllowedValues('transformTo', ['string', 'json']);
        $resolver->setDefaults(['transformTo' => 'string']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
