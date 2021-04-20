<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Unit\Domain\Validator;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\Specification\CustomerPhoneSpecificationInterface;
use OpenLoyalty\Component\Customer\Domain\Validator\CustomerUniqueValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomerUniqueValidatorTest.
 */
final class CustomerUniqueValidatorTest extends TestCase
{
    /**
     * @var Repository
     */
    protected $customerDetailsRepository;

    /**
     * @var CustomerPhoneSpecificationInterface|MockObject
     */
    private $customerSpecification;

    public function setUp()
    {
        $customer1 = new CustomerDetails(new CustomerId('00000000-0000-0000-0000-000000000011'));
        $customer1->setEmail('a@a.com');
        $customer1->setLoyaltyCardNumber('1');
        $customer2 = new CustomerDetails(new CustomerId('00000000-0000-0000-0000-000000000012'));
        $customer2->setEmail('b@b.com');
        $customer2->setLoyaltyCardNumber('2');
        $customer3 = new CustomerDetails(new CustomerId('00000000-0000-0000-0000-000000000012'));
        $customer3->setEmail('c@c.com');
        $customer3->setLoyaltyCardNumber('3');
        $customers = [
            'a@a.com' => $customer1,
            'b@b.com' => $customer2,
            'c@c.com' => $customer3,
        ];

        $this->customerDetailsRepository = $this->getMockBuilder('Broadway\ReadModel\Repository')->getMock();
        $this->customerDetailsRepository->method('findBy')->with($this->logicalOr(
                $this->arrayHasKey('email'),
                $this->arrayHasKey('loyaltyCardNumber')
            ))
            ->will($this->returnCallback(function ($params) use ($customers) {
                if (isset($params['email'])) {
                    $email = $params['email'];

                    return array_filter($customers, function (CustomerDetails $customerDetails) use ($email) {
                        if ($customerDetails->getEmail() == $email) {
                            return true;
                        }

                        return false;
                    });
                }
                if (isset($params['loyaltyCardNumber'])) {
                    $loyaltyCardNumber = $params['loyaltyCardNumber'];

                    return array_filter($customers, function (CustomerDetails $customerDetails) use ($loyaltyCardNumber) {
                        if ($customerDetails->getLoyaltyCardNumber() == $loyaltyCardNumber) {
                            return true;
                        }

                        return false;
                    });
                }

                return [];
            }));

        $this->customerSpecification = $this->createMock(CustomerPhoneSpecificationInterface::class);
        $this->customerSpecification->method('isSatisfiedBy')->willReturn(true);
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Component\Customer\Domain\Exception\EmailAlreadyExistsException
     */
    public function it_throws_exception_when_email_is_not_unique(): void
    {
        $validator = new CustomerUniqueValidator($this->customerDetailsRepository, $this->customerSpecification);
        $validator->validateEmailUnique('a@a.com');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_not_throwing_exception_when_email_belongs_to_user(): void
    {
        $validator = new CustomerUniqueValidator($this->customerDetailsRepository, $this->customerSpecification);
        $validator->validateEmailUnique('a@a.com', new CustomerId('00000000-0000-0000-0000-000000000011'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_not_throwing_exception_when_email_is_unique(): void
    {
        $validator = new CustomerUniqueValidator($this->customerDetailsRepository, $this->customerSpecification);
        $validator->validateEmailUnique('a2@a.com');
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Component\Customer\Domain\Exception\LoyaltyCardNumberAlreadyExistsException
     */
    public function it_throws_exception_when_card_number_is_not_unique(): void
    {
        $validator = new CustomerUniqueValidator($this->customerDetailsRepository, $this->customerSpecification);
        $validator->validateLoyaltyCardNumberUnique('3', new CustomerId('00000000-0000-0000-0000-000000000011'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_not_throwing_exception_when_card_number_belongs_to_user(): void
    {
        $validator = new CustomerUniqueValidator($this->customerDetailsRepository, $this->customerSpecification);
        $validator->validateLoyaltyCardNumberUnique('1', new CustomerId('00000000-0000-0000-0000-000000000011'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_not_throwing_exception_when_card_is_unique(): void
    {
        $validator = new CustomerUniqueValidator($this->customerDetailsRepository, $this->customerSpecification);
        $validator->validateLoyaltyCardNumberUnique('11', new CustomerId('00000000-0000-0000-0000-000000000011'));
    }
}
