<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\SettingsBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use OpenLoyalty\Bundle\SettingsBundle\Entity\SettingsEntry;
use OpenLoyalty\Bundle\SettingsBundle\Model\Settings;
use OpenLoyalty\Bundle\SettingsBundle\Exception\AlreadyExistException;

/**
 * Class DoctrineSettingsManager.
 */
class DoctrineSettingsManager implements SettingsManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * SettingsManager constructor.
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
    public function save(Settings $settings, $flush = true): void
    {
        foreach ($settings->getEntries() as $key => $entry) {
            $this->em->persist($entry);
        }

        if ($flush) {
            try {
                $this->em->flush();
            } catch (UniqueConstraintViolationException $exception) {
                throw new AlreadyExistException();
            }
        }
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeAll(): void
    {
        $settings = $this->getSettings();
        foreach ($settings->getEntries() as $entry) {
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
    public function getSettings(): Settings
    {
        $entries = $this->em->getRepository('OpenLoyaltySettingsBundle:SettingsEntry')->findAll();
        if ($entries instanceof ArrayCollection) {
            $entries = $entries->toArray();
        }

        return Settings::fromArray($entries);
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingByKey(string $key): ?SettingsEntry
    {
        return $this->em->getRepository('OpenLoyaltySettingsBundle:SettingsEntry')
            ->findOneBy(['key' => $key]);
    }
}
