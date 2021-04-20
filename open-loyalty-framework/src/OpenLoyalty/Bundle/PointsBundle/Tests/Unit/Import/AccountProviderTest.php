<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Tests\Unit\Import;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Bundle\PointsBundle\Import\AccountProvider;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use PHPUnit\Framework\TestCase;
use OpenLoyalty\Component\Customer\Domain\CustomerId as CustomerCustomerId;

/**
 * Class AccountProviderTest.
 */
class AccountProviderTest extends TestCase
{
    private const ACCOUNT_ID = '00000000-1111-0000-0000-b0dd880c07ee';

    private const BY_CUSTOMER_ID_CUSTOMER_ID = '00000000-2222-0000-0000-b0dd880c0fff';
    private const BY_EMAIL_CUSTOMER_ID = '00000000-3333-0000-0000-b0dd880c0fff';
    private const BY_LOYALTY_CARD_CUSTOMER_ID = '00000000-4444-0000-0000-b0dd880c0fff';
    private const BY_PHONE_CUSTOMER_ID = '00000000-5555-0000-0000-b0dd880c0fff';

    /**
     * @test
     */
    public function it_provides_null_when_no_customer_data(): void
    {
        $customerRepositoryMock = $this->getCustomerRepository();
        $accountRepositoryMock = $this->getAccountRepository(null);

        $accountProvider = new AccountProvider($customerRepositoryMock, $accountRepositoryMock);
        $this->assertNull($accountProvider->provideOne(null, null, null, null));
    }

    /**
     * @test
     */
    public function it_provides_account_by_customer_id(): void
    {
        $customerRepositoryMock = $this->getCustomerRepository(self::BY_CUSTOMER_ID_CUSTOMER_ID);
        $accountRepositoryMock = $this->getAccountRepository(self::BY_CUSTOMER_ID_CUSTOMER_ID);

        $accountProvider = new AccountProvider($customerRepositoryMock, $accountRepositoryMock);
        $account = $accountProvider->provideOne(self::BY_CUSTOMER_ID_CUSTOMER_ID, null, null, null);

        $this->assertNotNull($account);
        $this->assertEquals(self::BY_CUSTOMER_ID_CUSTOMER_ID, (string) $account->getCustomerId());
    }

    /**
     * @test
     */
    public function it_provides_account_by_email(): void
    {
        $customerRepositoryMock = $this->getCustomerRepository(self::BY_EMAIL_CUSTOMER_ID);
        $accountRepositoryMock = $this->getAccountRepository();

        $accountProvider = new AccountProvider($customerRepositoryMock, $accountRepositoryMock);
        $account = $accountProvider->provideOne(null, self::BY_EMAIL_CUSTOMER_ID, null, null);

        $this->assertNotNull($account);
        $this->assertEquals(self::BY_EMAIL_CUSTOMER_ID, (string) $account->getCustomerId());
    }

    /**
     * @test
     */
    public function it_provides_account_by_loyaltycard(): void
    {
        $customerRepositoryMock = $this->getCustomerRepository(null, self::BY_LOYALTY_CARD_CUSTOMER_ID);
        $accountRepositoryMock = $this->getAccountRepository();

        $accountProvider = new AccountProvider($customerRepositoryMock, $accountRepositoryMock);
        $account = $accountProvider->provideOne(null, null, null, self::BY_LOYALTY_CARD_CUSTOMER_ID);

        $this->assertNotNull($account);
        $this->assertEquals(self::BY_LOYALTY_CARD_CUSTOMER_ID, (string) $account->getCustomerId());
    }

    /**
     * @test
     */
    public function it_provides_account_by_phone(): void
    {
        $customerRepositoryMock = $this->getCustomerRepository(null, null, self::BY_PHONE_CUSTOMER_ID);
        $accountRepositoryMock = $this->getAccountRepository();

        $accountProvider = new AccountProvider($customerRepositoryMock, $accountRepositoryMock);
        $account = $accountProvider->provideOne(null, null, self::BY_PHONE_CUSTOMER_ID, null);

        $this->assertNotNull($account);
        $this->assertEquals(self::BY_PHONE_CUSTOMER_ID, (string) $account->getCustomerId());
    }

    /**
     * @test
     */
    public function it_provides_account_by_email_first(): void
    {
        $customerRepositoryMock = $this->getCustomerRepository(self::BY_EMAIL_CUSTOMER_ID, self::BY_LOYALTY_CARD_CUSTOMER_ID, self::BY_PHONE_CUSTOMER_ID);
        $accountRepositoryMock = $this->getAccountRepository();

        $accountProvider = new AccountProvider($customerRepositoryMock, $accountRepositoryMock);
        $account = $accountProvider->provideOne(null, self::BY_EMAIL_CUSTOMER_ID, self::BY_PHONE_CUSTOMER_ID, self::BY_LOYALTY_CARD_CUSTOMER_ID);

        $this->assertNotNull($account);
        $this->assertEquals(self::BY_EMAIL_CUSTOMER_ID, (string) $account->getCustomerId());
    }

    /**
     * @test
     */
    public function it_does_not_provide_account_when_does_not_exist(): void
    {
        $customerRepositoryMock = $this->getCustomerRepository();
        $accountRepositoryMock = $this->getAccountRepository();

        $accountProvider = new AccountProvider($customerRepositoryMock, $accountRepositoryMock);
        $account = $accountProvider->provideOne(null, self::BY_EMAIL_CUSTOMER_ID, self::BY_PHONE_CUSTOMER_ID, self::BY_LOYALTY_CARD_CUSTOMER_ID);

        $this->assertNull($account);
    }

    /**
     * @param string|null $customerId
     *
     * @return \MockObject|Repository
     */
    protected function getAccountRepository(?string $customerId = null): Repository
    {
        $accountRepositoryMock = $this->getMockBuilder(Repository::class)->getMock();
        $accountRepositoryMock->method('findBy')->willReturnCallback(function (array $fields) use ($customerId) {
            if (isset($fields['customerId']) && (null === $customerId || $fields['customerId'] === $customerId)) {
                return [new AccountDetails(
                    new AccountId(self::ACCOUNT_ID),
                    new CustomerId($fields['customerId'])
                )];
            }

            return [];
        });

        return $accountRepositoryMock;
    }

    /**
     * @param null|string $email
     * @param null|string $loyaltyCardNumber
     * @param null|string $phone
     *
     * @return \MockObject|CustomerDetailsRepository
     */
    protected function getCustomerRepository(?string $email = null, ?string $loyaltyCardNumber = null, ?string $phone = null): CustomerDetailsRepository
    {
        $customerRepositoryMock = $this->getMockBuilder(CustomerDetailsRepository::class)->getMock();
        $customerRepositoryMock->method('findBy')->willReturnCallback(function (array $fields) use ($email, $loyaltyCardNumber, $phone) {
            if (isset($fields['email']) && $fields['email'] === $email) {
                return [new CustomerDetails(new CustomerCustomerId(self::BY_EMAIL_CUSTOMER_ID))];
            }

            if (isset($fields['loyaltyCardNumber']) && $fields['loyaltyCardNumber'] === $loyaltyCardNumber) {
                return [new CustomerDetails(new CustomerCustomerId(self::BY_LOYALTY_CARD_CUSTOMER_ID))];
            }

            if (isset($fields['phone']) && $fields['phone'] === $phone) {
                return [new CustomerDetails(new CustomerCustomerId(self::BY_PHONE_CUSTOMER_ID))];
            }

            return [];
        });

        return $customerRepositoryMock;
    }
}
