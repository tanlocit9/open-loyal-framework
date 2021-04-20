<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Import;

use Broadway\CommandHandling\CommandBus;
use OpenLoyalty\Component\Import\Infrastructure\ImporterProcessor;
use OpenLoyalty\Component\Import\Infrastructure\ProcessImportResult;
use OpenLoyalty\Component\Transaction\Domain\Command\RegisterTransaction;

/**
 * Class TransactionImportProcessor.
 */
class TransactionImportProcessor implements ImporterProcessor
{
    /** @var CommandBus */
    protected $commandBus;

    /**
     * TransactionImportProcessor constructor.
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
        if (!$entity instanceof RegisterTransaction) {
            throw new \InvalidArgumentException('Entity object is not RegisterTransaction');
        }

        $this->commandBus->dispatch($entity);

        return new ProcessImportResult($entity->getTransactionId());
    }
}
