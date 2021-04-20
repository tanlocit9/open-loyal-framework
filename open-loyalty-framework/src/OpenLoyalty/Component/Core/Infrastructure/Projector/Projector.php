<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Core\Infrastructure\Projector;

use Broadway\ReadModel\Projector as BroadwayProjector;
use Broadway\Domain\DomainMessage;
use Elasticsearch\Common\Exceptions\Conflict409Exception;

/**
 * Class Projector.
 */
abstract class Projector extends BroadwayProjector
{
    private const MAX_RETRIES = 10;

    /**
     * {@inheritdoc}
     *
     * @throws NotSynchronizedProjectionException
     */
    public function handle(DomainMessage $domainMessage)
    {
        $retries = 1;
        do {
            try {
                parent::handle($domainMessage);

                return;
            } catch (Conflict409Exception $e) {
                ++$retries;
            }
        } while ($retries <= self::MAX_RETRIES);

        throw new NotSynchronizedProjectionException('', 0, $e);
    }
}
