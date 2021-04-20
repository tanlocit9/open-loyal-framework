<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Form\Type;

use OpenLoyalty\Bundle\UserBundle\Model\SearchCustomer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Type as Numeric;

/**
 * Class CustomerSearchFormType.
 */
class CustomerSearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('loyaltyCardNumber', TextType::class, [
            'required' => false,
        ]);
        $builder->add('phone', TextType::class, [
            'required' => false,
            'constraints' => [
                new Numeric(['type' => 'numeric', 'message' => 'Incorrect phone number format, use +00000000000']),
            ],
        ]);
        $builder->add('email', TextType::class, [
            'required' => false,
        ]);
        $builder->add('firstName', TextType::class, [
            'required' => false,
        ]);
        $builder->add('lastName', TextType::class, [
            'required' => false,
        ]);
        $builder->add('city', TextType::class, [
            'required' => false,
        ]);
        $builder->add('postcode', TextType::class, [
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchCustomer::class,
        ]);
    }
}
