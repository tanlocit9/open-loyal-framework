<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\CoreBundle\Security\Voter;

use OpenLoyalty\Bundle\PosBundle\Security\Voter\PosVoter;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\SellerVoter as BaseSellerVoter;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class SellerVoter.
 */
class SellerVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return in_array($attribute, [BaseSellerVoter::ASSIGN_POS_TO_SELLER]);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        $fullAdmin = $user->hasRole('ROLE_ADMIN')
            && $user->hasPermission(BaseSellerVoter::PERMISSION_RESOURCE, [PermissionAccess::VIEW, PermissionAccess::MODIFY]);

        $viewPosAdmin = $user->hasRole('ROLE_ADMIN')
            && $user->hasPermission(PosVoter::PERMISSION_RESOURCE, [PermissionAccess::VIEW]);

        switch ($attribute) {
            case BaseSellerVoter::ASSIGN_POS_TO_SELLER:
                return $fullAdmin && $viewPosAdmin;
            default:
                return false;
        }
    }
}
