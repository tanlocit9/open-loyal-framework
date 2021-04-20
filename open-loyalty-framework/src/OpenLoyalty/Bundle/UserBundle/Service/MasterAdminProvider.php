<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Service;

use OpenLoyalty\Bundle\UserBundle\CQRS\AdminId;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class MasterAdminProvider.
 */
class MasterAdminProvider implements UserProviderInterface
{
    const USERNAME = 'api';

    const INTERNAL_ID = '86b0c8ad-dadc-4d9f-8a72-8b55b9f81c3d';

    /** @var string|null */
    private $masterApiKey;

    /** @var UserManager */
    protected $userManager;

    /**
     * MasterAdminProvider constructor.
     *
     * @param string|null $masterApiKey
     * @param UserManager $userManager
     */
    public function __construct(?string $masterApiKey, UserManager $userManager)
    {
        $this->masterApiKey = $masterApiKey;
        $this->userManager = $userManager;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        if (empty($this->masterApiKey)) {
            return null;
        }

        if ($username !== self::USERNAME) {
            throw new UsernameNotFoundException();
        }

        $id = new AdminId(self::INTERNAL_ID);
        $user = $this->userManager->createNewAdmin($id, true);
        $user->setUsername(self::USERNAME);
        $user->setPassword($this->masterApiKey);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Heal\SecurityBundle\Entity\Admin';
    }
}
