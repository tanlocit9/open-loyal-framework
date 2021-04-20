<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Command;

use OpenLoyalty\Bundle\ImportBundle\Command\AbstractFileImportCommand;
use OpenLoyalty\Bundle\TransactionBundle\Import\TransactionXmlImporter;
use OpenLoyalty\Component\Import\Infrastructure\FileImporter;

/**
 * Class TransactionImportCommand.
 */
class TransactionImportCommand extends AbstractFileImportCommand
{
    /**
     * @var TransactionXmlImporter
     */
    private $importer;

    /**
     * TransactionImportCommand constructor.
     *
     * @param TransactionXmlImporter $importer
     */
    public function __construct(TransactionXmlImporter $importer)
    {
        $this->importer = $importer;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('oloy:transaction:import')
            ->setDescription('Import transaction from XML file');
    }

    /**
     * {@inheritdoc}
     */
    protected function getImporter(): FileImporter
    {
        return $this->importer;
    }
}
