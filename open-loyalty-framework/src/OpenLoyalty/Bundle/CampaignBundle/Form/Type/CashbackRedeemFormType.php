<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Form\Type;

use OpenLoyalty\Bundle\CampaignBundle\Model\CashbackRedeem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class CashbackRedeemFormType.
 */
class CashbackRedeemFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('rewardAmount', NumberType::class, [
            'constraints' => [new Range(['min' => 0])],
        ]);
        $builder->add('pointValue', NumberType::class, [
            'constraints' => [new Range(['min' => 0])],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CashbackSimulationFormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CashbackRedeem::class);
    }
}
