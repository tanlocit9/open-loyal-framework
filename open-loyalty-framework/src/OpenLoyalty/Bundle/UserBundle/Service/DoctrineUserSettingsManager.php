<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use OpenLoyalty\Bundle\UserBundle\Entity\UserSettingsEntry;
use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class DoctrineUserSettingsManager.
 */
class DoctrineUserSettingsManager implements UserSettingsManagerInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var CustomerId
     */
    private $customerId;

    /**
     * DoctrineUserSettingsManager constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $settings, $flush = true): void
    {
        foreach ($settings as $key => $entry) {
            $this->em->persist($entry);
        }

        if ($flush) {
            $this->em->flush();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws OptimisticLockException
     */
    public function removeAll(): void
    {
        $settings = $this->getSettings();
        foreach ($settings as $entry) {
            if (null !== $entry) {
                $this->em->remove($entry);
            }
        }
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function removeSettingByKey(string $key): void
    {
        $setting = $this->getSettingByKey($key);

        $this->em->remove($setting);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getSettings(): array
    {
        $entries = $this->em->getRepository('OpenLoyaltyUserBundle:UserSettingsEntry')
            ->findBy(['user' => $this->customerId]);

        if ($entries instanceof ArrayCollection) {
            $entries = $entries->toArray();
        }

        return $entries;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingByKey(string $key): ?UserSettingsEntry
    {
        return $this->em->getRepository('OpenLoyaltyUserBundle:UserSettingsEntry')
            ->findOneBy(['key' => $key, 'user' => $this->customerId]);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserId(CustomerId $customerId): void
    {
        $this->customerId = $customerId;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function createSetting(string $key): UserSettingsEntry
    {
        return new UserSettingsEntry(
            $this->em->getReference('OpenLoyaltyUserBundle:User', $this->getUserId()),
            $key
        );
    }
}
