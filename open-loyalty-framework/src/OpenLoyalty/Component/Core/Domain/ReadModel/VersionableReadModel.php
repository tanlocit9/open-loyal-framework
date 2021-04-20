<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Domain\ReadModel;

/**
 * Interface VersionableReadModel.
 */
interface VersionableReadModel
{
    /**
     * @return int|null
     */
    public function getVersion(): ?int;

    /**
     * @param int $version
     */
    public function setVersion(int $version): void;
}
