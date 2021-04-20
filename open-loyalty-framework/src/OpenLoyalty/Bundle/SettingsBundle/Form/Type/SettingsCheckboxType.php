<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Form\Type;

use OpenLoyalty\Bundle\SettingsBundle\Form\DataTransformer\BooleanSettingDataTransformer;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SettingsCheckboxType.
 */
class SettingsCheckboxType extends AbstractType
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
        $builder->addModelTransformer(new BooleanSettingDataTransformer($builder->getName(), $this->settingsManager));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CheckboxType::class;
    }
}
