<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Form\Type;

use OpenLoyalty\Bundle\UserBundle\Form\Type\CustomerRegistrationFormType;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\LevelRepository;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosId;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetails;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetailsRepository;
use OpenLoyalty\Component\Seller\Domain\SellerId;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CustomerRegistrationFormTypeTest.
 */
class CustomerRegistrationFormTypeTest extends TypeTestCase
{
    /**
     * @var LevelRepository | MockObject
     */
    protected $levelRepository;

    /**
     * @var PosRepository | MockObject
     */
    protected $posRepository;

    /**
     * @var SellerDetailsRepository | MockObject
     */
    protected $sellerRepository;

    /**
     * @var ValidatorInterface | MockObject
     */
    private $validator;

    /**
     * @var TranslatorInterface | MockObject
     */
    private $translator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturn('Incorrect phone number format, use 00000000000.');

        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->validator
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()));

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('addConstraint')->willReturn(true);
        $metadata->method('addPropertyConstraint')->willReturn(true);

        $this->validator->method('getMetadataFor')->willReturn($metadata);

        $this->levelRepository = $this->createMock(LevelRepository::class);
        $this->levelRepository->method('findAllActive')->will(
            $this->returnCallback(
                function () {
                    $level = [
                        new Level(
                            new LevelId('f99748f2-bf86-11e6-a4a6-cec0c932ce01'),
                            0
                        ),
                    ];

                    return $level;
                }
            )
        );
        $this->sellerRepository = $this->createMock(SellerDetailsRepository::class);
        $this->sellerRepository->method('findAll')->will(
            $this->returnCallback(
                function () {
                    $seller = [];
                    $seller[] = new SellerDetails(new SellerId('00000000-0000-0000-0000-000000000011'));
                    $seller[] = new SellerDetails(new SellerId('00000000-0000-0000-0000-000000000012'));

                    return $seller;
                }
            )
        );

        $this->posRepository = $this->createMock(PosRepository::class);
        $this->posRepository->method('findAll')->will(
            $this->returnCallback(
                function () {
                    $pos = [];
                    $pos[] = new Pos(new PosId('00000000-0000-0000-0000-000000000000'));
                    $pos[] = new Pos(new PosId('00000000-0000-0000-0000-000000000001'));

                    return $pos;
                }
            )
        );
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_contains_valid_form_fields_when_register_customer(): void
    {
        $formData = [
            'firstName' => 'Jone',
            'lastName' => 'Doe',
            'gender' => 'male',
            'email' => 'jone@example.com',
            'phone' => '123456',
            'birthDate' => '2018-12-12',
            'createdAt' => '2018-08-06',
            'loyaltyCardNumber' => '1234567',
            'referral_customer_email' => 'jone2@example.com',
            'agreement1' => 1,
            'agreement2' => 1,
            'agreement3' => 1,
            'address' => [
                'street' => 'street 1',
                'address1' => 'address 1',
                'address2' => 'address 2',
                'postal' => '777-888',
                'city' => 'Wroclaw',
                'province' => 'dolnoslaskie',
            ],
        ];

        $form = $this->factory->create(CustomerRegistrationFormType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());
        $this->assertSame('Jone', $form->get('firstName')->getData());
        $this->assertSame('Doe', $form->get('lastName')->getData());
        $this->assertSame('male', $form->get('gender')->getData());
        $this->assertSame('jone@example.com', $form->get('email')->getData());
        $this->assertSame('2018-12-12', $form->get('birthDate')->getData()->format('Y-m-d'));
        $this->assertSame('2018-08-06', $form->get('createdAt')->getData()->format('Y-m-d'));
        $this->assertSame('1234567', $form->get('loyaltyCardNumber')->getData());
        $this->assertTrue($form->get('agreement1')->getData());
        $this->assertTrue($form->get('agreement2')->getData());
        $this->assertTrue($form->get('agreement3')->getData());
        $this->assertSame('jone2@example.com', $form->get('referral_customer_email')->getData());

        $this->assertSame('address 1', $form->get('address')->get('address1')->getData());
        $this->assertSame('address 2', $form->get('address')->get('address2')->getData());
        $this->assertSame('777-888', $form->get('address')->get('postal')->getData());
        $this->assertSame('Wroclaw', $form->get('address')->get('city')->getData());
        $this->assertSame('dolnoslaskie', $form->get('address')->get('province')->getData());
    }

    /**
     * @return array
     */
    protected function getExtensions(): array
    {
        $formType = new CustomerRegistrationFormType(
            $this->levelRepository,
            $this->posRepository,
            $this->sellerRepository,
            $this->translator
        );

        return [
            new PreloadedExtension([$formType], []),
            new ValidatorExtension($this->validator),
        ];
    }
}
