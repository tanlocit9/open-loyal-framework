<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\LevelBundle\Security\Voter;

use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Component\Level\Domain\Level;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class LevelVoter.
 */
class LevelVoter extends Voter
{
    const PERMISSION_RESOURCE = 'LEVEL';

    const CREATE_LEVEL = 'CREATE_LEVEL';
    const EDIT = 'EDIT';
    const LIST_LEVELS = 'LIST_LEVELS';
    const LIST_CUSTOMERS = 'LIST_CUSTOMERS';
    const VIEW = 'VIEW';
    const ACTIVATE = 'ACTIVATE';
    const CUSTOMER_LIST_LEVELS = 'CUSTOMER_LIST_LEVELS';

    public function supports($attribute, $subject)
    {
        return $subject instanceof Level && in_array($attribute, [
            self::EDIT, self::VIEW, self::ACTIVATE, self::LIST_CUSTOMERS, self::CUSTOMER_LIST_LEVELS,
        ]) || $subject == null && in_array($attribute, [
            self::CREATE_LEVEL, self::LIST_LEVELS, self::CUSTOMER_LIST_LEVELS,
        ]);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        $viewAdmin = $user->hasRole('ROLE_ADMIN')
            && $user->hasPermission(self::PERMISSION_RESOURCE, [PermissionAccess::VIEW]);

        $fullAdmin = $user->hasRole('ROLE_ADMIN')
            && $user->hasPermission(self::PERMISSION_RESOURCE, [PermissionAccess::VIEW, PermissionAccess::MODIFY]);

        switch ($attribute) {
            case self::CREATE_LEVEL:
                return $fullAdmin;
            case self::LIST_LEVELS:
                return $viewAdmin || $user->hasRole('ROLE_SELLER');
            case self::EDIT:
                return $fullAdmin;
            case self::VIEW:
                return $viewAdmin || $user->hasRole('ROLE_SELLER');
            case self::LIST_CUSTOMERS:
                return $viewAdmin;
            case self::ACTIVATE:
                return $fullAdmin;
            case self::CUSTOMER_LIST_LEVELS:
                return $user->hasRole('ROLE_PARTICIPANT');
            default:
                return false;
        }
    }
}
