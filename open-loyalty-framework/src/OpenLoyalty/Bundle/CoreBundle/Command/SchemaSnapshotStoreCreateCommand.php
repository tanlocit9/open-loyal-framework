<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\CoreBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use OpenLoyalty\Bundle\CoreBundle\Repository\DBALSnapshotRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SchemaSnapshotStoreCreateCommand.
 */
class SchemaSnapshotStoreCreateCommand extends DoctrineCommand
{
    /**
     * @var DBALSnapshotRepository
     */
    private $snapshotRepository;

    /**
     * {@inheritdoc}
     *
     * @param DBALSnapshotRepository $snapshotRepository
     */
    public function __construct(DBALSnapshotRepository $snapshotRepository, ?string $name = null)
    {
        parent::__construct($name);

        $this->snapshotRepository = $snapshotRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('broadway:snapshoting:schema:init')
            ->setDescription('Creates snapshoting store');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (!$this->snapshotRepository->createSchema()) {
            $output->writeln('Broadway snapshoting store already exists');

            return;
        }

        $output->writeln('Broadway snapshoting store has been created');
    }
}
