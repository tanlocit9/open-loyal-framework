<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Command;

use OpenLoyalty\Bundle\PointsBundle\Service\PointsTransfersManager;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetails;
use OpenLoyalty\Component\Core\Domain\Exception\Translatable;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UnlockTransfersCommand.
 */
class UnlockTransfersCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ol:points:transfers:unlock');
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get(PointsTransfersManager::class);
        $totalTransfers = $manager->countTransfersToUnlock();
        if (0 == $totalTransfers) {
            $message = $this->getContainer()->get('translator')
                ->trans('account.points_transfer.no_transfers_to_unlock');
            $output->writeln($message);

            return;
        }
        $bar = new ProgressBar($output, $totalTransfers);
        $step = 0;
        $bar->start();
        try {
            $manager->unlockTransfers(function (PointsTransferDetails $transfer) use ($bar, &$step) {
                ++$step;
                $bar->setProgress($step);
            });
        } catch (Translatable $exception) {
            $message = $this->getContainer()->get('translator')
                ->trans($exception->getMessageKey(), $exception->getMessageParams());

            throw new \Exception($message, $exception);
        }
        $bar->finish();
    }
}
