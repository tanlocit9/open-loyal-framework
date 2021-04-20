<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Command;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Bundle\UserBundle\Service\AccountDetailsProviderInterface;
use OpenLoyalty\Component\Account\Domain\Command\ResetPoints;
use OpenLoyalty\Component\Customer\Domain\Command\RecalculateCustomerLevel;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Customer\Infrastructure\Exception\LevelDowngradeModeNotSupportedException;
use OpenLoyalty\Component\Customer\Infrastructure\LevelDowngradeModeProvider;
use OpenLoyalty\Component\Customer\Infrastructure\TierAssignTypeProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class RecalculateLevelCommand.
 */
class RecalculateLevelCommand extends ContainerAwareCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    /**
     * @var LevelDowngradeModeProvider
     */
    private $levelDowngradeModeProvider;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var AccountDetailsProviderInterface
     */
    private $accountDetailsProvider;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var TierAssignTypeProvider
     */
    private $tierAssignTypeProvider;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('oloy:user:level:recalculate')
            ->setDescription('Recalculate user level based on settings, eg. every 365 days')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->customerDetailsRepository = $this->getContainer()->get('oloy.user.read_model.repository.customer_details');
        $this->levelDowngradeModeProvider = $this->getContainer()->get(LevelDowngradeModeProvider::class);
        $this->commandBus = $this->getContainer()->get('broadway.command_handling.command_bus');
        $this->accountDetailsProvider = $this->getContainer()->get(AccountDetailsProviderInterface::class);
        $this->translator = $this->getContainer()->get('translator');
        $this->tierAssignTypeProvider = $this->getContainer()->get('oloy.user.settings_based_tier_assign_type_provider');
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if ($this->tierAssignTypeProvider->getType() !== TierAssignTypeProvider::TYPE_POINTS) {
            $output->writeln(
                $this->translator->trans('customer.level.downgrade_mode.not_available_when_tier_assignment_type_is_not_points')
            );

            return;
        }

        try {
            $mode = $this->levelDowngradeModeProvider->getMode();
        } catch (LevelDowngradeModeNotSupportedException $exception) {
            $output->writeln($this->translator->trans('customer.level.downgrade_mode.not_supported'));

            return;
        }

        if ($mode !== LevelDowngradeModeProvider::MODE_X_DAYS) {
            $output->writeln($this->translator->trans('customer.level.downgrade_mode.not_supported'));

            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $days = $this->levelDowngradeModeProvider->getDays();
        $resetPoints = $this->levelDowngradeModeProvider->getBase() === LevelDowngradeModeProvider::BASE_ACTIVE_POINTS
            && $this->levelDowngradeModeProvider->isResettingPointsEnabled();
        $date = new \DateTime();

        $customers = $this->customerDetailsRepository->findAllForLevelRecalculation($date, $days);

        $progress = new ProgressBar($output, count($customers));

        $step = 1;
        foreach ($customers as $customer) {
            /** @var Customer $customer */
            $accountCustomer = $this->accountDetailsProvider->getCustomerById(new CustomerId($customer->getId()));

            $account = $this->accountDetailsProvider->getAccountByCustomer($accountCustomer);

            $this->commandBus->dispatch(new RecalculateCustomerLevel($customer->getCustomerId(), $date));

            $progress->setProgress($step);
            ++$step;
            if ($resetPoints) {
                $this->commandBus->dispatch(new ResetPoints($account->getAccountId(), $date));
            }
        }

        $progress->finish();
    }
}
