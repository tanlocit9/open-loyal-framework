<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Security\Voter;

use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class CampaignCategoryVoter.
 */
class CampaignCategoryVoter extends Voter
{
    const PERMISSION_RESOURCE = 'REWARD_CAMPAIGN';

    const CREATE_CAMPAIGN_CATEGORY = 'CREATE_CAMPAIGN_CATEGORY';
    const EDIT = 'EDIT';
    const LIST_ALL_CAMPAIGN_CATEGORIES = 'LIST_ALL_CAMPAIGN_CATEGORIES';
    const VIEW = 'VIEW';

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        $allowEntity = $subject instanceof CampaignCategory && in_array($attribute, [self::EDIT, self::VIEW]);
        $allowGrid = $subject == null && in_array($attribute, [self::CREATE_CAMPAIGN_CATEGORY, self::LIST_ALL_CAMPAIGN_CATEGORIES]);

        return $allowEntity || $allowGrid;
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

        $viewAdmin = $user->hasRole('ROLE_ADMIN')
                     && $user->hasPermission(self::PERMISSION_RESOURCE, [PermissionAccess::VIEW]);

        $fullAdmin = $user->hasRole('ROLE_ADMIN')
                     && $user->hasPermission(self::PERMISSION_RESOURCE, [PermissionAccess::VIEW, PermissionAccess::MODIFY]);

        switch ($attribute) {
            case self::CREATE_CAMPAIGN_CATEGORY:
                return $fullAdmin;
            case self::LIST_ALL_CAMPAIGN_CATEGORIES:
                return $viewAdmin;
            case self::EDIT:
                return $fullAdmin;
            case self::VIEW:
                return $viewAdmin || $user->hasRole('ROLE_SELLER');
            default:
                return false;
        }
    }
}
