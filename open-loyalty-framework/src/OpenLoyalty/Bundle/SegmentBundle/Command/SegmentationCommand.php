<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Command;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Segment\Domain\ReadModel\SegmentedCustomers;
use OpenLoyalty\Component\Segment\Domain\Segment;
use OpenLoyalty\Component\Segment\Domain\SegmentId;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class SegmentationCommand.
 */
class SegmentationCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function run(InputInterface $input, OutputInterface $output)
    {
        /** @var LoggerInterface $logger */
        $logger = $this->getContainer()->get('logger');
        $segmentsRepository = $this->getContainer()->get('oloy.segment.repository');

        /** @var Repository $segmentedCustomersRepository */
        $segmentedCustomersRepository = $this->getContainer()->get('oloy.segment.read_model.repository.segmented_customers');
        $segmentationProvider = $this->getContainer()->get('oloy.segment.segmentation_provider');
        $segmentedCustomersProjector = $this->getContainer()->get('oloy.segment.read_model.projector.segmented_customers');
        $segmentToRecreateName = $input->getOption('segment');
        $segmentToRecreateId = $input->getOption('segmentId');

        if (!$segmentToRecreateName && !$segmentToRecreateId) {
            $this->recreateAllSegments($segmentedCustomersRepository, $segmentedCustomersProjector, $segmentsRepository, $segmentationProvider, $logger);
        } else {
            $this->recreateNamedSegment($segmentedCustomersRepository, $segmentedCustomersProjector, $segmentsRepository, $segmentationProvider, $logger, $segmentToRecreateName, $segmentToRecreateId);
        }
    }

    /**
     * @param Repository $segmentedCustomersRepository
     * @param $segmentedCustomersProjector
     * @param $segmentsRepository
     * @param $segmentationProvider
     * @param $logger
     */
    public function recreateAllSegments($segmentedCustomersRepository, $segmentedCustomersProjector, $segmentsRepository, $segmentationProvider, $logger)
    {
        $allCurrentCustomers = $segmentedCustomersRepository->findAll();
        $segments = $segmentsRepository->findAllActive();
        $oldCustomers = [];
        /** @var SegmentedCustomers $segmented */
        foreach ($allCurrentCustomers as $segmented) {
            if (!isset($oldCustomers[$segmented->getSegmentId()->__toString()])) {
                $oldCustomers[$segmented->getSegmentId()->__toString()] = [];
            }
            $oldCustomers[$segmented->getSegmentId()->__toString()][] = $segmented;
        }
        $segmentedCustomersProjector->removeAll();

        /** @var Segment $segment */
        foreach ($segments as $segment) {
            $logger->info('[segmentation] segmenting: '.$segment->getName(), [
                'segmentId' => $segment->getSegmentId()->__toString(),
            ]);
            $customers = $segmentationProvider->evaluateSegment($segment);
            $currentCustomers = isset($oldCustomers[$segment->getSegmentId()->__toString()]) ? $oldCustomers[$segment->getSegmentId()->__toString()] : [];

            $segmentedCustomersProjector->storeSegmentation($segment, $customers, $currentCustomers);

            $logger->info('[segmentation] '.count($customers).' customers added to segment', [
                'segmentId' => $segment->getSegmentId()->__toString(),
            ]);
        }
    }

    /**
     * @param Repository $segmentedCustomersRepository
     * @param $segmentedCustomersProjector
     * @param $segmentsRepository
     * @param $segmentationProvider
     * @param $logger
     * @param $segmentName
     * @param string|null $segmentId
     */
    public function recreateNamedSegment($segmentedCustomersRepository, $segmentedCustomersProjector, $segmentsRepository, $segmentationProvider, $logger, $segmentName = null, $segmentId = null)
    {
        if ($segmentId) {
            /** @var Segment $segment */
            $segment = $segmentsRepository->find(new SegmentId($segmentId));
        } else {
            $segment = $segmentsRepository->findOneBy(['name' => $segmentName]);
        }
        if (!$segment) {
            $logger->info('[segmentation] segmentation failed. Segment '.$segmentName.' does not exist');
            die();
        }
        $segmentId = $segment->getSegmentId();
        $allSegmentCustomers = $segmentedCustomersRepository->findByParameters(['segmentId' => $segmentId->__toString()]);

        $segmentedCustomersProjector->removeOneSegment($segmentId->__toString());
        $logger->info('[segmentation] segmenting: '.$segment->getName(), [
            'segmentId' => $segment->getSegmentId()->__toString(),
        ]);

        $customers = $segmentationProvider->evaluateSegment($segment);
        $currentCustomers = isset($allSegmentCustomers) ? $allSegmentCustomers : [];
        $segmentedCustomersProjector->storeSegmentation($segment, $customers, $currentCustomers);

        $logger->info('[segmentation] '.count($customers).' customers added to segment', [
            'segmentId' => $segment->getSegmentId()->__toString(),
        ]);
    }

    protected function configure()
    {
        $this->setName('oloy:segment:recreate');
        $this->addOption('segment', 's', InputOption::VALUE_OPTIONAL);
        $this->addOption('segmentId', 'sid', InputOption::VALUE_OPTIONAL);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
