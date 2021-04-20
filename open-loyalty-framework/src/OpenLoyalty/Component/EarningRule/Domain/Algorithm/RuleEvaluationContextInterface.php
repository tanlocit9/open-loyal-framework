<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * Created by PhpStorm.
 * User: tjurczak
 * Date: 14.02.17
 * Time: 12:52.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Algorithm;

use OpenLoyalty\Component\Transaction\Domain\Transaction;

/**
 * Interface RuleEvaluationContextInterface.
 */
interface RuleEvaluationContextInterface
{
    /**
     * @return Transaction
     */
    public function getTransaction(): Transaction;

    /**
     * @param string $sku
     *
     * @return int
     */
    public function getProductPoints($sku);

    /**
     * @param string $sku
     * @param float  $points
     */
    public function addProductPoints($sku, $points);

    /**
     * @param string $sku
     * @param int    $points
     */
    public function setProductPoints($sku, $points);

    /**
     * @return array
     */
    public function getProducts();

    /**
     * @return string
     */
    public function getCustomerId();
}
