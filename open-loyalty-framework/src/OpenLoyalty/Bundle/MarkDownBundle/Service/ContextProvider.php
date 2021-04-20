<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\MarkDownBundle\Service;

/**
 * Interface ContextProvider.
 */
interface ContextProvider
{
    /**
     * @return null|string
     */
    public function getOutputFormat(): ?string;
}
