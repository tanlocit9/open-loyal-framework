<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Form\Type;

use OpenLoyalty\Bundle\SettingsBundle\Entity\BooleanSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Bundle\UserBundle\Form\Listener\AllowUserToEditProfileSubscriber;
use OpenLoyalty\Bundle\UserBundle\Form\Type\CustomerEditFormType;
use OpenLoyalty\Bundle\UserBundle\Form\Type\CustomerRegistrationFormType;
use OpenLoyalty\Component\Level\Domain\LevelRepository;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetailsRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CustomerEditFormTypeCase.
 */
abstract class CustomerEditFormTypeCase extends TypeTestCase
{
    /**
     * @return bool
     */
    abstract protected function isAllowCustomersProfileEdits(): bool;

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $settings = $this->createMock(SettingsManager::class);
        $settings
            ->method('getSettingByKey')
            ->willReturn(new BooleanSettingEntry('allowCustomersProfileEdits', $this->isAllowCustomersProfileEdits()))
        ;

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn(new User('test', ''));

        /** @var TranslatorInterface|MockObject $translator */
        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturn('You don\'t have permission to edit this data.')
        ;

        $subscriber = new AllowUserToEditProfileSubscriber($settings, $tokenStorage, $translator);

        $customerEditFormType = new CustomerEditFormType(
            $subscriber
        );

        $levelRepository = $this->createMock(LevelRepository::class);
        $sellerRepository = $this->createMock(SellerDetailsRepository::class);
        $posRepository = $this->createMock(PosRepository::class);

        $customerRegistrationFormType = new CustomerRegistrationFormType(
            $levelRepository,
            $posRepository,
            $sellerRepository,
            $translator
        );

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->method('validate')
            ->will($this->returnValue(new ConstraintViolationList()))
        ;

        $metadata = $this
            ->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $metadata->method('addConstraint')->willReturn(true);
        $metadata->method('addPropertyConstraint')->willReturn(true);
        $validator->method('getMetadataFor')->willReturn($metadata);

        return [
            new PreloadedExtension([$customerEditFormType, $customerRegistrationFormType], []),
            new ValidatorExtension($validator),
        ];
    }
}
