<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Command;

use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CustomerId;
use OpenLoyalty\Component\Campaign\Domain\Model\Coupon;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Campaign\Infrastructure\ReadModel\CouponUsageProjector;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RecreateCouponUsageCommand.
 */
class RecreateCouponUsageCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('oloy:coupon_usage:recreate');
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        /** @var LoggerInterface $logger */
        $logger = $this->getContainer()->get('logger');
        $customers = $this->getContainer()->get('oloy.user.read_model.repository.customer_details')->findAll();
        /** @var CouponUsageProjector $projector */
        $projector = $this->getContainer()->get('oloy.campaign.read_model.projector.coupon_usage');
        $projector->removeAll();

        /** @var CustomerDetails $customer */
        foreach ($customers as $customer) {
            foreach ($customer->getCampaignPurchases() as $campaignPurchase) {
                $logger->info('[coupon_usage] coupon '.$campaignPurchase->getCoupon()->getCode().' used', [
                    'campaignId' => $campaignPurchase->getCampaignId()->__toString(),
                    'customerId' => $customer->getCustomerId()->__toString(),
                    'coupon' => $campaignPurchase->getCoupon()->getCode(),
                ]);

                $projector->storeCouponUsage(
                    new CampaignId($campaignPurchase->getCampaignId()->__toString()),
                    new CustomerId($customer->getCustomerId()->__toString()),
                    new Coupon($campaignPurchase->getCoupon()->getCode())
                );
            }
        }
    }
}
