<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Security\Voter;

use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class AdminVoter.
 */
class AdminVoter extends Voter
{
    const PERMISSION_RESOURCE = 'ADMIN';

    const LIST = 'LIST_ADMINS';
    const EDIT = 'EDIT';
    const VIEW = 'VIEW';
    const CREATE_USER = 'CREATE_USER';

    public function supports($attribute, $subject)
    {
        return $subject instanceof Admin && in_array($attribute, [
            self::VIEW, self::EDIT,
        ]) || in_array($attribute, [self::CREATE_USER, self::LIST]);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
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
            case self::CREATE_USER:
                return $fullAdmin;
            default:
                return false;
        }
    }
}
