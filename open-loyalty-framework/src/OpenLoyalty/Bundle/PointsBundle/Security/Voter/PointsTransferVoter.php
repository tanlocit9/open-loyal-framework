<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Security\Voter;

use OpenLoyalty\Bundle\UserBundle\Entity\Seller;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetails;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class PointsTransferVoter.
 */
class PointsTransferVoter extends Voter
{
    const PERMISSION_RESOURCE = 'POINT_TRANSFER';

    const LIST_POINTS_TRANSFERS = 'LIST_POINTS_TRANSFERS';
    const ADD_POINTS = 'ADD_POINTS';
    const SPEND_POINTS = 'SPEND_POINTS';
    const TRANSFER_POINTS = 'TRANSFER_POINTS';
    const CANCEL = 'CANCEL';
    const LIST_CUSTOMER_POINTS_TRANSFERS = 'LIST_CUSTOMER_POINTS_TRANSFERS';

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject): bool
    {
        return $subject instanceof PointsTransferDetails && in_array($attribute, [self::CANCEL])
            || $subject == null
                && in_array(
                    $attribute,
                    [
                        self::LIST_CUSTOMER_POINTS_TRANSFERS,
                        self::LIST_POINTS_TRANSFERS,
                        self::ADD_POINTS,
                        self::SPEND_POINTS,
                        self::TRANSFER_POINTS,
                    ]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var User|Seller $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $viewAdmin = $user->hasRole('ROLE_ADMIN')
            && $user->hasPermission(self::PERMISSION_RESOURCE, [PermissionAccess::VIEW]);

        $fullAdmin = $user->hasRole('ROLE_ADMIN')
            && $user->hasPermission(self::PERMISSION_RESOURCE, [PermissionAccess::VIEW, PermissionAccess::MODIFY]);

        switch ($attribute) {
            case self::LIST_POINTS_TRANSFERS:
                return $viewAdmin || $user->hasRole('ROLE_SELLER');
            case self::TRANSFER_POINTS:
                return $fullAdmin || $user->hasRole('ROLE_PARTICIPANT');
            case self::ADD_POINTS:
                return $fullAdmin || ($user->hasRole('ROLE_SELLER') && $user->isAllowPointTransfer());
            case self::SPEND_POINTS:
                return $fullAdmin || ($user->hasRole('ROLE_SELLER') && $user instanceof Seller && $user->isAllowPointTransfer());
            case self::CANCEL:
                return $fullAdmin;
            case self::LIST_CUSTOMER_POINTS_TRANSFERS:
                return $user->hasRole('ROLE_PARTICIPANT');
            default:
                return false;
        }
    }
}
