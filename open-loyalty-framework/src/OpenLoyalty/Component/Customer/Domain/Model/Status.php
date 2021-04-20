<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\Model;

use Broadway\Serializer\Serializable;

/**
 * Class Status.
 */
class Status implements Serializable
{
    const TYPE_NEW = 'new';
    const TYPE_ACTIVE = 'active';
    const TYPE_BLOCKED = 'blocked';
    const TYPE_DELETED = 'deleted';

    const STATE_NO_CARD = 'no-card';
    const STATE_CARD_SENT = 'card-sent';
    const STATE_WITH_CARD = 'with-card';

    /**
     * @var array
     */
    protected static $availableTypes = [
        self::TYPE_NEW,
        self::TYPE_ACTIVE,
        self::TYPE_BLOCKED,
        self::TYPE_DELETED,
    ];

    /**
     * @var array
     */
    protected static $availableStates = [
        self::STATE_NO_CARD,
        self::STATE_CARD_SENT,
        self::STATE_WITH_CARD,
    ];

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $state;

    /**
     * Status constructor.
     *
     * @param null|string $type
     * @param null|string $state
     */
    public function __construct($type = null, $state = null)
    {
        $this->setType($type);
        $this->setState($state);
    }

    /**
     * @return Status
     */
    public static function typeNew()
    {
        return new static(self::TYPE_NEW);
    }

    /**
     * @return Status
     */
    public static function typeActiveNoCard()
    {
        return new static(self::TYPE_ACTIVE, self::STATE_NO_CARD);
    }

    /**
     * @return Status
     */
    public static function typeActiveCardSent()
    {
        return new static(self::TYPE_ACTIVE, self::STATE_CARD_SENT);
    }

    /**
     * @return Status
     */
    public static function typeActiveWithCard()
    {
        return new static(self::TYPE_ACTIVE, self::STATE_WITH_CARD);
    }

    /**
     * @return Status
     */
    public static function typeBlocked()
    {
        return new static(self::TYPE_BLOCKED);
    }

    /**
     * @return Status
     */
    public static function typeDeleted()
    {
        return new static(self::TYPE_DELETED);
    }

    /**
     * @return array
     */
    public static function getAvailableStatuses()
    {
        return self::$availableTypes;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type = null)
    {
        if (null !== $type && !in_array($type, self::$availableTypes)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Status type should be %s',
                    implode(' or ', self::$availableTypes)
                )
            );
        }
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        if (null !== $state && !in_array($state, self::$availableStates)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Status type should be %s',
                    implode(' or ', self::$availableStates)
                )
            );
        }
        $this->state = $state;
    }

    /**
     * @param $statusData
     *
     * @return Status
     */
    public static function fromData($statusData)
    {
        $statusData = self::resolveOptions($statusData);

        return new self($statusData['type'], $statusData['state']);
    }

    /**
     * @param $data
     *
     * @return array
     */
    private static function resolveOptions($data)
    {
        $default = [
            'type' => null,
            'state' => null,
        ];

        return array_merge($default, $data);
    }

    /**
     * @param array $data
     *
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return self::fromData($data);
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'type' => $this->getType(),
            'state' => $this->getState(),
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $results = $this->getType();
        if (!empty($this->getState())) {
            $results .= ':'.$this->getState();
        }

        return $results;
    }
}
