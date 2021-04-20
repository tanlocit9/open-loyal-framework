<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Form\Type;

use OpenLoyalty\Bundle\TransactionBundle\Model\AssignCustomer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use OpenLoyalty\Bundle\TransactionBundle\Validator\Constraints\TransactionReturnDocument;

/**
 * Class ManuallyAssignCustomerToTransactionFormType.
 */
class ManuallyAssignCustomerToTransactionFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('transactionDocumentNumber', TextType::class, [
            'required' => true,
            'constraints' => [new TransactionReturnDocument(['isManually' => false])],
        ]);

        $builder->add('customerId', TextType::class, [
            'required' => false,
        ]);
        $builder->add('customerLoyaltyCardNumber', TextType::class, [
            'required' => false,
        ]);
        $builder->add('customerPhoneNumber', TextType::class, [
            'required' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AssignCustomer::class,
        ]);
    }
}
