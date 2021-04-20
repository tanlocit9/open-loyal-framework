<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Bundle\CoreBundle\Security\Voter;

use OpenLoyalty\Bundle\CampaignBundle\Security\Voter\CampaignVoter as BaseCampaignVoter;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\CustomerVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class CampaignVoter.
 */
class CampaignVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return $subject == null && in_array($attribute, [
                BaseCampaignVoter::LIST_ALL_VISIBLE_CAMPAIGNS,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $viewCustomerAdmin = $user->hasRole('ROLE_ADMIN')
                     && $user->hasPermission(CustomerVoter::PERMISSION_RESOURCE, [PermissionAccess::VIEW]);

        $viewAdmin = $user->hasRole('ROLE_ADMIN')
                     && $user->hasPermission(BaseCampaignVoter::PERMISSION_RESOURCE, [PermissionAccess::VIEW]);

        switch ($attribute) {
            case BaseCampaignVoter::LIST_ALL_VISIBLE_CAMPAIGNS:
                return ($viewAdmin && $viewCustomerAdmin) || $user->hasRole('ROLE_SELLER');
            default:
                return false;
        }
    }
}
