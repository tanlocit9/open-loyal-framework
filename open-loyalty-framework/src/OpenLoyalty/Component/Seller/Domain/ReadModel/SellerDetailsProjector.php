<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Seller\Domain\ReadModel;

use OpenLoyalty\Component\Core\Infrastructure\Projector\Projector;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasActivated;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasDeactivated;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasDeleted;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasRegistered;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasUpdated;
use OpenLoyalty\Component\Seller\Domain\PosId;
use OpenLoyalty\Component\Seller\Domain\SellerId;

/**
 * Class SellerDetailsProjector.
 */
class SellerDetailsProjector extends Projector
{
    private $repository;

    /**
     * @var PosRepository
     */
    protected $posRepository;

    /**
     * SellerDetailsProjector constructor.
     *
     * @param               $repository
     * @param PosRepository $posRepository
     */
    public function __construct($repository, PosRepository $posRepository)
    {
        $this->repository = $repository;
        $this->posRepository = $posRepository;
    }

    /**
     * @param SellerWasRegistered $event
     */
    protected function applySellerWasRegistered(SellerWasRegistered $event): void
    {
        $data = $event->getSellerData();
        if (isset($data['posId']) && !$data['posId'] instanceof PosId) {
            $data['posId'] = new PosId($data['posId']);
        }
        $readModel = $this->getReadModel($event->getSellerId());
        $readModel->setFirstName($data['firstName']);
        $readModel->setLastName($data['lastName']);
        $readModel->setEmail($data['email']);
        $readModel->setPhone($data['phone']);

        if (isset($data['allowPointTransfer'])) {
            $readModel->setAllowPointTransfer($data['allowPointTransfer']);
        }

        if (isset($data['posId'])) {
            $readModel->setPosId($data['posId']);
        }

        if ($readModel->getPosId()) {
            /** @var Pos $pos */
            $pos = $this->posRepository->byId(
                new \OpenLoyalty\Component\Pos\Domain\PosId($readModel->getPosId()->__toString())
            );
            if ($pos) {
                $readModel->setPosName($pos->getName());
                $readModel->setPosCity($pos->getLocation() ? $pos->getLocation()->getCity() : null);
            }
        }

        if (isset($data['active'])) {
            $readModel->setActive($data['active']);
        }
        if (isset($data['deleted'])) {
            $readModel->setDeleted($data['deleted']);
        }
        $this->repository->save($readModel);
    }

    /**
     * @param SellerWasDeleted $event
     */
    protected function applySellerWasDeleted(SellerWasDeleted $event): void
    {
        $readModel = $this->getReadModel($event->getSellerId());
        $readModel->setDeleted(true);
        $this->repository->save($readModel);
    }

    /**
     * @param SellerWasActivated $event
     */
    protected function applySellerWasActivated(SellerWasActivated $event): void
    {
        $readModel = $this->getReadModel($event->getSellerId());
        $readModel->setActive(true);
        $this->repository->save($readModel);
    }

    /**
     * @param SellerWasDeactivated $event
     */
    protected function applySellerWasDeactivated(SellerWasDeactivated $event): void
    {
        $readModel = $this->getReadModel($event->getSellerId());
        $readModel->setActive(false);
        $this->repository->save($readModel);
    }

    /**
     * @param SellerWasUpdated $event
     */
    protected function applySellerWasUpdated(SellerWasUpdated $event): void
    {
        $readModel = $this->getReadModel($event->getSellerId());
        $data = $event->getSellerData();

        if (isset($data['firstName'])) {
            $readModel->setFirstName($data['firstName']);
        }

        if (isset($data['lastName'])) {
            $readModel->setLastName($data['lastName']);
        }

        if (isset($data['email'])) {
            $readModel->setEmail($data['email']);
        }

        if (isset($data['phone'])) {
            $readModel->setPhone($data['phone']);
        }

        if (isset($data['allowPointTransfer'])) {
            $readModel->setAllowPointTransfer($data['allowPointTransfer']);
        }

        if (isset($data['posId'])) {
            if (!$data['posId'] instanceof PosId) {
                $data['posId'] = new PosId($data['posId']);
            }
            $readModel->setPosId($data['posId']);
            if ($readModel->getPosId()) {
                /** @var Pos $pos */
                $pos = $this->posRepository->byId(
                    new \OpenLoyalty\Component\Pos\Domain\PosId($readModel->getPosId()->__toString())
                );
                if ($pos) {
                    $readModel->setPosName($pos->getName());
                    $readModel->setPosCity($pos->getLocation() ? $pos->getLocation()->getCity() : null);
                }
            }
        }

        $this->repository->save($readModel);
    }

    /**
     * @param SellerId $sellerId
     *
     * @return SellerDetails
     */
    private function getReadModel(SellerId $sellerId): SellerDetails
    {
        $readModel = $this->repository->find($sellerId->__toString());

        if (null === $readModel) {
            $readModel = new SellerDetails($sellerId);
        }

        return $readModel;
    }
}
