<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Service;

use Doctrine\ORM\EntityManager;
use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class UserSettingsManagerFactory.
 */
class UserSettingsManagerFactory
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * UserSettingsManagerFactory constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return UserSettingsManager
     */
    public function create(): UserSettingsManager
    {
        $persistence = new DoctrineUserSettingsManager($this->em);

        return new UserSettingsManager($persistence);
    }

    /**
     * @param CustomerId $customerId
     *
     * @return UserSettingsManager
     */
    public function createForUser(CustomerId $customerId): UserSettingsManager
    {
        $persistence = new DoctrineUserSettingsManager($this->em);
        $settingsManager = new UserSettingsManager($persistence);
        $settingsManager->setUserId($customerId);

        return $settingsManager;
    }
}
