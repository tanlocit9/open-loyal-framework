<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\CoreBundle\Security\Voter;

use OpenLoyalty\Bundle\PointsBundle\Security\Voter\PointsTransferVoter;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\CustomerVoter as BaseCustomerVoter;
use OpenLoyalty\Bundle\LevelBundle\Security\Voter\LevelVoter as BaseLevelVoter;
use OpenLoyalty\Bundle\CampaignBundle\Security\Voter\CampaignVoter as BaseCampaignVoter;

/**
 * Class CustomerVoter.
 */
class CustomerVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return
            (
                $subject instanceof CustomerDetails && \in_array($attribute, [BaseCustomerVoter::ASSIGN_CUSTOMER_LEVEL])
            )
            ||
            (
                $subject instanceof PointsTransferDetails && in_array($attribute, [PointsTransferVoter::CANCEL])
            )
            ||
            (
                $subject === null && in_array($attribute,
                [
                    PointsTransferVoter::ADD_POINTS,
                    PointsTransferVoter::SPEND_POINTS,
                    PointsTransferVoter::TRANSFER_POINTS,
                    BaseCampaignVoter::VIEW_BUY_FOR_CUSTOMER_ADMIN,
                    BaseCampaignVoter::LIST_ALL_CAMPAIGNS_CUSTOMERS,
                ])
            );
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

        $fullCustomerAdmin = $user->hasRole('ROLE_ADMIN')
            && $user->hasPermission(BaseCustomerVoter::PERMISSION_RESOURCE, [PermissionAccess::VIEW, PermissionAccess::MODIFY]);

        $viewCustomerAdmin = $user->hasRole('ROLE_ADMIN')
            && $user->hasPermission(BaseCustomerVoter::PERMISSION_RESOURCE, [PermissionAccess::VIEW]);

        switch ($attribute) {
            case BaseCustomerVoter::ASSIGN_CUSTOMER_LEVEL:
                return $viewLevelAdmin;
            case PointsTransferVoter::ADD_POINTS:
            case PointsTransferVoter::SPEND_POINTS:
            case PointsTransferVoter::TRANSFER_POINTS:
                return $fullCustomerAdmin;
            case BaseCampaignVoter::VIEW_BUY_FOR_CUSTOMER_ADMIN:
            case BaseCampaignVoter::LIST_ALL_CAMPAIGNS_CUSTOMERS:
                return $viewCustomerAdmin;
            default:
                return false;
        }
    }
}
