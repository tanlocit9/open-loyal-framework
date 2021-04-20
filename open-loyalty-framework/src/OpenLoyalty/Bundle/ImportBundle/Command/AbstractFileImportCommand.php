<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ImportBundle\Command;

use OpenLoyalty\Component\Import\Infrastructure\FileImporter;
use OpenLoyalty\Component\Import\Infrastructure\ImportResult;
use OpenLoyalty\Component\Import\Infrastructure\ImportResultItem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class AbstractFileImportCommand.
 */
abstract class AbstractFileImportCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
        $this->addArgument('file', InputArgument::REQUIRED);
    }

    /**
     * @return FileImporter
     */
    abstract protected function getImporter(): FileImporter;

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $importer = $this->getImporter();
        $output->writeln('<info>Import process...</info>');

        /** @var ImportResult $result */
        $result = $importer->import($input->getArgument('file'));
        $this->showSummary($output, $result);

        $output->writeln(sprintf(
            '<info>Import has been finished: Processed %s, Success: %s, Failed: %s</info>',
            $result->getTotalProcessed(),
            $result->getTotalSuccess(),
            $result->getTotalFailed()
        ));
    }

    /**
     * @param OutputInterface $output
     * @param ImportResult    $result
     */
    protected function showSummary(OutputInterface $output, ImportResult $result)
    {
        foreach ($result->getItems() as $resultItem) {
            switch ($resultItem->getStatus()) {
                case ImportResultItem::SUCCESS:
                    $output->writeln(sprintf(
                        '<info>Success (%s => %s)</info>',
                        $resultItem->getIdentifier() ?? '',
                        $resultItem->getProcessImportResult()
                    ));
                    break;
                case ImportResultItem::ERROR:
                    $output->writeln(sprintf(
                        '<error>Error (%s): %s</error>',
                        $resultItem->getIdentifier() ?? '',
                        $resultItem->getMessage()
                    ));
                    $output->writeln(sprintf(
                        '<error>%s</error>',
                            $resultItem->getException()->getTraceAsString()
                    ));
                    break;
            }
        }
    }
}
