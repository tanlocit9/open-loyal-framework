<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Event\Listener;

use Broadway\Repository\Repository;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetails;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use OpenLoyalty\Component\Transaction\Domain\Model\Item;
use OpenLoyalty\Component\Transaction\Domain\Transaction;

/**
 * Class PointsTransferSerializationListener.
 */
class PointsTransferSerializationListener implements EventSubscriberInterface
{
    /**
     * @var PosRepository
     */
    protected $posRepository;

    /**
     * @var Repository
     */
    protected $transactionRepository;

    /**
     * PointsTransferSerializationListener constructor.
     *
     * @param PosRepository $posRepository
     * @param Repository    $transactionRepository
     */
    public function __construct(
        PosRepository $posRepository,
        Repository $transactionRepository
    ) {
        $this->posRepository = $posRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.post_serialize', 'method' => 'onPostSerialize'),
        );
    }

    /**
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        /** @var PointsTransferDetails $transfer */
        $transfer = $event->getObject();

        if ($transfer instanceof PointsTransferDetails) {
            if ($transfer->getPosIdentifier()) {
                $pos = $this->posRepository->oneByIdentifier($transfer->getPosIdentifier());
                if ($pos instanceof Pos) {
                    $event->getVisitor()->addData('posName', $pos->getName());
                }
            }

            if ($transfer->getTransactionId()) {
                $transaction = $this->transactionRepository->load((string) $transfer->getTransactionId());
                if ($transaction instanceof Transaction) {
                    $event->getVisitor()->setData('transactionDocumentNumber', $transaction->getDocumentNumber());
                    $event->getVisitor()->setData('transaction', [
                        'grossValue' => $transaction->getGrossValue(),
                        'items' => array_map(function (Item $item) {
                            return $item->serialize();
                        }, $transaction->getItems()),
                    ]);
                }
            }

            if ($transfer->getRevisedTransactionId()) {
                $transaction = $this->transactionRepository->load((string) $transfer->getRevisedTransactionId());
                if ($transaction instanceof Transaction) {
                    $event->getVisitor()->setData('revisedTransactionDocumentNumber', $transaction->getDocumentNumber());
                }
            }
        }
    }
}
