<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Form\Type;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type as Numeric;

/**
 * Class SellerRegistrationFormType.
 */
class SellerRegistrationFormType extends AbstractType
{
    /**
     * @var Repository
     */
    protected $posRepository;

    /**
     * SellerRegistrationFormType constructor.
     *
     * @param PosRepository $posRepository
     */
    public function __construct(PosRepository $posRepository)
    {
        $this->posRepository = $posRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'firstName',
            TextType::class,
            [
                'label' => 'First name',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ]
        );
        $builder->add(
            'lastName',
            TextType::class,
            [
                'label' => 'Last name',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ]
        );
        $builder->add(
            'active',
            CheckboxType::class,
            [
                'required' => false,
            ]
        );
        $builder->add(
            'email',
            EmailType::class,
            [
                'label' => 'Email',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ],
            ]
        );
        $builder->add(
            'phone',
            TextType::class,
            [
                'label' => 'Phone',
                'required' => false,
                'constraints' => [
                    new Numeric(['type' => 'numeric', 'message' => 'Incorrect phone number format, use +00000000000']),
                ],
            ]
        );

        $poses = array_map(
            function (Pos $pos) {
                return $pos->getPosId()->__toString();
            },
            $this->posRepository->findAll()
        );

        $builder->add(
            'posId',
            ChoiceType::class,
            [
                'required' => true,
                'constraints' => [new NotBlank()],
                'choices' => array_combine($poses, $poses),
            ]
        );

        $builder->add(
            'plainPassword',
            PasswordType::class,
            [
                'required' => true,
                'constraints' => [new NotBlank()],
            ]
        );

        $builder->add(
            'allowPointTransfer',
            CheckboxType::class,
            [
                'required' => false,
            ]
        );
    }
}
