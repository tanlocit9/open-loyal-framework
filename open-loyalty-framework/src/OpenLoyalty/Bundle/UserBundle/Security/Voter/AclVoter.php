<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Security\Voter;

use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class AclVoter.
 */
class AclVoter extends Voter
{
    const PERMISSION_RESOURCE = 'ACL';

    const LIST = 'LIST_ROLES';
    const EDIT = 'EDIT';
    const VIEW = 'VIEW';
    const CREATE_ROLE = 'CREATE_ROLE';

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject): bool
    {
        return $subject instanceof Role && in_array($attribute, [self::VIEW, self::EDIT])
            || in_array($attribute, [self::CREATE_ROLE, self::LIST]);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $viewAdmin = $user->hasRole('ROLE_ADMIN')
            && $user->hasPermission(self::PERMISSION_RESOURCE, [PermissionAccess::VIEW]);

        $fullAdmin = $user->hasRole('ROLE_ADMIN')
            && $user->hasPermission(self::PERMISSION_RESOURCE, [PermissionAccess::VIEW, PermissionAccess::MODIFY]);

        switch ($attribute) {
            case self::LIST:
                return $viewAdmin;
            case self::VIEW:
                return $viewAdmin;
            case self::EDIT:
                return $fullAdmin;
            case self::CREATE_ROLE:
                return $fullAdmin;
            default:
                return false;
        }
    }
}
