<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\Transaction\Tests\Unit\Command;

use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Transaction\Domain\Command\EditTransactionLabels;
use OpenLoyalty\Component\Transaction\Domain\Event\LabelsWereUpdated;
use OpenLoyalty\Component\Transaction\Domain\Event\TransactionWasRegistered;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class EditTransactionLabelsTest.
 */
class EditTransactionLabelsTest extends TransactionCommandHandlerTest
{
    /**
     * @test
     */
    public function it_edits_labels()
    {
        $transactionId = new TransactionId('00000000-0000-0000-0000-000000000000');

        $labels = [['key' => 'added label', 'value' => 'with some value']];
        $editedLabels = [
            new Label('edited label', 'with some value'),
        ];

        $this->scenario
            ->withAggregateId((string) $transactionId)
            ->given([
                new TransactionWasRegistered(
                    $transactionId,
                    $this->getTransactionData(),
                    $this->getCustomerData(),
                    [],
                    null,
                    null,
                    null,
                    null,
                    null,
                    $labels
                ),
            ])
            ->when(new EditTransactionLabels($transactionId, [['key' => 'edited label', 'value' => 'with some value']]))
            ->then(array(
                new LabelsWereUpdated(
                    $transactionId,
                    $editedLabels
                ),
            ));
    }

    /**
     * @return array
     */
    protected function getTransactionData()
    {
        return [
            'documentNumber' => '123',
            'purchasePlace' => 'wroclaw',
            'purchaseDate' => '1471859115',
            'documentType' => 'sell',
        ];
    }

    /**
     * @return array
     */
    protected function getCustomerData()
    {
        return [
            'name' => 'Jan Nowak',
            'email' => 'ol@oy.com',
            'nip' => 'aaa',
            'phone' => '123',
            'loyaltyCardNumber' => '222',
            'address' => [
                'street' => 'Bagno',
                'address1' => '12',
                'city' => 'Warszawa',
                'country' => 'PL',
                'province' => 'Mazowieckie',
                'postal' => '00-800',
            ],
        ];
    }
}
