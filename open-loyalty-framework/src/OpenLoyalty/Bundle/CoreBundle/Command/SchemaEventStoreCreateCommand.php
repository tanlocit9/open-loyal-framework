<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CoreBundle\Command;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates the event store schema.
 */
class SchemaEventStoreCreateCommand extends AbstractSchemaEventStoreCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('broadway:event-store:schema:init')
            ->setDescription('Creates the event store schema')
            ->setHelp(
                <<<EOT
The <info>%command.name%</info> command creates the schema in the default
connections database:

<info>php app/console %command.name%</info>
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->connection) {
            $output->writeln('<error>Could not create Broadway event-store schema</error>');
            $output->writeln(sprintf('<error>%s</error>', $this->exception->getMessage()));

            return 1;
        }

        $error = false;
        try {
            $schemaManager = $this->connection->getSchemaManager();
            $schema = $schemaManager->createSchema();
            $eventStore = $this->getEventStore();

            $table = $eventStore->configureSchema($schema);
            if (null !== $table) {
                $schemaManager->createTable($table);
                $output->writeln('<info>Created Broadway event-store schema</info>');
            } else {
                $output->writeln('<info>Broadway event-store schema already exists</info>');
            }
        } catch (Exception $e) {
            $output->writeln('<error>Could not create Broadway event-store schema</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $error = true;
        }

        return $error ? 1 : 0;
    }
}
