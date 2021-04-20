<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Tests\Integration\Command;

use OpenLoyalty\Bundle\PointsBundle\Command\PointsTransferImportCommand;
use OpenLoyalty\Bundle\PointsBundle\Import\PointsTransferXmlImporter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class PointsTransferImportCommandTest.
 */
class PointsTransferImportCommandTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_imports_using_command(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        /** @var PointsTransferXmlImporter $importer */
        $importer = $kernel->getContainer()->get('test.'.PointsTransferXmlImporter::class);

        $application = new Application($kernel);
        $application->add(new PointsTransferImportCommand($importer));

        $command = $application->find('oloy:points:import');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file' => __DIR__.'/../../Resources/fixtures/import.xml',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertContains('Import has been finished: Processed 2, Success: 2, Failed: 0', $output);
    }
}
