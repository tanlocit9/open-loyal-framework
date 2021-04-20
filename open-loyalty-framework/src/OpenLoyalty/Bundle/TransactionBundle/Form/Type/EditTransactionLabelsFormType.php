<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Form\Type;

use OpenLoyalty\Bundle\TransactionBundle\Model\EditLabels;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EditTransactionLabelsFormType.
 */
class EditTransactionLabelsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('transactionId', TextType::class, [
            'required' => true,
        ]);

        $builder->add('labels', CollectionType::class, [
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => LabelFormType::class,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EditLabels::class,
        ]);
    }
}
