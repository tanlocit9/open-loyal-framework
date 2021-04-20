<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Validator;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Exception\EmailAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Exception\LoyaltyCardNumberAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Exception\PhoneAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\Specification\CustomerPhoneSpecificationInterface;

/**
 * Class CustomerUniqueValidator.
 */
class CustomerUniqueValidator
{
    /**
     * @var Repository
     */
    private $customerDetailsRepository;

    /**
     * @var CustomerPhoneSpecificationInterface
     */
    private $customerPhoneSpecification;

    /**
     * CustomerUniqueValidator constructor.
     *
     * @param Repository                          $customerDetailsRepository
     * @param CustomerPhoneSpecificationInterface $customerPhoneSpecification
     */
    public function __construct(
        Repository $customerDetailsRepository,
        CustomerPhoneSpecificationInterface $customerPhoneSpecification
    ) {
        $this->customerDetailsRepository = $customerDetailsRepository;
        $this->customerPhoneSpecification = $customerPhoneSpecification;
    }

    /**
     * @param string          $email
     * @param CustomerId|null $customerId
     *
     * @throws EmailAlreadyExistsException
     */
    public function validateEmailUnique(string $email, ?CustomerId $customerId = null): void
    {
        $customers = $this->customerDetailsRepository->findBy(['email' => strtolower($email)]);
        if ($customerId) {
            /** @var CustomerDetails $customer */
            foreach ($customers as $key => $customer) {
                if ($customer->getId() == $customerId->__toString()) {
                    unset($customers[$key]);
                }
            }
        }

        if (count($customers) > 0) {
            throw new EmailAlreadyExistsException();
        }
    }

    /**
     * @param string     $number
     * @param CustomerId $customerId
     *
     * @throws LoyaltyCardNumberAlreadyExistsException
     */
    public function validateLoyaltyCardNumberUnique(string $number, CustomerId $customerId): void
    {
        // no loyalty card number
        if (empty($number)) {
            return;
        }

        $customers = $this->customerDetailsRepository->findBy(['loyaltyCardNumber' => $number]);
        /** @var CustomerDetails $customer */
        foreach ($customers as $key => $customer) {
            if ($customer->getId() == (string) $customerId) {
                unset($customers[$key]);
            }
        }
        if (count($customers) > 0) {
            throw new LoyaltyCardNumberAlreadyExistsException();
        }
    }

    /**
     * @param string          $number
     * @param CustomerId|null $customerId
     *
     * @throws PhoneAlreadyExistsException
     */
    public function validatePhoneUnique(string $number, ?CustomerId $customerId = null): void
    {
        $customerId = null === $customerId ? null : (string) $customerId;
        if (false === $this->customerPhoneSpecification->isSatisfiedBy($number, $customerId)) {
            throw new PhoneAlreadyExistsException();
        }
    }
}
