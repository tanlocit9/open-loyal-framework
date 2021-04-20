<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Command;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Component\Customer\Domain\Command\ActivateBoughtCampaign;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SetCouponsAsActiveCommand.
 */
class SetCouponsAsActiveCommand extends Command
{
    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SetCouponsAsActiveCommand constructor.
     *
     * @param CustomerDetailsRepository $customerDetailsRepository
     * @param CommandBus                $commandBus
     * @param LoggerInterface           $logger
     */
    public function __construct(CustomerDetailsRepository $customerDetailsRepository, CommandBus $commandBus, LoggerInterface $logger)
    {
        $this->customerDetailsRepository = $customerDetailsRepository;
        $this->commandBus = $commandBus;
        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('oloy:coupons:set_active');
        $this->setDescription('Sets all coupons (campaign bought) which are invalid for specific time as active');
        $this->addOption('progress-bar', null, InputOption::VALUE_NONE, 'Show progress bar');
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $customers = $this->customerDetailsRepository->findCustomersWithPurchasesToActivate();
        $progressBarActive = $input->getOption('progress-bar');
        $progressBar = null;

        if ($progressBarActive) {
            $progressBar = new ProgressBar($output, count($customers));
            $progressBar->start();
        }

        $date = new \DateTime();

        /** @var CustomerDetails $customer */
        foreach ($customers as $customer) {
            /** @var CampaignPurchase $campaignPurchase */
            foreach ($customer->getCampaignPurchases() as $campaignPurchase) {
                if ($campaignPurchase->getStatus() === CampaignPurchase::STATUS_INACTIVE &&
                    $campaignPurchase->getActiveSince() && $campaignPurchase->getActiveSince() < $date) {
                    $this->commandBus->dispatch(
                        new ActivateBoughtCampaign(
                            $customer->getCustomerId(),
                            $campaignPurchase->getCampaignId(),
                            $campaignPurchase->getCoupon(),
                            $campaignPurchase->getTransactionId()
                        )
                    );

                    $this->logger->info('coupon activated '.$campaignPurchase->getCoupon()->getCode(), [
                        'campaignId' => $campaignPurchase->getCampaignId()->__toString(),
                        'customerId' => (string) $customer->getCustomerId(),
                        'coupon' => $campaignPurchase->getCoupon()->getCode(),
                        'couponId' => $campaignPurchase->getCoupon()->getId(),
                    ]);
                }
            }
            if ($progressBarActive && $progressBar instanceof ProgressBar) {
                $progressBar->advance();
            }
        }
        if ($progressBarActive && $progressBar instanceof ProgressBar) {
            $progressBar->finish();
        }
    }
}
