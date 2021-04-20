<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Tests\Integration\Command;

use OpenLoyalty\Bundle\TransactionBundle\Command\TransactionImportCommand;
use OpenLoyalty\Bundle\TransactionBundle\Import\TransactionXmlImporter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class TransactionImportCommandTest.
 */
class TransactionImportCommandTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_imports_using_command(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        /** @var TransactionXmlImporter $importer */
        $importer = $kernel->getContainer()->get(TransactionXmlImporter::class);

        $application = new Application($kernel);
        $application->add(new TransactionImportCommand($importer));

        $command = $application->find('oloy:transaction:import');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file' => __DIR__.'/../../Resources/fixtures/import-2.xml',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertContains('Import has been finished: Processed 2, Success: 2, Failed: 0', $output);
    }
}
