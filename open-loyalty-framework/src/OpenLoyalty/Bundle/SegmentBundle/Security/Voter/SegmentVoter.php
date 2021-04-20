<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SegmentBundle\Security\Voter;

use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Component\Segment\Domain\Segment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class SegmentVoter.
 */
class SegmentVoter extends Voter
{
    const PERMISSION_RESOURCE = 'SEGMENT';

    const LIST_SEGMENTS = 'LIST_SEGMENTS';
    const LIST_CUSTOMERS = 'LIST_CUSTOMERS';
    const EDIT = 'EDIT';
    const ACTIVATE = 'ACTIVATE';
    const DEACTIVATE = 'DEACTIVATE';
    const DELETE = 'DELETE';
    const CREATE_SEGMENT = 'CREATE_SEGMENT';
    const VIEW = 'VIEW';

    public function supports($attribute, $subject)
    {
        return $subject instanceof Segment && in_array($attribute, [
            self::EDIT, self::VIEW, self::DEACTIVATE, self::ACTIVATE, self::LIST_CUSTOMERS, self::DELETE,
        ]) || $subject == null && in_array($attribute, [
            self::LIST_SEGMENTS, self::CREATE_SEGMENT,
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
            case self::LIST_SEGMENTS:
                return $viewAdmin;
            case self::EDIT:
                return $fullAdmin;
            case self::CREATE_SEGMENT:
                return $fullAdmin;
            case self::VIEW:
                return $viewAdmin;
            case self::ACTIVATE:
                return $fullAdmin;
            case self::DEACTIVATE:
                return $fullAdmin;
            case self::DELETE:
                return $fullAdmin;
            case self::LIST_CUSTOMERS:
                return $viewAdmin;
            default:
                return false;
        }
    }
}
