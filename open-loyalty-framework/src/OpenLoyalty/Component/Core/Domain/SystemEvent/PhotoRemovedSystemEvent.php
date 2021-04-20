<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Domain\SystemEvent;

/**
 * Class PhotoRemovedSystemEvent.
 */
class PhotoRemovedSystemEvent
{
    /**
     * @var string
     */
    private $name;

    /**
     * PhotoRemovedSystemEvent constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
