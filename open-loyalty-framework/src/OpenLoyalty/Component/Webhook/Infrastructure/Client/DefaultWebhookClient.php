<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Webhook\Infrastructure\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use OpenLoyalty\Component\Webhook\Infrastructure\Client\Request\Header\RequestHeaderInterface;
use Psr\Log\LoggerInterface;

/**
 * Class GuzzleWebhookClient.
 */
final class DefaultWebhookClient implements WebhookClientInterface
{
    /**
     * Timeout for guzzle request.
     */
    const TIMEOUT_SEC = 1;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestHeaderInterface
     */
    private $requestHeader;

    /**
     * GuzzleWebhookClient constructor.
     *
     * @param LoggerInterface        $logger
     * @param RequestHeaderInterface $requestHeader
     */
    public function __construct(LoggerInterface $logger, RequestHeaderInterface $requestHeader)
    {
        $this->logger = $logger;
        $this->requestHeader = $requestHeader;
    }

    /**
     * {@inheritdoc}
     */
    public function postAction(string $uri, array $data, array $config = []): void
    {
        $client = new Client(['headers' => $this->requestHeader->headers()]);

        try {
            // Perform simulating async request with low timeout.
            // Response does not matter for us.
            $client->post(
                $uri,
                [
                    'json' => $data,
                    'timeout' => self::TIMEOUT_SEC,
                ]
            );
        } catch (ConnectException $exception) {
            $this->logger->debug(sprintf('[Webhooks] Request timeout: %s', $exception->getMessage()));
        } catch (RequestException $exception) {
            $this->logger->warning(sprintf('[Webhooks] Request problem: %s', $exception->getMessage()));
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('[Webhooks] Error request: %s', $exception->getMessage()));
        }
    }
}
