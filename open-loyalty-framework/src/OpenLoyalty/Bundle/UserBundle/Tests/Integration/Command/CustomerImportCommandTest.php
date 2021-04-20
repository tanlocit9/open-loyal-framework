<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Integration\Command;

use OpenLoyalty\Bundle\UserBundle\Command\CustomerImportCommand;
use OpenLoyalty\Bundle\UserBundle\Import\CustomerXmlImporter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CustomerImportCommandTest.
 */
class CustomerImportCommandTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_imports_using_command(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        /** @var CustomerXmlImporter $importer */
        $importer = $kernel->getContainer()->get(CustomerXmlImporter::class);

        $application = new Application($kernel);
        $application->add(new CustomerImportCommand($importer));

        $command = $application->find('oloy:customer:import');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file' => __DIR__.'/../../Resources/fixtures/import2.xml',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertContains('Import has been finished: Processed 1, Success: 1, Failed: 0', $output);
    }
}
