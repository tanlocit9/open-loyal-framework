<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Segment\Domain\Model;

/**
 * Interface CriterionInterface.
 */
interface CriterionInterface
{
    /**
     * @return array
     */
    public function getDataAsArray(): array;

    /**
     * @return string
     */
    public function getType(): string;
}
