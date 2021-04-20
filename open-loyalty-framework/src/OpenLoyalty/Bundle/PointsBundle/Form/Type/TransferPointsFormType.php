<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Form\Type;

use OpenLoyalty\Bundle\PointsBundle\Form\DataTransformer\CustomerDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TransferPointsFormType.
 */
class TransferPointsFormType extends AbstractType
{
    /**
     * @var CustomerDataTransformer
     */
    private $transformer;

    /**
     * TransferPointsByCustomerFormType constructor.
     *
     * @param CustomerDataTransformer $transformer
     */
    public function __construct(CustomerDataTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sender', TextType::class, [
            'constraints' => [new NotBlank()],
        ]);
        $builder->get('sender')->addModelTransformer($this->transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TransferPointsByCustomerFormType::class;
    }
}
