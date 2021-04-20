<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Form\Type;

use OpenLoyalty\Bundle\TransactionBundle\Model\AppendLabels;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AppendLabelsToTransactionFormType.
 */
class AppendLabelsToTransactionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('transactionDocumentNumber', TextType::class, [
            'required' => true,
        ]);

        $builder->add('labels', CollectionType::class, [
            'allow_add' => true,
            'allow_delete' => false,
            'entry_type' => LabelFormType::class,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AppendLabels::class,
        ]);
    }
}
