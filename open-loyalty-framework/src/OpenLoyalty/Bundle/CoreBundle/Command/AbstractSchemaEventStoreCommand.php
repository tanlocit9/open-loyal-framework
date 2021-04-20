<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CoreBundle\Command;

use Assert\Assertion;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use OpenLoyalty\Bundle\CoreBundle\EventStore\ConcurrencyDBALEventStore;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSchemaEventStoreCommand extends DoctrineCommand
{
    /** @var \Doctrine\DBAL\Connection */
    protected $connection;

    /** @var \Exception */
    protected $exception;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                'connection',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Specifies the database connection to use.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $databaseConnectionName = $input->getOption('connection') ?: $this->getContainer()->getParameter('doctrine.default_connection');
        Assertion::string($databaseConnectionName, 'Input option "connection" must be of type `string`.');

        try {
            $this->connection = $this->getDoctrineConnection($databaseConnectionName);
        } catch (\Exception $exception) {
            $this->exception = $exception;
        }
    }

    /**
     * @return ConcurrencyDBALEventStore
     *
     * @throws \RuntimeException
     */
    protected function getEventStore()
    {
        $eventStore = $this->getContainer()->get('broadway.event_store');

        if (!$eventStore instanceof ConcurrencyDBALEventStore) {
            throw new \RuntimeException("'broadway.event_store' must be configured as an instance of DBALEventStore");
        }

        return $eventStore;
    }
}
