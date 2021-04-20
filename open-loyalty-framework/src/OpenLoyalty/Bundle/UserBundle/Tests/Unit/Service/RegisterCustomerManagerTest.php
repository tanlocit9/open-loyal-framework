<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Service;

use Broadway\CommandHandling\CommandBus;
use Doctrine\ORM\EntityManager;
use OpenLoyalty\Bundle\LevelBundle\DataFixtures\ORM\LoadLevelData;
use OpenLoyalty\Bundle\UserBundle\DataFixtures\ORM\LoadUserData;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Service\RegisterCustomerManager;
use OpenLoyalty\Bundle\UserBundle\Service\UserManager;
use OpenLoyalty\Component\Customer\Domain\Command\MoveCustomerToLevel;
use OpenLoyalty\Component\Customer\Domain\Command\RegisterCustomer;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerAddress;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerCompanyDetails;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerLoyaltyCardNumber;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Exception\LoyaltyCardNumberAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Exception\PhoneAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\LevelIdProvider;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Customer\Domain\Validator\CustomerUniqueValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class RegisterCustomerManagerTest.
 */
class RegisterCustomerManagerTest extends TestCase
{
    /**
     * @param UserManager|null             $userManagerMock
     * @param CustomerUniqueValidator|null $customerUniqueValidator
     * @param CommandBus|null              $commandBus
     * @param null|LevelIdProvider         $levelIdProvider
     *
     * @return RegisterCustomerManager
     */
    protected function getRegisterCustomerManagerInstance(
        ?UserManager $userManagerMock = null,
        ?CustomerUniqueValidator $customerUniqueValidator = null,
        ?CommandBus $commandBus = null,
        ?LevelIdProvider $levelIdProvider = null
    ): RegisterCustomerManager {
        $userManagerMock = $userManagerMock
            ?? $this->getMockBuilder(UserManager::class)->disableOriginalConstructor()->getMock();

        $userManagerMock->method('createNewCustomer')
                ->willReturn(new Customer(new CustomerId('00000000-0000-474c-b092-b0dd880c07e2')));

        $customerUniqueValidator = $customerUniqueValidator
            ?? $this->getMockBuilder(CustomerUniqueValidator::class)->disableOriginalConstructor()->getMock();
        $commandBus = $commandBus
            ?? $this->getMockBuilder(CommandBus::class)->disableOriginalConstructor()->getMock();

        /** @var CustomerDetailsRepository|MockObject $customerRepository */
        $customerRepository = $this->getMockBuilder(CustomerDetailsRepository::class)
            ->disableOriginalConstructor()->getMock();

        /** @var EntityManager|MockObject $entityManager */
        $entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();

        /** @var TranslatorInterface|MockObject $translator */
        $translator = $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock();

        /** @var LevelIdProvider|MockObject $levelIdProviderMock */
        $levelIdProviderMock = $levelIdProvider ?? $this->getMockBuilder(LevelIdProvider::class)->getMock();

        return new RegisterCustomerManager(
            $userManagerMock,
            $customerUniqueValidator,
            $commandBus,
            $customerRepository,
            $entityManager,
            $translator,
            $levelIdProviderMock
        );
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Component\Customer\Domain\Exception\EmailAlreadyExistsException
     */
    public function it_throws_exception_when_customer_with_the_same_email_exist()
    {
        $userManagerMock = $this->getMockBuilder(UserManager::class)->disableOriginalConstructor()->getMock();
        $userManagerMock->expects($this->once())->method('isCustomerExist')->willReturn(true);

        /** @var LevelIdProvider|MockObject $levelIdProviderMock */
        $levelIdProviderMock = $this->getMockBuilder(LevelIdProvider::class)->getMock();
        $levelIdProviderMock->method('findLevelIdByConditionValueWithTheBiggestReward')->willReturn(LoadLevelData::LEVEL0_ID);

        $customerManager = $this->getRegisterCustomerManagerInstance($userManagerMock, null, null, $levelIdProviderMock);
        $customerManager->register(
            new CustomerId(LoadUserData::TEST_USER_ID),
            [
                'email' => 'mock@example.com',
            ]
        );
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Component\Customer\Domain\Exception\LoyaltyCardNumberAlreadyExistsException
     */
    public function it_throws_exception_when_customer_with_the_same_loyalty_card_exist()
    {
        $customerUniqueValidator = $this->getMockBuilder(CustomerUniqueValidator::class)->disableOriginalConstructor()->getMock();
        $customerUniqueValidator->expects($this->once())->method('validateLoyaltyCardNumberUnique')
            ->willThrowException(new LoyaltyCardNumberAlreadyExistsException());

        /** @var LevelIdProvider|MockObject $levelIdProviderMock */
        $levelIdProviderMock = $this->getMockBuilder(LevelIdProvider::class)->getMock();
        $levelIdProviderMock->method('findLevelIdByConditionValueWithTheBiggestReward')->willReturn(LoadLevelData::LEVEL0_ID);

        $customerManager = $this->getRegisterCustomerManagerInstance(null, $customerUniqueValidator, null, $levelIdProviderMock);
        $customerManager->register(
            new CustomerId(LoadUserData::TEST_USER_ID),
            [
                'email' => 'mock@example.com',
                'loyaltyCardNumber' => '123456',
            ]
        );
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Component\Customer\Domain\Exception\PhoneAlreadyExistsException
     */
    public function it_throws_exception_when_customer_with_the_same_phone_exist()
    {
        $customerUniqueValidator = $this->getMockBuilder(CustomerUniqueValidator::class)->disableOriginalConstructor()->getMock();
        $customerUniqueValidator->expects($this->once())->method('validatePhoneUnique')
            ->willThrowException(new PhoneAlreadyExistsException());

        /** @var LevelIdProvider|MockObject $levelIdProviderMock */
        $levelIdProviderMock = $this->getMockBuilder(LevelIdProvider::class)->getMock();
        $levelIdProviderMock->method('findLevelIdByConditionValueWithTheBiggestReward')->willReturn(LoadLevelData::LEVEL0_ID);

        $customerManager = $this->getRegisterCustomerManagerInstance(null, $customerUniqueValidator, null, $levelIdProviderMock);
        $customerManager->register(
            new CustomerId(LoadUserData::TEST_USER_ID),
            [
                'email' => 'mock@example.com',
                'loyaltyCardNumber' => '123456',
                'phone' => '+48123123',
            ]
        );
    }

    /**
     * @test
     */
    public function it_dispatch_only_register_command()
    {
        $commandBus = $this->getMockBuilder(CommandBus::class)->disableOriginalConstructor()->getMock();
        $commandBus->expects($this->once())->method('dispatch')
            ->with($this->isInstanceOf(RegisterCustomer::class));

        /** @var LevelIdProvider|MockObject $levelIdProviderMock */
        $levelIdProviderMock = $this->getMockBuilder(LevelIdProvider::class)->getMock();
        $levelIdProviderMock->method('findLevelIdByConditionValueWithTheBiggestReward')->willReturn(LoadLevelData::LEVEL0_ID);

        $customerManager = $this->getRegisterCustomerManagerInstance(null, null, $commandBus, $levelIdProviderMock);
        $customerManager->register(
            new CustomerId(LoadUserData::TEST_USER_ID),
            [
                'email' => 'mock@example.com',
                'phone' => '+48123123',
            ]
        );
    }

    /**
     * @test
     */
    public function it_dispatch_register_command_and_related_commands()
    {
        $commandBus = $this->getMockBuilder(CommandBus::class)->disableOriginalConstructor()->getMock();
        $commandBus->expects($this->exactly(5))->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(RegisterCustomer::class)],
                [$this->isInstanceOf(UpdateCustomerAddress::class)],
                [$this->isInstanceOf(UpdateCustomerCompanyDetails::class)],
                [$this->isInstanceOf(UpdateCustomerLoyaltyCardNumber::class)],
                [$this->isInstanceOf(MoveCustomerToLevel::class)]
            );

        /** @var LevelIdProvider|MockObject $levelIdProviderMock */
        $levelIdProviderMock = $this->getMockBuilder(LevelIdProvider::class)->getMock();
        $levelIdProviderMock->method('findLevelIdByConditionValueWithTheBiggestReward')->willReturn(LoadLevelData::LEVEL0_ID);

        $customerManager = $this->getRegisterCustomerManagerInstance(null, null, $commandBus, $levelIdProviderMock);
        $customerManager->register(
            new CustomerId(LoadUserData::TEST_USER_ID),
            [
                'email' => 'mock@example.com',
                'phone' => '+48123123',
                'address' => '',
                'company' => [
                    'name' => 'company_name',
                    'nip' => '889-11-22-981',
                ],
                'loyaltyCardNumber' => '123456',
                'level' => 'f99748f2-bf86-11e6-a4a6-cec0c932ce01',
            ]
        );
    }
}
