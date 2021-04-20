<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Form\Type;

use OpenLoyalty\Bundle\SegmentBundle\Form\Transformer\CustomerTransformer;
use OpenLoyalty\Bundle\SegmentBundle\Provider\CustomerIdProvider;
use OpenLoyalty\Bundle\SegmentBundle\Validator\Constraints\Customer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends TextType
{
    /**
     * @var CustomerIdProvider
     */
    private $customerIdProvider;

    /**
     * CustomerType constructor.
     *
     * @param CustomerIdProvider $customerIdProvider
     */
    public function __construct(CustomerIdProvider $customerIdProvider)
    {
        $this->customerIdProvider = $customerIdProvider;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CustomerTransformer($this->customerIdProvider));
    }

    public function configureOptions(OptionsResolver $options)
    {
        $options->setDefaults([
            'compound' => false,
            'constraints' => [new Customer()],
        ]);
    }
}
