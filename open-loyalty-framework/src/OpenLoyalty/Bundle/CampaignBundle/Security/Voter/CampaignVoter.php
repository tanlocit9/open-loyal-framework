<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Security\Voter;

use OpenLoyalty\Bundle\CampaignBundle\Service\CampaignProvider;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class CampaignVoter.
 */
class CampaignVoter extends Voter
{
    const PERMISSION_RESOURCE = 'REWARD_CAMPAIGN';

    const CREATE_CAMPAIGN = 'CREATE_CAMPAIGN';
    const EDIT = 'EDIT';
    const LIST_ALL_CAMPAIGNS = 'LIST_ALL_CAMPAIGNS';
    const LIST_ALL_VISIBLE_CAMPAIGNS = 'LIST_ALL_VISIBLE_CAMPAIGNS';
    const LIST_ALL_ACTIVE_CAMPAIGNS = 'LIST_ALL_ACTIVE_CAMPAIGNS';
    const LIST_ALL_BOUGHT_CAMPAIGNS = 'LIST_ALL_BOUGHT_CAMPAIGNS';
    const VIEW = 'VIEW';
    const LIST_CAMPAIGNS_AVAILABLE_FOR_ME = 'LIST_CAMPAIGNS_AVAILABLE_FOR_ME';
    const LIST_CAMPAIGNS_BOUGHT_BY_ME = 'LIST_CAMPAIGNS_BOUGHT_BY_ME';
    const BUY = 'BUY';
    const LIST_ALL_CAMPAIGNS_CUSTOMERS = 'LIST_ALL_CAMPAIGNS_CUSTOMERS';
    const BUY_FOR_CUSTOMER_SELLER = 'BUY_FOR_CUSTOMER_SELLER';
    const BUY_FOR_CUSTOMER_ADMIN = 'BUY_FOR_CUSTOMER_ADMIN';
    const MARK_MULTIPLE_COUPONS_AS_USED = 'MARK_MULTIPLE_COUPONS_AS_USED';
    const MARK_SELF_MULTIPLE_COUPONS_AS_USED = 'MARK_SELF_MULTIPLE_COUPONS_AS_USED';
    const CASHBACK = 'CASHBACK';
    const VIEW_BUY_FOR_CUSTOMER_SELLER = 'VIEW_BUY_FOR_CUSTOMER_SELLER';
    const VIEW_BUY_FOR_CUSTOMER_ADMIN = 'VIEW_BUY_FOR_CUSTOMER_ADMIN';

    /**
     * @var CampaignProvider
     */
    protected $campaignsProvider;

    /**
     * CampaignVoter constructor.
     *
     * @param CampaignProvider $campaignsProvider
     */
    public function __construct(CampaignProvider $campaignsProvider)
    {
        $this->campaignsProvider = $campaignsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return $subject instanceof Campaign && in_array($attribute, [
            self::EDIT, self::VIEW, self::BUY,
        ]) || $subject == null && in_array($attribute, [
                self::CREATE_CAMPAIGN,
                self::LIST_ALL_CAMPAIGNS,
                self::LIST_ALL_BOUGHT_CAMPAIGNS,
                self::LIST_ALL_CAMPAIGNS_CUSTOMERS,
                self::LIST_ALL_ACTIVE_CAMPAIGNS,
                self::LIST_ALL_VISIBLE_CAMPAIGNS,
                self::LIST_CAMPAIGNS_BOUGHT_BY_ME,
                self::LIST_CAMPAIGNS_AVAILABLE_FOR_ME,
                self::BUY_FOR_CUSTOMER_SELLER,
                self::BUY_FOR_CUSTOMER_ADMIN,
                self::MARK_MULTIPLE_COUPONS_AS_USED,
                self::MARK_SELF_MULTIPLE_COUPONS_AS_USED,
                self::CASHBACK,
                self::VIEW_BUY_FOR_CUSTOMER_SELLER,
                self::VIEW_BUY_FOR_CUSTOMER_ADMIN,
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
            case self::CREATE_CAMPAIGN:
                return $fullAdmin;
            case self::LIST_ALL_CAMPAIGNS:
                return $viewAdmin;
            case self::LIST_ALL_BOUGHT_CAMPAIGNS:
                return $viewAdmin;
            case self::LIST_ALL_CAMPAIGNS_CUSTOMERS:
                return $viewAdmin;
            case self::LIST_ALL_ACTIVE_CAMPAIGNS:
                return $viewAdmin;
            case self::LIST_ALL_VISIBLE_CAMPAIGNS:
                return $viewAdmin || $user->hasRole('ROLE_SELLER');
            case self::BUY_FOR_CUSTOMER_SELLER:
                return $user->hasRole('ROLE_SELLER');
            case self::VIEW_BUY_FOR_CUSTOMER_SELLER:
                return $user->hasRole('ROLE_SELLER');
            case self::VIEW_BUY_FOR_CUSTOMER_ADMIN:
                return $viewAdmin;
            case self::BUY_FOR_CUSTOMER_ADMIN:
                return $fullAdmin;
            case self::EDIT:
                return $fullAdmin;
            case self::VIEW:
                return $viewAdmin || $this->canSellerOrCustomerView($user, $subject);
            case self::LIST_CAMPAIGNS_AVAILABLE_FOR_ME:
                return $user->hasRole('ROLE_PARTICIPANT');
            case self::LIST_CAMPAIGNS_BOUGHT_BY_ME:
                return $user->hasRole('ROLE_PARTICIPANT');
            case self::BUY:
                return $user->hasRole('ROLE_PARTICIPANT');
            case self::MARK_MULTIPLE_COUPONS_AS_USED:
                return $fullAdmin;
            case self::MARK_SELF_MULTIPLE_COUPONS_AS_USED:
                return $user->hasRole('ROLE_PARTICIPANT');
            case self::CASHBACK:
                return $fullAdmin;
            default:
                return false;
        }
    }

    /**
     * @param User $user
     * @param      $subject
     *
     * @return bool
     */
    protected function canSellerOrCustomerView(User $user, $subject): bool
    {
        if ($user->hasRole('ROLE_SELLER')) {
            return true;
        }
        if ($user->hasRole('ROLE_PARTICIPANT')) {
            $customers = array_values($this->campaignsProvider->visibleForCustomers($subject));
            if (in_array($user->getId(), $customers)) {
                return true;
            }
        }

        return false;
    }
}
