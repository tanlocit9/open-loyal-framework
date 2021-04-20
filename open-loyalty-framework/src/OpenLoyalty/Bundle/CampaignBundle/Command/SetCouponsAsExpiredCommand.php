<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Command;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Component\Customer\Domain\Command\ExpireBoughtCampaign;
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
 * Class SetCouponsAsExpiredCommand.
 */
class SetCouponsAsExpiredCommand extends Command
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
     * SetCouponsAsExpiredCommand constructor.
     *
     * @param CustomerDetailsRepository $customerDetailsRepository
     * @param CommandBus                $commandBus
     * @param LoggerInterface           $logger
     */
    public function __construct(
        CustomerDetailsRepository $customerDetailsRepository,
        CommandBus $commandBus,
        LoggerInterface $logger
    ) {
        $this->customerDetailsRepository = $customerDetailsRepository;
        $this->commandBus = $commandBus;
        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('oloy:coupons:set_expired')
            ->setDescription('Sets all coupons (campaign bought) which are valid for specific time as expired')
            ->addOption('progress-bar', null, InputOption::VALUE_NONE, 'Show progress bar')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input, OutputInterface $output): void
    {
        $customers = $this->customerDetailsRepository->findCustomersWithPurchasesToExpire();
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
            $campaignPurchases = $customer->getCampaignPurchases();
            foreach ($campaignPurchases as $campaignPurchase) {
                if ($campaignPurchase->getStatus() === CampaignPurchase::STATUS_ACTIVE
                    && $campaignPurchase->isUsed() === false
                    && $campaignPurchase->getActiveTo()
                    && $campaignPurchase->getActiveTo() < $date) {
                    $this->commandBus->dispatch(
                        new ExpireBoughtCampaign(
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
            if ($progressBarActive && $progressBarActive instanceof ProgressBar) {
                $progressBar->advance();
            }
        }
        if ($progressBarActive && $progressBarActive instanceof ProgressBar) {
            $progressBar->finish();
        }
    }
}
