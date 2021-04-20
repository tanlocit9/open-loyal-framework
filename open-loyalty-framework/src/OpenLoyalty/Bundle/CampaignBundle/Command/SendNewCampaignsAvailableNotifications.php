<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\CampaignBundle\Command;

use OpenLoyalty\Bundle\CampaignBundle\Service\CampaignProvider;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Bundle\UserBundle\Service\NotificationService;
use OpenLoyalty\Bundle\UserBundle\Service\UserSettingsManager;
use OpenLoyalty\Bundle\UserBundle\Service\UserSettingsManagerFactory;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\Exception\NoAvailableCampaignsCacheSaved;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\AvailableCampaignsCache;
use OpenLoyalty\Component\Campaign\Infrastructure\Persistence\Doctrine\Repository\DoctrineCampaignRepository;
use OpenLoyalty\Component\Campaign\Infrastructure\Repository\AvailableCampaignsCacheElasticsearchRepository;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SendNewCampaignsAvailableNotifications.
 */
class SendNewCampaignsAvailableNotifications extends Command
{
    private const COMMAND_ID = 'send-new-campaigns-available-notifications';

    private const COMMAND_NAME = 'oloy:campaigns:notify:new_available';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * @var UserSettingsManagerFactory
     */
    private $userSettingsManagerFactory;

    /**
     * @var UserSettingsManager[]
     */
    private $userSettingsManagers;

    /**
     * @var CampaignProvider
     */
    private $campaignProvider;

    /**
     * @var DoctrineCampaignRepository
     */
    private $campaignRepository;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @var AvailableCampaignsCacheElasticsearchRepository
     */
    private $cacheRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Campaign[]
     */
    private $campaigns = [];

    /**
     * SendNewCampaignsAvailableNotifications constructor.
     *
     * @param DoctrineCampaignRepository                     $campaignRepository
     * @param CampaignProvider                               $campaignProvider
     * @param SettingsManager                                $settingsManager
     * @param UserSettingsManagerFactory                     $userSettingsManagerFactory
     * @param NotificationService                            $notificationService
     * @param AvailableCampaignsCacheElasticsearchRepository $cacheRepository
     * @param TranslatorInterface                            $translator
     */
    public function __construct(
        DoctrineCampaignRepository $campaignRepository,
        CampaignProvider $campaignProvider,
        SettingsManager $settingsManager,
        UserSettingsManagerFactory $userSettingsManagerFactory,
        NotificationService $notificationService,
        AvailableCampaignsCacheElasticsearchRepository $cacheRepository,
        TranslatorInterface $translator
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->campaignProvider = $campaignProvider;
        $this->settingsManager = $settingsManager;
        $this->userSettingsManagerFactory = $userSettingsManagerFactory;
        $this->notificationService = $notificationService;
        $this->cacheRepository = $cacheRepository;
        $this->translator = $translator;
        $this->userSettingsManagers = [];

        parent::__construct(self::COMMAND_NAME);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Send notifications to the users to whom new campaigns have become available.')
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
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start(self::COMMAND_ID);

        try {
            $campaigns = $this->campaignRepository->getActiveCampaignsWithPushNotificationText();

            // create an array of [campaignId => array of customerIds]
            $usersVisibleTo = array_column(array_map(
                function (Campaign $campaign) {
                    return [
                        array_values($this->campaignProvider->visibleForCustomers($campaign)),
                        (string) $campaign->getCampaignId(),
                    ];
                },
                $campaigns
            ), 0, 1);

            $usersAlreadyInformed = $this->getVisibleToFromCache();

            $sendQueue = $this->getUsersToBeNotified($campaigns, $usersAlreadyInformed, $usersVisibleTo);
            $pushyQueue = $this->getPushyTokens($sendQueue);
            $this->sendNotifications($pushyQueue);

            $this->saveVisibleToToCache($usersVisibleTo);

            $this->io->success('Push notifications for new campaigns available were sent!');
        } catch (NoAvailableCampaignsCacheSaved $exception) {
            // save already available reward campaigns for the first time or after Elasticsearch indexes rebuild
            if (isset($usersVisibleTo)) {
                $this->saveVisibleToToCache($usersVisibleTo);
            }

            $this->io->warning($exception->getMessage());
        } catch (\Exception $exception) {
            $this->io->error($exception->getMessage());
        }

        $event = $stopwatch->stop(self::COMMAND_ID);

        // debug information
        if ($output->isVeryVerbose()) {
            $this->io->table(
                ['campaignId', 'userIds'],
                array_map(
                    function ($k, $v) {
                        return [$k, join("\n", $v) ?: '-'];
                    },
                    array_keys($usersVisibleTo ?? []),
                    $usersVisibleTo ?? []
                ) ?? []
            );

            $this->io->table(
                ['campaignId', 'userIds'],
                $sendQueue ?? []
            );

            $this->io->table(
                ['campaignId', 'pushyTokens'],
                array_map(
                    function ($k, $v) {
                        return [$k, join("\n", $v) ?: '-'];
                    },
                    array_keys($pushyQueue ?? []),
                    $pushyQueue ?? []
                ) ?? []
            );
        }

        if ($output->isVerbose()) {
            $this->io->comment(sprintf(
                'User-campaign pairs: %d / Pushy messages sent: %d',
                count($sendQueue ?? []),
                count($pushyQueue ?? [])
            ));

            $this->io->comment(sprintf(
                'Elapsed time: %.2f ms / Consumed memory: %.2f MB',
                $event->getDuration(),
                $event->getMemory() / (1024 ** 2)
            ));
        }
    }

