<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Security\Voter;

use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetails;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class SellerVoter.
 */
class SellerVoter extends Voter
{
    const PERMISSION_RESOURCE = 'SELLER';

    const LIST_SELLERS = 'LIST_SELLERS';
    const CREATE_SELLER = 'CREATE_SELLER';
    const VIEW = 'VIEW';
    const EDIT = 'EDIT';
    const DEACTIVATE = 'DEACTIVATE';
    const ACTIVATE = 'ACTIVATE';
    const DELETE = 'DELETE';
    const ASSIGN_POS_TO_SELLER = 'ASSIGN_POS_TO_SELLER';

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return $subject instanceof SellerDetails && in_array($attribute, [
            self::VIEW, self::EDIT, self::ACTIVATE, self::DEACTIVATE, self::DELETE,
        ]) || $subject == null && in_array($attribute, [
            self::LIST_SELLERS, self::CREATE_SELLER, self::ASSIGN_POS_TO_SELLER,
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

        $viewAdmin = $user->hasRole('ROLE_ADMIN')
                     && $user->hasPermission(self::PERMISSION_RESOURCE, [PermissionAccess::VIEW]);

        $fullAdmin = $user->hasRole('ROLE_ADMIN')
                     && $user->hasPermission(self::PERMISSION_RESOURCE, [PermissionAccess::VIEW, PermissionAccess::MODIFY]);

        switch ($attribute) {
            case self::LIST_SELLERS:
                return $viewAdmin;
            case self::CREATE_SELLER:
                return $fullAdmin;
            case self::DEACTIVATE:
                return $fullAdmin;
            case self::ACTIVATE:
                return $fullAdmin;
            case self::DELETE:
                return $fullAdmin;
            case self::ASSIGN_POS_TO_SELLER:
                return $fullAdmin;
            case self::VIEW:
                return $viewAdmin || $this->canSellerView($user, $subject);
            case self::EDIT:
                return $fullAdmin;
            default:
                return false;
        }
    }

    /**
     * @param User          $user
     * @param SellerDetails $subject
     *
     * @return bool
     */
    protected function canSellerView(User $user, SellerDetails $subject): bool
    {
        if ($user->hasRole('ROLE_SELLER') && $subject->getSellerId() && (string) $subject->getSellerId() == $user->getId()) {
            return true;
        }

        return false;
    }
}
