<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Tests\Integration\Domain\Specification;

use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Customer\Domain\Specification\CustomerPhoneIsUnique;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class CustomerPhoneIsUniqueTest.
 */
final class CustomerPhoneIsUniqueTest extends KernelTestCase
{
    private const EXISTING_CUSTOMER_ID = '00000000-0000-474c-b092-b0dd880c07e1';

    /**
     * @var CustomerPhoneIsUnique
     */
    private $customerPhoneUnique;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        static::bootKernel();

        /** @var CustomerDetailsRepository $customerDetails */
        $customerDetails = self::$kernel->getContainer()->get(CustomerDetailsRepository::class);
        $this->customerPhoneUnique = new CustomerPhoneIsUnique($customerDetails);
    }

    /**
     * @test
     */
    public function it_return_false_when_customer_with_given_phone_number_exists(): void
    {
        $withPlusPrefix = $this->customerPhoneUnique->isSatisfiedBy('+48234234000');
        $withoutPlusPrefix = $this->customerPhoneUnique->isSatisfiedBy('48234234000');
        $this->assertFalse($withPlusPrefix);
        $this->assertFalse($withoutPlusPrefix);
    }

    /**
     * @test
     */
    public function it_return_true_when_customer_with_given_phone_number_not_exists(): void
    {
        $withPlusPrefix = $this->customerPhoneUnique->isSatisfiedBy('+non_exists_phone_number');
        $withoutPlusPrefix = $this->customerPhoneUnique->isSatisfiedBy('non_exists_phone_number');
        $this->assertTrue($withPlusPrefix);
        $this->assertTrue($withoutPlusPrefix);
    }

    /**
     * @test
     */
    public function it_return_true_when_given_phone_number_exists_to_customer(): void
    {
        $withPlusPrefix = $this->customerPhoneUnique->isSatisfiedBy('+48234234000', self::EXISTING_CUSTOMER_ID);
        $withoutPlusPrefix = $this->customerPhoneUnique->isSatisfiedBy('48234234000', self::EXISTING_CUSTOMER_ID);
        $this->assertTrue($withPlusPrefix);
        $this->assertTrue($withoutPlusPrefix);
    }

    /**
     * @test
     */
    public function it_return_false_when_given_phone_number_exists_and_is_not_belong_to_user(): void
    {
        $withPlusPrefix = $this->customerPhoneUnique->isSatisfiedBy('+48456456000', self::EXISTING_CUSTOMER_ID);
        $withoutPlusPrefix = $this->customerPhoneUnique->isSatisfiedBy('48456456000', self::EXISTING_CUSTOMER_ID);
        $this->assertFalse($withPlusPrefix);
        $this->assertFalse($withoutPlusPrefix);
    }
}
