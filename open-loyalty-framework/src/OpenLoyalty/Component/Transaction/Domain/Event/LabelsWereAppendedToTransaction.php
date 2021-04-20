<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain\Event;

use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;

/**
 * Class LabelsWereAppendedToTransaction.
 */
class LabelsWereAppendedToTransaction extends TransactionEvent
{
    /**
     * @var array
     */
    protected $labels;

    public function __construct(TransactionId $transactionId, array $labels = [])
    {
        parent::__construct($transactionId);
        $transactionLabels = [];
        foreach ($labels as $label) {
            if ($label instanceof Label) {
                $transactionLabels[] = $label;
            } else {
                $transactionLabels[] = Label::deserialize($label);
            }
        }
        $this->labels = $transactionLabels;
    }

    public function serialize(): array
    {
        $labels = [];
        foreach ($this->labels as $label) {
            $labels[] = $label->serialize();
        }

        return array_merge(parent::serialize(), [
            'labels' => $labels,
        ]);
    }

    /**
     * @param array $data
     *
     * @return LabelsWereAppendedToTransaction
     */
    public static function deserialize(array $data)
    {
        $labels = [];
        foreach ($data['labels'] as $label) {
            $labels[] = Label::deserialize($label);
        }

        return new self(new TransactionId($data['transactionId']), $labels);
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }
}
