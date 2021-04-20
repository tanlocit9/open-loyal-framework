<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Security\Voter;

use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class SettingsVoter.
 */
class SettingsVoter extends Voter
{
    const PERMISSION_RESOURCE = 'SETTINGS';

    const VIEW_SETTINGS_CHOICES = 'VIEW_SETTINGS_CHOICES';
    const VIEW_SETTINGS = 'VIEW_SETTINGS';
    const EDIT_SETTINGS = 'EDIT_SETTINGS';

    public function supports($attribute, $subject)
    {
        return $subject == null && in_array($attribute, [
            self::VIEW_SETTINGS, self::VIEW_SETTINGS_CHOICES, self::EDIT_SETTINGS,
        ]);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($attribute === self::VIEW_SETTINGS_CHOICES) {
            return true;
        }

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
            case self::VIEW_SETTINGS:
                return $viewAdmin;
            case self::EDIT_SETTINGS:
                return $fullAdmin;
            default:
                return false;
        }
    }
}
