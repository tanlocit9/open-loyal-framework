<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Command;

use OpenLoyalty\Bundle\PointsBundle\Service\PointsTransfersManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExpireTransfersCommand.
 */
class ExpireTransfersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('ol:points:transfers:expire');
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get(PointsTransfersManager::class);
        $transfers = $manager->expireTransfers();

        $output->writeln(count($transfers).' expired');
    }
}
