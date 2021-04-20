<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Seller\Domain;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasActivated;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasDeactivated;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasDeleted;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasRegistered;
use OpenLoyalty\Component\Seller\Domain\Event\SellerWasUpdated;

/**
 * Class Seller.
 */
class Seller extends EventSourcedAggregateRoot
{
    /**
     * @var SellerId
     */
    protected $id;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @var bool
     */
    protected $deleted = false;

    /**
     * @return string
     */
    public function getAggregateRootId(): string
    {
        return $this->id;
    }

    /**
     * @return SellerId
     */
    public function getId(): SellerId
    {
        return $this->id;
    }

    /**
     * @param SellerId $sellerId
     * @param array    $sellerData
     *
     * @return Seller
     */
    public static function registerSeller(SellerId $sellerId, array $sellerData): Seller
    {
        $seller = new self();
        $seller->register($sellerId, $sellerData);

        return $seller;
    }

    /**
     * @param array $sellerData
     */
    public function update(array $sellerData): void
    {
        $this->apply(
            new SellerWasUpdated($this->getId(), $sellerData)
        );
    }

    /**
     * @param SellerWasUpdated $event
     */
    protected function applySellerWasUpdated(SellerWasUpdated $event): void
    {
        $this->data = $event->getSellerData();
    }

    /**
     * Activate.
     */
    public function activate(): void
    {
        $this->apply(
            new SellerWasActivated($this->getId())
        );
    }

    /**
     * @param SellerWasActivated $event
     */
    protected function applySellerWasActivated(SellerWasActivated $event): void
    {
        $this->setActive(true);
    }

    /**
     * @param bool $active
     */
    private function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * Deactivate.
     */
    public function deactivate(): void
    {
        $this->apply(
            new SellerWasDeactivated($this->getId())
        );
    }

    /**
     * @param SellerWasDeactivated $event
     */
    protected function applySellerWasDeactivated(SellerWasDeactivated $event): void
    {
        $this->setActive(false);
    }

    /**
     * Delete.
     */
    public function delete(): void
    {
        $this->apply(
            new SellerWasDeleted($this->getId())
        );
    }

    /**
     * @param SellerWasDeleted $event
     */
    protected function applySellerWasDeleted(SellerWasDeleted $event): void
    {
        $this->setDeleted(true);
    }

    /**
     * @param bool $deleted
     */
    private function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    /**
     * @param SellerId $sellerId
     * @param array    $sellerData
     */
    private function register(SellerId $sellerId, array $sellerData): void
    {
        $this->apply(
            new SellerWasRegistered($sellerId, $sellerData)
        );
    }

    /**
     * @param SellerWasRegistered $event
     */
    protected function applySellerWasRegistered(SellerWasRegistered $event): void
    {
        $this->id = $event->getSellerId();
        $this->data = $event->getSellerData();
    }
}
