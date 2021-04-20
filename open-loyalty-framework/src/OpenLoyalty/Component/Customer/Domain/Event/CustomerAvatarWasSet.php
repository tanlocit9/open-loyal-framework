<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Domain\Event;

use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class CustomerAvatarWasSet.
 */
class CustomerAvatarWasSet extends CustomerEvent
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $originalName;

    /**
     * @var string
     */
    private $mime;

    /**
     * SetAvatar constructor.
     *
     * @param CustomerId $customerId
     * @param string     $path
     * @param string     $originalName
     * @param string     $mime
     */
    public function __construct(CustomerId $customerId, string $path, string $originalName, string $mime)
    {
        parent::__construct($customerId);
        $this->path = $path;
        $this->originalName = $originalName;
        $this->mime = $mime;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return array_merge(parent::serialize(), [
            'path' => $this->path,
            'originalName' => $this->originalName,
            'mime' => $this->mime,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data): CustomerAvatarWasSet
    {
        $event = new self(
            new CustomerId($data['customerId']),
            $data['path'],
            $data['originalName'],
            $data['mime']
        );

        return $event;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    /**
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }
}
