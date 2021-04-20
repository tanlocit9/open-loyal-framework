<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UtilityBundle\Service;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Bundle\UserBundle\Service\AccountDetailsProviderInterface;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Segment\Domain\Segment;

/**
 * Class CustomerDetailsCsvFormatter.
 */
class CustomerDetailsCsvFormatter
{
    private const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var Repository
     */
    private $segmentedCustomersRepository;

    /**
     * @var Repository
     */
    private $levelCustomersRepository;

    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    /**
     * @var AccountDetailsProviderInterface
     */
    private $accountDetailsProvider;

    /**
     * CustomerDetailsCsvFormatter constructor.
     *
     * @param Repository                      $segmentedCustomersRepository
     * @param Repository                      $levelCustomersRepository
     * @param CustomerDetailsRepository       $customerDetailsRepository
     * @param AccountDetailsProviderInterface $accountDetailsProvider
     */
    public function __construct(
        Repository $segmentedCustomersRepository,
        Repository $levelCustomersRepository,
        CustomerDetailsRepository $customerDetailsRepository,
        AccountDetailsProviderInterface $accountDetailsProvider
    ) {
        $this->segmentedCustomersRepository = $segmentedCustomersRepository;
        $this->levelCustomersRepository = $levelCustomersRepository;
        $this->customerDetailsRepository = $customerDetailsRepository;
        $this->accountDetailsProvider = $accountDetailsProvider;
    }

    /**
     * @param Segment $segment
     *
     * @return array
     */
    public function getFormattedSegmentUsers($segment): array
    {
        /** @var array $customers */
        $customers = $this->segmentedCustomersRepository->findBy(['segmentId' => (string) $segment->getSegmentId()]);
        $customerDetails = [];
        /** @var CustomerDetails $customer */
        foreach ($customers as $customer) {
            $details = $this->customerDetailsRepository->find($customer->getCustomerId());
            if ($details instanceof CustomerDetails) {
                $customerDetails[(string) $customer->getCustomerId()] = $this->serializeCustomerDataForCsv($details);
            }
        }

        return $customerDetails;
    }

    /**
     * @param Level $level
     *
     * @return array
     */
    public function getFormattedLevelUsers($level): array
    {
        $customers = $this->levelCustomersRepository->findBy(['levelId' => (string) $level->getLevelId()]);
        $customerDetails = [];
        if (!$customers) {
            return $customerDetails;
        }

        /* @var CustomerDetails $customer */
        foreach (reset($customers)->getCustomers() as $customer) {
            $details = $this->customerDetailsRepository->find($customer['customerId']);
            if ($details instanceof CustomerDetails) {
                $accountDetails = $this->accountDetailsProvider->getAccountByCustomerId($details->getCustomerId());
                $customerDetails[$customer['customerId']] = $this->serializeCustomerDataForCsv($details, $accountDetails);
            }
        }

        return $customerDetails;
    }

    /**
     * @param CustomerDetails     $customerDetails
     * @param null|AccountDetails $accountDetails
     *
     * @return array
     */
    protected function serializeCustomerDataForCsv(CustomerDetails $customerDetails, AccountDetails $accountDetails = null): array
    {
        $birthDate = '';
        if ($customerDetails->getBirthDate()) {
            $birthDate = $customerDetails->getBirthDate()->format(self::DATE_TIME_FORMAT);
        }

        $detailsArray = [
            $customerDetails->getCustomerId(),
            $customerDetails->getFirstName(),
            $customerDetails->getLastName(),
            $customerDetails->getEmail(),
            $customerDetails->getGender(),
            $customerDetails->getPhone(),
            $customerDetails->getLoyaltyCardNumber(),
            $birthDate,
            $customerDetails->getCreatedAt()->format(self::DATE_TIME_FORMAT),
            $customerDetails->isAgreement1(),
            $customerDetails->isAgreement2(),
            $customerDetails->isAgreement3(),
        ];

        if (null !== $accountDetails) {
            $detailsArray[] = $accountDetails->getAvailableAmount();
            $detailsArray[] = $accountDetails->getUsedAmount();
        }
        $detailsArray[] = (new \DateTime())->format(self::DATE_TIME_FORMAT);

        return $detailsArray;
    }

    /**
     * @return array
     */
    public function getLevelUsersCsvMap(): array
    {
        return [
            'Customer id',
            'First name',
            'Last name',
            'E-mail address',
            'Gender',
            'Telephone',
            'Loyalty card number',
            'Birthdate',
            'Created at',
            'Legal agreement',
            'Marketing agreement',
            'Data processing agreement',
            'Active points',
            'Used points',
            'Export date',
        ];
    }

    /**
     * @return array
     */
    public function getSegmentUsersCsvMap(): array
    {
        return [
            'Customer id',
            'First name',
            'Last name',
            'E-mail address',
            'Gender',
            'Telephone',
            'Loyalty card number',
            'Birthdate',
            'Created at',
            'Legal agreement',
            'Marketing agreement',
            'Data processing agreement',
            'Export date',
        ];
    }
}
