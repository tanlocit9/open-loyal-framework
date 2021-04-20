<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyaltyPlugin\SalesManagoBundle\Form\Type;

use OpenLoyalty\Bundle\SettingsBundle\Form\DataTransformer\ChoicesToJsonSettingDataTransformer;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ConfigFormType.
 */
class ConfigFormType extends AbstractType
{
    /**
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * ConfigFormType constructor.
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
        $builder->add('api_url', TextType::class, ['required' => true, 'constraints' => [new NotBlank()]]);
        $builder->add('api_secret', TextType::class, ['required' => true, 'constraints' => [new NotBlank()]]);
        $builder->add('api_key', TextType::class, ['required' => true, 'constraints' => [new NotBlank()]]);
        $builder->add('customer_id', TextType::class, ['required' => true, 'constraints' => [new NotBlank()]]);
        $builder->add('email', EmailType::class, ['required' => true, 'constraints' => [new NotBlank(), new Email()]]);

        $builder->addModelTransformer(new ChoicesToJsonSettingDataTransformer('sales_manago', $this->settingsManager));
    }
}
