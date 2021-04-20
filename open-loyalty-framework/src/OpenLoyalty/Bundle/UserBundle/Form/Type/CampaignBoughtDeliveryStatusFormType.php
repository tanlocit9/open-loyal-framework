<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Form\Type;

use OpenLoyalty\Bundle\SettingsBundle\Provider\DeliveryStatusChoices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class CampaignBoughtDeliveryStatusFormType.
 */
class CampaignBoughtDeliveryStatusFormType extends AbstractType
{
    /**
     * @var DeliveryStatusChoices
     */
    private $deliveryStatusChoices;

    /**
     * CampaignBoughtDeliveryStatusFormType constructor.
     *
     * @param DeliveryStatusChoices $deliveryStatusChoices
     */
    public function __construct(DeliveryStatusChoices $deliveryStatusChoices)
    {
        $this->deliveryStatusChoices = $deliveryStatusChoices;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'status',
            ChoiceType::class,
            [
                'choices' => [
                    $this->deliveryStatusChoices->getChoices()['choices'],
                ],
                'required' => true,
                'constraints' => [new NotBlank()],
            ]
        );
    }
}
