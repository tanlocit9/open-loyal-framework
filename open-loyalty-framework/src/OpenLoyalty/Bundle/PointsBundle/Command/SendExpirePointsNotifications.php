<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\PointsBundle\Command;

use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Account\Infrastructure\Notifier\ExpirePointsNotifierInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class SendExpirePointsNotifications.
 */
class SendExpirePointsNotifications extends Command
{
    private const COMMAND_ID = 'send-expire-points-notifications';

    private const COMMAND_NAME = 'oloy:points:notify:expiration';

    /**
     * @var int
     */
    private $daysToExpire = 0;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var ExpirePointsNotifierInterface
     */
    private $expirePointsNotifier;

    /**
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * SendExpirePointsNotifications constructor.
     *
     * @param ExpirePointsNotifierInterface $expirePointsNotifier
     * @param SettingsManager               $settingsManager
     */
    public function __construct(ExpirePointsNotifierInterface $expirePointsNotifier, SettingsManager $settingsManager)
    {
        $this->expirePointsNotifier = $expirePointsNotifier;
        $this->settingsManager = $settingsManager;

        parent::__construct(self::COMMAND_NAME);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Send expire points notification to the users')
            ->addArgument('days-to-expire', InputArgument::OPTIONAL, 'Number of days to expire points')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $daysToExpire = $input->getArgument('days-to-expire');

        if (null !== $daysToExpire) {
            $this->daysToExpire = $daysToExpire;

            return;
        }

        $settingDaysToExpire = $this->settingsManager->getSettingByKey('expirePointsNotificationDays');

        if (null !== $settingDaysToExpire) {
            $this->daysToExpire = $settingDaysToExpire->getValue();

            return;
        }

        throw new \RuntimeException('No expiry date for points specified');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start(self::COMMAND_ID);

        try {
            $expireDate = new \DateTime(sprintf('+%d days', $this->daysToExpire));

            $this->expirePointsNotifier->sendNotificationsForPointsExpiringAt($expireDate);

            $this->io->success(sprintf(
                'Successfully sent %d notifications about expiring points!',
                $this->expirePointsNotifier->sentNotificationsCount()
            ));
        } catch (\Exception $exception) {
            $this->io->error($exception->getMessage());
        }

        $event = $stopwatch->stop(self::COMMAND_ID);

        if ($output->isVerbose()) {
            $this->io->comment(sprintf(
                'Sent requests with webhooks: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB',
                $this->expirePointsNotifier->sentNotificationsCount(),
                $event->getDuration(),
                $event->getMemory() / (1024 ** 2)
            ));
        }
    }
}
