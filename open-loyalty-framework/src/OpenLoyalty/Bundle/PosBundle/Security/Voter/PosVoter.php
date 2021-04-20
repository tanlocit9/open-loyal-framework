<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PosBundle\Security\Voter;

use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Component\Pos\Domain\Pos;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class PosVoter.
 */
class PosVoter extends Voter
{
    const PERMISSION_RESOURCE = 'POS';

    const LIST_POS = 'LIST_POS';
    const EDIT = 'EDIT';
    const CREATE_POS = 'CREATE_POS';
    const VIEW = 'VIEW';

    public function supports($attribute, $subject)
    {
        return $subject instanceof Pos && in_array($attribute, [
            self::EDIT, self::VIEW,
        ]) || $subject == null && in_array($attribute, [
            self::LIST_POS, self::CREATE_POS,
        ]);
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
            case self::LIST_POS:
                return $viewAdmin || $user->hasRole('ROLE_SELLER');
            case self::EDIT:
                return $fullAdmin;
            case self::CREATE_POS:
                return $fullAdmin;
            case self::VIEW:
                return $viewAdmin || $user->hasRole('ROLE_SELLER');
            default:
                return false;
        }
    }
}
