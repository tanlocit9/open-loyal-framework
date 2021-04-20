<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Service;

use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\CustomerRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Broadway\ReadModel\Repository;

/**
 * Class AccountDetailsProvider.
 */
class AccountDetailsProvider implements AccountDetailsProviderInterface
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * AccountDetailsProvider constructor.
     *
     * @param CustomerRepository $customerRepository
     * @param Repository         $accountRepository
     */
    public function __construct(CustomerRepository $customerRepository, Repository $accountRepository)
    {
        $this->customerRepository = $customerRepository;
        $this->accountRepository = $accountRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function getCustomerById(CustomerId $customerId): ?Customer
    {
        $customer = $this->customerRepository->load((string) $customerId);

        if (empty($customer->getId())) {
            throw new \Exception('Customer does not exist');
        }

        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccountByCustomer(Customer $customer): AccountDetails
    {
        $account = $this->validateAccount($this->accountRepository->findBy(['customerId' => (string) $customer->getId()]));

        return $account;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccountByCustomerId(CustomerId $customerId): AccountDetails
    {
        $account = $this->validateAccount($this->accountRepository->findBy(['customerId' => (string) $customerId]));

        return $account;
    }

    /**
     * @param iterable $account
     *
     * @return AccountDetails
     */
    private function validateAccount(iterable $account): AccountDetails
    {
        if (count($account) == 0) {
            throw new Exception('Account does not exist.');
        }
        /** @var AccountDetails $account */
        $account = reset($account);

        if (!$account instanceof AccountDetails) {
            throw new Exception('Account does not exist.');
        }

        return $account;
    }
}
