<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Command;

use OpenLoyalty\Bundle\ImportBundle\Command\AbstractFileImportCommand;
use OpenLoyalty\Bundle\UserBundle\Import\CustomerXmlImporter;
use OpenLoyalty\Component\Import\Infrastructure\FileImporter;

/**
 * Class CustomerImportCommand.
 */
class CustomerImportCommand extends AbstractFileImportCommand
{
    /**
     * @var CustomerXmlImporter
     */
    private $importer;

    /**
     * CustomerImportCommand constructor.
     *
     * @param CustomerXmlImporter $importer
     */
    public function __construct(CustomerXmlImporter $importer)
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
            ->setName('oloy:customer:import')
            ->setDescription('Import customers from XML file');
    }

    /**
     * {@inheritdoc}
     */
    protected function getImporter(): FileImporter
    {
        return $this->importer;
    }
}
