<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\ReadModel;

use Broadway\ReadModel\SerializableReadModel;
use OpenLoyalty\Component\Core\Domain\ReadModel\Versionable;
use OpenLoyalty\Component\Core\Domain\ReadModel\VersionableReadModel;

/**
 * Class AvailableCampaignsCache.
 */
class AvailableCampaignsCache implements SerializableReadModel, VersionableReadModel
{
    use Versionable;

    /**
     * @var array
     */
    private $visibleTo;

    /**
     * AvailableCampaignsCache constructor.
     *
     * @param array $visibleTo
     */
    public function __construct(array $visibleTo = [])
    {
        $this->visibleTo = $visibleTo;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data): self
    {
        if (isset($data['visibleTo'])) {
            $visibleTo = json_decode($data['visibleTo'], true);
        } else {
            $visibleTo = [];
        }

        return new self(
            $visibleTo
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return [
            'visibleTo' => json_encode($this->visibleTo),
        ];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return 'visible_to';
    }

    /**
     * @return array
     */
    public function getVisibleTo(): array
    {
        return $this->visibleTo;
    }
}
