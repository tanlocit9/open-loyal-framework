<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Import;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Component\Account\Domain\Command\AddPoints;
use OpenLoyalty\Component\Account\Domain\Command\SpendPoints;
use OpenLoyalty\Component\Import\Infrastructure\ImporterProcessor;
use OpenLoyalty\Component\Import\Infrastructure\ProcessImportResult;

/**
 * Class PointsTransferImportProcessor.
 */
class PointsTransferImportProcessor implements ImporterProcessor
{
    /** @var CommandBus */
    protected $commandBus;

    /**
     * PointsTransferImportProcessor constructor.
     *
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * {@inheritdoc}
     */
    public function processItem($entity): ProcessImportResult
    {
        if (!$entity instanceof AddPoints && !$entity instanceof SpendPoints) {
            throw new \InvalidArgumentException('Entity object is not AddPoints|SpendPoints');
        }

        $this->commandBus->dispatch($entity);

        return new ProcessImportResult($entity->getPointsTransfer()->getId());
    }
}
