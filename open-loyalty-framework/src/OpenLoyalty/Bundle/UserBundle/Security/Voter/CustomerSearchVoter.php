<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Security\Voter;

use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class CustomerSearchVoter.
 */
class CustomerSearchVoter extends Voter
{
    const PERMISSION_RESOURCE = 'CUSTOMER';
    const SEARCH_CUSTOMER = 'SEARCH_CUSTOMER';

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return $subject == null && in_array($attribute, [
            self::SEARCH_CUSTOMER,
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

        switch ($attribute) {
            case self::SEARCH_CUSTOMER:
                return $viewAdmin || $user->hasRole('ROLE_SELLER');
            default:
                return false;
        }
    }
}
