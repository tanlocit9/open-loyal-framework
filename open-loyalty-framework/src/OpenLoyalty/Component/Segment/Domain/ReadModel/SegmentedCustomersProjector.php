<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\ReadModel;

use Psr\Log\LoggerInterface;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\ReadModel\Repository;
use Broadway\Repository\Repository as AggregateRootRepository;
use OpenLoyalty\Component\Customer\Domain\Customer;
use OpenLoyalty\Component\Core\Domain\Model\Identifier;
use OpenLoyalty\Component\Segment\Domain\CustomerId;
use OpenLoyalty\Component\Segment\Domain\Segment;
use OpenLoyalty\Component\Segment\Domain\SegmentId;
use OpenLoyalty\Component\Segment\Domain\SegmentRepository;
use OpenLoyalty\Component\Segment\Domain\SystemEvent\CustomerAddedToSegmentSystemEvent;
use OpenLoyalty\Component\Segment\Domain\SystemEvent\CustomerRemovedFromSegmentSystemEvent;
use OpenLoyalty\Component\Segment\Domain\SystemEvent\SegmentSystemEvents;

/**
 * Class SegmentedCustomersProjector.
 */
class SegmentedCustomersProjector
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var SegmentRepository
     */
    protected $segmentRepository;

    /**
     * @var AggregateRootRepository
     */
    protected $customerRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * SegmentedCustomersProjector constructor.
     *
     * @param Repository              $repository
     * @param SegmentRepository       $segmentRepository
     * @param EventDispatcher         $eventDispatcher
     * @param AggregateRootRepository $customerRepository
     */
    public function __construct(
        Repository $repository,
        SegmentRepository $segmentRepository,
        EventDispatcher $eventDispatcher,
        AggregateRootRepository $customerRepository
    ) {
        $this->repository = $repository;
        $this->segmentRepository = $segmentRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param Segment $segment
     * @param array   $customers
     * @param array   $currentCustomers
     */
    public function storeSegmentation(Segment $segment, array $customers, array $currentCustomers = []): void
    {
        foreach ($customers as $customer) {
            if (!$customer instanceof CustomerId) {
                $customer = new CustomerId($customer);
            }

            $readModel = $this->getReadModel($segment->getSegmentId(), $customer);
            $readModel->setSegmentName($segment->getName());
            $customerDomainModel = $this->customerRepository->load((string) $customer);
            if ($customerDomainModel instanceof Customer) {
                $readModel->setFirstName($customerDomainModel->getFirstName());
                $readModel->setLastName($customerDomainModel->getLastName());
                $readModel->setEmail($customerDomainModel->getEmail());
                $readModel->setPhone($customerDomainModel->getPhone());
            }
            $this->repository->save($readModel);
        }

        $this->dispatchEventsForSegmentation(
            $segment->getSegmentId(),
            $this->getCustomersIdsAsStringFromSegmentation($currentCustomers),
            $this->getCustomersIdsAsString($customers)
        );

        $segment->setCustomersCount(count($customers));
        $this->segmentRepository->save($segment);
    }

    /**
     * Removes all segments.
     */
    public function removeAll(): void
    {
        foreach ($this->repository->findAll() as $segmented) {
            $this->repository->remove($segmented->getId());
        }
    }

    /**
     * @param string $id
     */
    public function removeOneSegment(string $id): void
    {
        $segmentedCustomers = $this->repository->findBy(['segmentId' => $id]);

        foreach ($segmentedCustomers as $segmented) {
            $this->repository->remove($segmented->getId());
        }
    }

    /**
     * @param SegmentId $segmentId
     * @param array     $oldCustomers
     * @param array     $newCustomers
     */
    protected function dispatchEventsForSegmentation(SegmentId $segmentId, array $oldCustomers, array $newCustomers): void
    {
        $dispatcher = $this->eventDispatcher;
        $toRemoveCustomers = array_diff($oldCustomers, $newCustomers);
        foreach ($toRemoveCustomers as $toRemoveCustomer) {
            if ($this->logger) {
                $this->logger->info('[segmentation] customer: '.$toRemoveCustomer.' removed from segment '.(string) $segmentId);
            }
            $dispatcher->dispatch(
                SegmentSystemEvents::CUSTOMER_REMOVED_FROM_SEGMENT,
                [new CustomerRemovedFromSegmentSystemEvent($segmentId, new CustomerId($toRemoveCustomer))]
            );
        }
        $toAddCustomers = array_diff($newCustomers, $oldCustomers);

        foreach ($toAddCustomers as $toAddCustomer) {
            if ($this->logger) {
                $this->logger->info('[segmentation] customer: '.$toAddCustomer.' added to segment '.(string) $segmentId);
            }
            $dispatcher->dispatch(
                SegmentSystemEvents::CUSTOMER_ADDED_TO_SEGMENT,
                [new CustomerAddedToSegmentSystemEvent($segmentId, new CustomerId($toAddCustomer))]
            );
        }
    }

    /**
     * @param array $customers
     *
     * @return array
     */
    protected function getCustomersIdsAsStringFromSegmentation(array $customers): array
    {
        return array_map(function (SegmentedCustomers $segmentedCustomers) {
            return (string) $segmentedCustomers->getCustomerId();
        }, $customers);
    }

    /**
     * @param array $customers
     *
     * @return array
     */
    protected function getCustomersIdsAsString(array $customers): array
    {
        return array_map(function ($customerId) {
            if ($customerId instanceof Identifier) {
                $customerId = (string) $customerId;
            }

            return $customerId;
        }, $customers);
    }

    /**
     * @param SegmentId  $segmentId
     * @param CustomerId $customerId
     *
     * @return SegmentedCustomers
     */
    private function getReadModel(SegmentId $segmentId, CustomerId $customerId): SegmentedCustomers
    {
        $readModel = $this->repository->find((string) $segmentId.'_'.(string) $customerId);

        if (null === $readModel) {
            $readModel = new SegmentedCustomers($segmentId, $customerId);
        }

        return $readModel;
    }
}
