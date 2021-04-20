<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Service;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Transaction\Domain\CustomerIdProvider;

/**
 * Class SettingsBasedCustomerIdProvider.
 */
class SettingsBasedCustomerIdProvider implements CustomerIdProvider
{
    /**
     * @var SettingsManager
     */
    protected $settingsManager;

    /**
     * @var Repository
     */
    protected $customerDetailsRepository;

    /**
     * SettingsBasedCustomerIdProvider constructor.
     *
     * @param SettingsManager $settingsManager
     * @param Repository      $customerDetailsRepository
     */
    public function __construct(SettingsManager $settingsManager, Repository $customerDetailsRepository)
    {
        $this->settingsManager = $settingsManager;
        $this->customerDetailsRepository = $customerDetailsRepository;
    }

    /**
     * @param array $customerData
     *
     * @return string|null
     */
    public function getId(array $customerData)
    {
        $priority = $this->settingsManager->getSettingByKey('customersIdentificationPriority');
        if (!$priority) {
            $priority = [
                ['field' => 'phone'],
                ['field' => 'loyaltyCardNumber'],
                ['field' => 'email'],
            ];
        } else {
            $priority = $priority->getValue();
        }

        if (count($priority) == 0) {
            return;
        }

        foreach ($priority as $field) {
            if (!isset($customerData[$field['field']]) || $customerData[$field['field']] === '' || null === $customerData[$field['field']]) {
                continue;
            }
            $customers = $this->customerDetailsRepository->findBy(
                [
                    $field['field'] => strtolower($customerData[$field['field']]),
                ]
            );
            if (count($customers) == 0) {
                continue;
            }
            $customer = reset($customers);

            if ($customer instanceof CustomerDetails && $customer->isActive()) {
                return $customer->getId();
            }
        }

        return;
    }
}
