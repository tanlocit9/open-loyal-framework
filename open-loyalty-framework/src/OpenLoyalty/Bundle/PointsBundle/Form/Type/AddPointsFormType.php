<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Form\Type;

use Broadway\ReadModel\Repository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use OpenLoyalty\Bundle\PointsBundle\Validator\Constraints\Customer;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Class AddPointsFormType.
 */
class AddPointsFormType extends AbstractType
{
    /**
     * @var Repository
     */
    protected $customerDetailsRepository;

    /**
     * AddPointsFormType constructor.
     *
     * @param Repository $customerDetailsRepository
     */
    public function __construct(Repository $customerDetailsRepository)
    {
        $this->customerDetailsRepository = $customerDetailsRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('customer', TextType::class, [
            'required' => true,
            'constraints' => [
                new NotBlank(),
                new Customer(),
                new Uuid(),
            ],
        ]);
        $builder->add('points', NumberType::class, [
            'attr' => ['min' => 1],
            'scale' => 2,
            'constraints' => [
                new NotBlank(),
                new Range(['min' => 1]),
            ],
        ]);

        $builder->add('validityDuration', NumberType::class, [
            'scale' => 2,
            'constraints' => [
                new Range(['min' => 1]),
            ],
        ]);

        $builder->add('comment', TextType::class, [
            'required' => false,
        ]);
    }
}
