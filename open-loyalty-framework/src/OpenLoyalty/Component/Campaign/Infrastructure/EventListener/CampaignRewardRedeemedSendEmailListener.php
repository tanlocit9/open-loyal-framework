<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Campaign\Infrastructure\EventListener;

use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBoughtRepository;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Campaign\Infrastructure\Service\CampaignRewardRedeemedEmailSenderInterface;
use OpenLoyalty\Component\Customer\Domain\SystemEvent\CampaignUsageWasChangedSystemEvent;

/**
 * Class CampaignRewardRedeemedSendEmailListener.
 */
class CampaignRewardRedeemedSendEmailListener implements CampaignRewardRedeemedSendEmailListenerInterface
{
    /**
     * @var CampaignRewardRedeemedEmailSenderInterface
     */
    private $emailSender;

    /**
     * @var CampaignBoughtRepository
     */
    private $campaignBoughtRepository;

    /**
     * @var CampaignRepository
     */
    private $campaignRepository;

    /**
     * CampaignRewardRedeemedSendEmailListener constructor.
     *
     * @param CampaignRewardRedeemedEmailSenderInterface $emailSettings
     * @param CampaignBoughtRepository                   $campaignBoughtRepository
     * @param CampaignRepository                         $campaignRepository
     */
    public function __construct(
        CampaignRewardRedeemedEmailSenderInterface $emailSettings,
        CampaignBoughtRepository $campaignBoughtRepository,
        CampaignRepository $campaignRepository
    ) {
        $this->emailSender = $emailSettings;
        $this->campaignBoughtRepository = $campaignBoughtRepository;
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(CampaignUsageWasChangedSystemEvent $event): void
    {
        if ($event->isUsed()) {
            $campaign = $this->campaignRepository->byId(new CampaignId($event->getCampaignId()));
            if ($campaign->isFulfillmentTracking()) {
                $campaignBoughtId = CampaignBought::createIdFromString(
                    $event->getCampaignId(),
                    $event->getCustomerId(),
                    $event->getCouponCode(),
                    $event->getCouponId(),
                    $event->getTransactionId()
                );
                $campaignBought = $this->campaignBoughtRepository->findOneByCouponId($event->getCouponId());
                $this->emailSender->send($campaignBought);
            }
        }
    }
}
