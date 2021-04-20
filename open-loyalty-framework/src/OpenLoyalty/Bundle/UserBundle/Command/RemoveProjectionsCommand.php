<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Command;

use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBoughtRepository;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RemoveProjectionsCommand.
 */
class RemoveProjectionsCommand extends ContainerAwareCommand
{
    protected $repos = [
        'oloy.user.read_model.repository.customer_details',
        'oloy.user.read_model.repository.invitation_details',
        'oloy.points.account.repository.account_details',
        'oloy.points.account.repository.points_transfer_details',
        'oloy.user.read_model.repository.customers_belonging_to_one_level',
        TransactionDetailsRepository::class,
        'oloy.user.read_model.repository.seller_details',
        'oloy.segment.read_model.repository.segmented_customers',
        'oloy.campaign.read_model.repository.coupon_usage',
        'oloy.campaign.read_model.repository.campaign_usage',
        CampaignBoughtRepository::class,
    ];

    protected function configure()
    {
        $this->setName('oloy:user:projections:purge');
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->repos as $repo) {
            $repo = $this->getContainer()->get($repo);
            $all = $repo->findAll();
            foreach ($all as $projection) {
                $repo->remove($projection->getId());
            }
        }
    }
}
