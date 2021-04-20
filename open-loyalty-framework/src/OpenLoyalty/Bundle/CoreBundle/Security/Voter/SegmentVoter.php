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
use OpenLoyalty\Bundle\UtilityBundle\Security\Voter\UtilityVoter;
use OpenLoyalty\Component\Segment\Domain\Segment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use OpenLoyalty\Bundle\SegmentBundle\Security\Voter\SegmentVoter as BaseSegmentVoter;

/**
 * Class SegmentVoter.
 */
class SegmentVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return ($subject instanceof Segment && in_array($attribute, [BaseSegmentVoter::LIST_CUSTOMERS]))
            || ($subject === null && in_array($attribute, [UtilityVoter::GENERATE_CSV_BY_SEGMENT]));
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        $viewCustomerAdmin = $user->hasRole('ROLE_ADMIN')
             && $user->hasPermission(CustomerVoter::PERMISSION_RESOURCE, [PermissionAccess::VIEW]);

        switch ($attribute) {
            case UtilityVoter::GENERATE_CSV_BY_SEGMENT:
                return $viewCustomerAdmin;
            case BaseSegmentVoter::LIST_CUSTOMERS:
                return $viewCustomerAdmin;
            default:
                return false;
        }
    }
}
