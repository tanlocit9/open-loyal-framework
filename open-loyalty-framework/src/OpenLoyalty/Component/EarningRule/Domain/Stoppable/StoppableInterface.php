<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Stoppable;

/**
 * Interface StoppableInterface.
 */
interface StoppableInterface
{
    /**
     * @return bool
     */
    public function isLastExecutedRule(): bool;
}
