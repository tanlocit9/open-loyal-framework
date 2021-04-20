<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AppendLabels.
 */
class AppendLabels
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $transactionDocumentNumber;

    /**
     * @var array
     * @Assert\NotBlank()
     */
    protected $labels;

    /**
     * @return string
     */
    public function getTransactionDocumentNumber()
    {
        return $this->transactionDocumentNumber;
    }

    /**
     * @param string $transactionDocumentNumber
     */
    public function setTransactionDocumentNumber($transactionDocumentNumber)
    {
        $this->transactionDocumentNumber = $transactionDocumentNumber;
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
