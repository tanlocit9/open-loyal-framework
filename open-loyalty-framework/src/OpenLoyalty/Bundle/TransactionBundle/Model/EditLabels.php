<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EditLabels.
 */
class EditLabels
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $transactionId;

    /**
     * @var array
     */
    protected $labels;

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId(string $transactionId = null)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param array $labels
     */
    public function setLabels(array $labels)
    {
        $this->labels = $labels;
    }
}
