<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\CoreBundle\Security\Voter;

use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\CustomerVoter;
use OpenLoyalty\Component\Level\Domain\Level;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use OpenLoyalty\Bundle\LevelBundle\Security\Voter\LevelVoter as BaseLevelVoter;

/**
 * Class LevelVoter.
 */
class LevelVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return $subject instanceof Level && in_array($attribute, [BaseLevelVoter::LIST_CUSTOMERS]);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        $viewLevelAdmin = $user->hasRole('ROLE_ADMIN')
             && $user->hasPermission(BaseLevelVoter::PERMISSION_RESOURCE, [PermissionAccess::VIEW]);

        $viewCustomerAdmin = $user->hasRole('ROLE_ADMIN')
            && $user->hasPermission(CustomerVoter::PERMISSION_RESOURCE, [PermissionAccess::VIEW]);

        switch ($attribute) {
            case BaseLevelVoter::LIST_CUSTOMERS:
                return $viewLevelAdmin && $viewCustomerAdmin;
            default:
                return false;
        }
    }
}
