<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Form\Type;

use OpenLoyalty\Bundle\UserBundle\Form\DataTransformer\DateTransformer;
use OpenLoyalty\Bundle\UserBundle\Validator\Constraint\CustomerLabel;
use OpenLoyalty\Component\Customer\Domain\Model\AccountActivationMethod;
use OpenLoyalty\Component\Customer\Domain\Model\Gender;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelRepository;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetails;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetailsRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type as Numeric;

/**
 * Class CustomerRegistrationFormType.
 */
class CustomerRegistrationFormType extends AbstractType
{
    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * @var PosRepository
     */
    private $posRepository;

    /**
     * @var SellerDetailsRepository
     */
    private $sellerRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * CustomerRegistrationFormType constructor.
     *
     * @param LevelRepository         $levelRepository
     * @param PosRepository           $posRepository
     * @param SellerDetailsRepository $sellerRepository
     * @param TranslatorInterface     $translator
     */
    public function __construct(
        LevelRepository $levelRepository,
        PosRepository $posRepository,
        SellerDetailsRepository $sellerRepository,
        TranslatorInterface $translator
    ) {
        $this->levelRepository = $levelRepository;
        $this->posRepository = $posRepository;
        $this->sellerRepository = $sellerRepository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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
            'gender',
            ChoiceType::class,
            [
                'label' => 'Gender',
                'required' => false,
                'empty_data' => Gender::NOT_DISCLOSED,
                'choices' => [
                    Gender::MALE => Gender::MALE,
                    Gender::FEMALE => Gender::FEMALE,
                    Gender::NOT_DISCLOSED => Gender::NOT_DISCLOSED,
                ],
            ]
        );

        if (AccountActivationMethod::isMethodEmail($options['activationMethod'])) {
            $builder->add(
                'email',
                EmailType::class,
                [
                    'label' => 'Email',
                    'required' => true,
                    'constraints' => [
                        new Email(),
                        new NotBlank(),
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
                        new Numeric(
                            [
                                'type' => 'numeric',
                                'message' => $this->translator->trans('customer.registration.invalid_phone_number'),
                            ]
                        ),
                    ],
                ]
            );
        } elseif (AccountActivationMethod::isMethodSms($options['activationMethod'])) {
            $builder->add(
                'email',
                EmailType::class,
                [
                    'label' => 'Email',
                    'required' => false,
                    'constraints' => [
                        new Email(),
                    ],
                    'empty_data' => '',
                ]
            );
            $builder->add(
                'phone',
                TextType::class,
                [
                    'label' => 'Phone',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                        new Numeric(
                            [
                                'type' => 'numeric',
                                'message' => $this->translator->trans('customer.registration.invalid_phone_number'),
                            ]
                        ),
                    ],
                ]
            );
        }
        $builder->add(
            $builder->create('birthDate',
                TextType::class,
                [
                    'label' => 'Birth date',
                    'required' => false,
                    'constraints' => [
                        new DateTime(['format' => 'Y-m-d']),
                    ],
                ])->addModelTransformer(new DateTransformer())
        );
        $builder->add(
            'createdAt',
            DateTimeType::class,
            [
                'label' => 'Creation date',
                'required' => false,
                'widget' => 'single_text',
                'format' => DateTimeType::HTML5_FORMAT,
            ]
        );

        $this->addAddressFields($builder);
        $this->addCompanyFields($builder);

        $builder->add(
            'loyaltyCardNumber',
            TextType::class,
            [
                'label' => 'Loyalty card number',
                'required' => false,
                'empty_data' => '',
            ]
        );

        $builder->add('labels', LabelsFormType::class, [
            'label' => 'Labels',
            'required' => false,
            'constraints' => [
                new CustomerLabel(),
            ],
        ]);

        $builder->add(
            'agreement1',
            CheckboxType::class,
            [
                'label' => 'TOS Agreement (required to be true)',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ]
        );
        $builder->add(
            'agreement2',
            CheckboxType::class,
            [
                'label' => 'Direct Marketing Agreement (default false)',
                'required' => false,
            ]
        );
        $builder->add(
            'agreement3',
            CheckboxType::class,
            [
                'required' => false,
            ]
        );
        $builder->add(
            'referral_customer_email',
            TextType::class,
            [
                'required' => false,
            ]
        );

        if ($options['includeLevelId'] && $this->levelRepository) {
            $levelChoices = array_map(
                function (Level $level) {
                    return (string) $level->getLevelId();
                },
                $this->levelRepository->findAllActive()
            );

            $builder->add(
                'levelId',
                ChoiceType::class,
                [
                    'required' => false,
                    'choices' => array_combine($levelChoices, $levelChoices),
                ]
            );
        }

        if ($options['includePosId'] && $this->posRepository) {
            $posChoices = array_map(function (Pos $pos) {
                return (string) $pos->getPosId();
            }, $this->posRepository->findAll());

            $builder->add(
                'posId',
                ChoiceType::class,
                [
                    'required' => false,
                    'choices' => array_combine($posChoices, $posChoices),
                ]
            );
        }

        if ($options['includeSellerId'] && $this->sellerRepository) {
            $sellerChoices = array_map(
                function (SellerDetails $seller) {
                    return $seller->getId();
                },
                $this->sellerRepository->findAll()
            );

            $builder->add(
                'sellerId',
                ChoiceType::class,
                [
                    'required' => false,
                    'choices' => array_combine($sellerChoices, $sellerChoices),
                ]
            );
        }
    }

    private function addCompanyFields(FormBuilderInterface $builder)
    {
        $company = $builder->create(
            'company',
            FormType::class,
            [
                'label' => 'Company',
                'required' => false,
            ]
        );
        $company->add(
            'name',
            TextType::class,
            [
                'label' => 'Name',
            ]
        )->add(
            'nip',
            TextType::class,
            [
                'label' => 'NIP',
            ]
        );
        $builder->add($company);
    }

    private function addAddressFields(FormBuilderInterface $builder)
    {
        $address = $builder->create(
            'address',
            FormType::class,
            [
                'label' => 'Address',
                'required' => false,
            ]
        );

        $address->add(
            'street',
            TextType::class,
            [
                'label' => 'Street',
                'required' => false,
            ]
        )->add(
            'address1',
            TextType::class,
            [
                'label' => 'address1',
                'required' => false,
            ]
        )->add(
            'address2',
            TextType::class,
            [
                'label' => 'address2',
                'required' => false,
            ]
        )->add(
            'postal',
            TextType::class,
            [
                'label' => 'Post code',
                'required' => false,
            ]
        )->add(
            'city',
            TextType::class,
            [
                'label' => 'City',
                'required' => false,
            ]
        )->add(
            'province',
            TextType::class,
            [
                'label' => 'Province',
                'required' => false,
            ]
        )->add(
            'country',
            CountryType::class,
            [
                'label' => 'Country',
                'required' => false,
            ]
        );

        $builder->add($address);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'includeLevelId' => false,
                'includePosId' => false,
                'includeSellerId' => false,
            ]
        );
        $resolver->setDefault('activationMethod', AccountActivationMethod::methodEmail());
    }
}
