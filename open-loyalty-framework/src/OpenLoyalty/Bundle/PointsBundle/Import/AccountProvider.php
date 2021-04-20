<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Import;

use Broadway\ReadModel\Identifiable;
use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;

/**
 * Class AccountProvider.
 */
class AccountProvider implements AccountProviderInterface
{
    /**
     * @var CustomerDetailsRepository
     */
    private $customerRepository;

    /**
     * @var Repository
     */
    protected $accountRepository;

    /**
     * AccountProvider constructor.
     *
     * @param CustomerDetailsRepository $customerRepository
     * @param Repository                $accountRepository
     */
    public function __construct(CustomerDetailsRepository $customerRepository, Repository $accountRepository)
    {
        $this->customerRepository = $customerRepository;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param string|null $customerId
     * @param string|null $email
     * @param string|null $phone
     * @param string|null $loyaltyNumber
     *
     * @return null|AccountDetails
     */
    public function provideOne(?string $customerId, ?string $email, ?string $phone, ?string $loyaltyNumber): ?AccountDetails
    {
        if ($customerId && $account = $this->normalizeResult($this->accountRepository->findBy(['customerId' => $customerId]))) {
            return $account;
        }

        $customerFields = [
            'email' => $email,
            'loyaltyCardNumber' => $loyaltyNumber,
            'phone' => $phone,
        ];

        foreach ($customerFields as $field => $value) {
            if ($value && $customer = $this->normalizeResult($this->customerRepository->findBy([$field => $value]))) {
                $accounts = $this->accountRepository->findBy(['customerId' => $customer->getId()]);

                return $this->normalizeResult($accounts);
            }
        }

        return null;
    }

    /**
     * @param array|null $items
     *
     * @return Identifiable|null
     */
    protected function normalizeResult(?array $items): ?Identifiable
    {
        if (0 !== count($items)) {
            return reset($items);
        }

        return null;
    }
}
