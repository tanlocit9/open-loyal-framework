<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UtilityBundle\Security\Voter;

use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class UtilityVoter.
 */
class UtilityVoter extends Voter
{
    const PERMISSION_RESOURCE = 'SEGMENT_EXPORT';

    const GENERATE_CSV_BY_SEGMENT = 'GENERATE_CSV_BY_SEGMENT';
    const GENERATE_CSV_BY_LEVEL = 'GENERATE_CSV_BY_LEVEL';

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    public function supports($attribute, $subject)
    {
        return $subject == null && in_array($attribute, [self::GENERATE_CSV_BY_SEGMENT, self::GENERATE_CSV_BY_LEVEL]);
    }

    /**
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $viewPermission = $user->hasRole('ROLE_ADMIN') && $user->hasPermission(
                self::PERMISSION_RESOURCE, [PermissionAccess::VIEW]
            );

        switch ($attribute) {
            case self::GENERATE_CSV_BY_SEGMENT:
                return $viewPermission;
            case self::GENERATE_CSV_BY_LEVEL:
                return $viewPermission;
            default:
                return false;
        }
    }
}
