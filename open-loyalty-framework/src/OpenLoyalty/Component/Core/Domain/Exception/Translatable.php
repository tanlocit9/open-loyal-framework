<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Domain\Exception;

interface Translatable extends \Throwable
{
    /**
     * @return string
     */
    public function getMessageKey(): string;

    /**
     * @return array
     */
    public function getMessageParams(): array;
}
