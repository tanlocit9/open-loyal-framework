<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ol__user_settings")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @JMS\ExclusionPolicy("all")
 * @UniqueEntity(
 *  fields={"user", "key"},
 *  message="This key already has value for this user"
 * )
 */
class UserSettingsEntry implements \Serializable
{
    /**
     * @var User
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinTable(name="ol__user")
     */
    protected $user;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=128)
     * @JMS\Expose()
     */
    protected $key;

    /**
     * @var mixed
     * @ORM\Column(type="string")
     */
    protected $value;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="created_at")
     * @JMS\Expose()
     */
    protected $createdAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true, name="deleted_at")
     */
    protected $deletedAt = null;

    /**
     * UserKeyValue constructor.
     *
     * @param User   $user
     * @param string $key
     * @param mixed  $value
     */
    public function __construct(User $user, string $key, $value = null)
    {
        $this->createdAt = new \DateTime('now');
        $this->user = $user;
        $this->key = $key;
        if ($value !== null) {
            $this->setValue($value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return \serialize(array(
            $this->user,
            $this->key,
            $this->value,
        ));
    }

    /**
     * @param string $serialized
     *
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        [
            $this->user,
            $this->key,
            $this->value,
        ] = \unserialize($serialized);
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime|null $deletedAt
     */
    public function setDeletedAt($deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }
}
