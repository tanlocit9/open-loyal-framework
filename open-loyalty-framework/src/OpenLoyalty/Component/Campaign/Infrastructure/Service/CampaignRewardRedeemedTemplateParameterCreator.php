<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Infrastructure\Service;

use OpenLoyalty\Bundle\EmailBundle\DTO\EmailTemplateParameter;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Customer\Infrastructure\Provider\CustomerDetailsProviderInterface;
use OpenLoyalty\Component\Campaign\Infrastructure\Provider\RewardCampaignProviderInterface;

/**
 * Class CampaignRewardRedeemedTemplateParameterCreator.
 */
class CampaignRewardRedeemedTemplateParameterCreator implements CampaignRewardRedeemedTemplateParameterCreatorInterface
{
    /**
     * @var CustomerDetailsProviderInterface
     */
    private $customerDetailsProvider;

    /**
     * @var RewardCampaignProviderInterface
     */
    private $campaignProvider;

    /**
     * CampaignRewardRedeemedTemplateParameterCreator constructor.
     *
     * @param CustomerDetailsProviderInterface $customerDetailsProvider
     * @param RewardCampaignProviderInterface  $campaignProvider
     */
    public function __construct(
        CustomerDetailsProviderInterface $customerDetailsProvider,
        RewardCampaignProviderInterface $campaignProvider
    ) {
        $this->customerDetailsProvider = $customerDetailsProvider;
        $this->campaignProvider = $campaignProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function parameters(CampaignBought $campaignBought, string $templateName): EmailTemplateParameter
    {
        $customerId = new CustomerId((string) $campaignBought->getCustomerId());
        $customer = $this->customerDetailsProvider->getCustomerDetailsByCustomerId($customerId);

        $templateParameter = new EmailTemplateParameter($templateName);

        $templateParameter->addParameter('customer_name', $customer->getFirstName());
        $templateParameter->addParameter('customer_last_name', $customer->getLastName());
        $templateParameter->addParameter('customer_phone_number', $customer->getPhone());
        $templateParameter->addParameter('customer_email', $customer->getEmail());

        if (null !== $customer->getAddress()) {
            $templateParameter->addParameter('customer_street', $customer->getAddress()->getStreet());
            $templateParameter->addParameter('customer_address1', $customer->getAddress()->getAddress1());
            $templateParameter->addParameter('customer_address2', $customer->getAddress()->getAddress2());
            $templateParameter->addParameter('customer_city', $customer->getAddress()->getCity());
            $templateParameter->addParameter('customer_postal', $customer->getAddress()->getPostal());
            $templateParameter->addParameter('customer_state', $customer->getAddress()->getProvince());
            $templateParameter->addParameter('customer_country', $customer->getAddress()->getCountry());
        }

        $templateParameter->addParameter('coupon_code', $campaignBought->getCoupon()->getCode());

        $campaign = $this->campaignProvider->findById(new CampaignId((string) $campaignBought->getCampaignId()));

        $templateParameter->addParameter('reward_name', $campaign->getName());
        $templateParameter->addParameter('reward_description', $campaign->getConditionsDescription());

        return $templateParameter;
    }
}