    /**
     * @param array $campaigns
     * @param array $usersAlreadyInformed
     * @param array $usersVisibleTo
     *
     * @return array
     */
    private function getUsersToBeNotified(array $campaigns, array $usersAlreadyInformed, array $usersVisibleTo)
    {
        $sendQueue = [];

        /** @var Campaign $campaign */
        foreach ($campaigns as $campaign) {
            $campaignId = (string) $campaign->getCampaignId();

            if (isset($usersAlreadyInformed[$campaignId])) {
                // if the campaign isn't new, add customers who joined the target
                foreach ($usersVisibleTo[$campaignId] as $userId) {
                    if (!in_array($userId, $usersAlreadyInformed[$campaignId])) {
                        $sendQueue[] = ['campaignId' => $campaignId, 'userId' => $userId];
                    }
                }
            } else {
                // if the campaign is new, add all customers
                foreach ($usersVisibleTo[$campaignId] as $userId) {
                    $sendQueue[] = ['campaignId' => $campaignId, 'userId' => $userId];
                }
            }
        }

        return $sendQueue;
    }

    /**
     * @param $sendQueue
     *
     * @return array
     *
     * @throws \Assert\AssertionFailedException
     */
    private function getPushyTokens($sendQueue): array
    {
        $pushyQueue = [];

        foreach ($sendQueue as ['campaignId' => $campaignId, 'userId' => $userId]) {
            $this->userSettingsManagers[$userId] = $this->userSettingsManagers[$userId]
                ?? $this->userSettingsManagerFactory->createForUser(new CustomerId($userId));
            $pushyTokens = $this->userSettingsManagers[$userId]->getPushyTokens();
            if (!empty($pushyTokens)) {
                $pushyQueue[$campaignId] = array_merge($pushyQueue[$campaignId] ?? [], $pushyTokens);
            }
        }

        return $pushyQueue;
    }

    /**
     * @param $pushyQueue
     */
    private function sendNotifications($pushyQueue): void
    {
        foreach ($pushyQueue as $campaignId => $pushyTokens) {
            $campaign = $this->getCampaign($campaignId);

            $labels = [];
            foreach ($campaign->getLabels() as $label) {
                $labels[] = $label->serialize();
            }

            $this->notificationService->sendRewardAvailableNotification([
                'recipientTokens' => $pushyTokens,
                'data' => [
                    'title' => $campaign->getName(),
                    'message' => $campaign->getPushNotificationText(),
                    'campaignId' => $campaignId,
                    'labels' => $labels,
                ],
            ]);
        }
    }

    /**
     * @param string $campaignId
     *
     * @return Campaign
     */
    private function getCampaign(string $campaignId): Campaign
    {
        if (!array_key_exists($campaignId, $this->campaigns)) {
            $this->campaigns[$campaignId] = $this->campaignRepository->find($campaignId);
        }

        return $this->campaigns[$campaignId];
    }

    /**
     * @return array
     *
     * @throws NoAvailableCampaignsCacheSaved
     */
    private function getVisibleToFromCache()
    {
        $visibleTo = $this->cacheRepository->find('visible_to');

        if ($visibleTo === null) {
            throw NoAvailableCampaignsCacheSaved::create();
        }

        return $visibleTo->getVisibleTo();
    }

    /**
     * @param array $usersVisibleTo
     */
    private function saveVisibleToToCache(array $usersVisibleTo)
    {
        $data = new AvailableCampaignsCache($usersVisibleTo);
        $this->cacheRepository->save($data);
    }
}
