<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyaltyPlugin\SalesManagoBundle\Service;

use GuzzleHttp\Exception\RequestException;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyaltyPlugin\SalesManagoBundle\Config\Config;
use OpenLoyaltyPlugin\SalesManagoBundle\Config\InvalidConfigException;
use OpenLoyaltyPlugin\SalesManagoBundle\DataProvider\DataProviderInterface;
use OpenLoyaltyPlugin\SalesManagoBundle\Entity\Deadletter;
use OpenLoyaltyPlugin\SalesManagoBundle\Repository\DeadletterRepository;
use Pixers\SalesManagoAPI\Client;
use Pixers\SalesManagoAPI\Exception\InvalidRequestException;
use Pixers\SalesManagoAPI\SalesManago;
use Psr\Log\LoggerInterface;

/**
 * Class SalesManagoContactSender.
 */
abstract class SalesManagoContactSender
{
    /**
     * @var SalesManago
     */
    protected $connector;

    /**
     * @var string
     */
    protected $ownerEmail;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * @var DeadletterRepository
     */
    protected $deadletterRepository;

    /**
     * @var SettingsManager
     */
    protected $settings;

    /**
     * @var Config
     */
    protected $config;

    /**
     * SalesManagoContactSender constructor.
     *
     * @param LoggerInterface       $logger
     * @param DataProviderInterface $dataProvider
     * @param DeadletterRepository  $deadletterRepository
     * @param SettingsManager       $settingsManager
     * @param Config                $config
     *
     * @throws InvalidConfigException
     */
    public function __construct(
        LoggerInterface $logger,
        DataProviderInterface $dataProvider,
        DeadletterRepository $deadletterRepository,
        SettingsManager $settingsManager,
        Config $config
    ) {
        $this->logger = $logger;
        $this->dataProvider = $dataProvider;
        $this->deadletterRepository = $deadletterRepository;
        $this->settings = $settingsManager;
        $this->config = $config;
        $this->createClient();
    }

    /**
     * Creates a SalesManago client instance.
     */
    protected function createClient()
    {
        $isActive = $this->settings->getSettingByKey('marketingVendorsValue');

        if ($isActive && $isActive->getValue() === Config::KEY) {
            $config = $this->settings->getSettingByKey(Config::KEY);

            if ($config) {
                $configArray = $config->getValue();

                // validate configuration
                $requiredConfigFields = array_keys($this->config->getSettingsConfig());
                $isValid = true;
                foreach ($requiredConfigFields as $requiredConfigField) {
                    if (!array_key_exists($requiredConfigField, $configArray)) {
                        $isValid = false;
                    }
                    if (empty($configArray[$requiredConfigField])) {
                        $isValid = false;
                    }
                }

                // if configuration is not valid then do nothing
                if (!$isValid) {
                    return;
                }

                $endpoint = $configArray['api_url'];
                $apiSecret = $configArray['api_secret'];
                $apiKey = $configArray['api_key'];
                $customerId = $configArray['customer_id'];
                try {
                    $this->connector = $this->createConnector(new Client($customerId, $endpoint, $apiSecret, $apiKey));
                } catch (\Exception $exception) {
                    $this->logger->debug(json_encode($exception->getMessage()));
                }

                $this->ownerEmail = $configArray['email'];
            }
        }
    }

    /**
     * @param $client
     *
     * @return SalesManago
     */
    protected function createConnector($client)
    {
        return new SalesManago($client);
    }

    /**
     * For those with more time and resources - move it to RabbitMQ, and add proper workers  - this slows down a lot.
     *
     * @param string $customerEmail
     * @param array  $tag
     */
    public function send($customerEmail, $tag)
    {
        if ($customerEmail !== null) {
            try {
                $response = $this->connector->getTagService()->modify(
                    $this->ownerEmail,
                    $customerEmail,
                    $tag
                );
                $this->logger->debug(json_encode($response));
            } catch (RequestException $e) {
                $deadletter = new Deadletter($this->ownerEmail, $customerEmail, json_encode($tag));
                $this->deadletterRepository->save($deadletter);
                $this->logger->error(json_encode($e->getMessage()));
            } catch (InvalidRequestException $e) {
                $deadletter = new Deadletter($this->ownerEmail, $customerEmail, json_encode($tag));
                $this->deadletterRepository->save($deadletter);
                $this->logger->error(json_encode($e->getMessage()));
            }
        }
    }

    /**
     * @param string $tag
     *
     * @return array
     */
    public function buildTag($tag)
    {
        $tagsArray =
            [
                'tags' => [
                    $tag,
                ],
            ];

        return $tagsArray;
    }

    /**
     * @return SalesManago
     */
    protected function getConnector()
    {
        return $this->connector;
    }
}
