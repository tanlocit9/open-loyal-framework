<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\MarkDownBundle\Service;

/**
 * Interface ContextFormatter.
 */
interface ContextFormatter
{
    /**
     * @param null|string     $value
     * @param ContextProvider $context
     *
     * @return null|string
     */
    public function format(?string $value, ContextProvider $context): ?string;
}
