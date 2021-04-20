<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Bundle\UserBundle\Security\Voter;

use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetailsRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class CustomerVoter.
 */
class CustomerVoter extends Voter
{
    const PERMISSION_RESOURCE = 'CUSTOMER';

    const ACTIVATE = 'ACTIVATE';
    const ASSIGN_POS = 'ASSIGN_POS';
    const ASSIGN_CUSTOMER_LEVEL = 'ASSIGN_CUSTOMER_LEVEL';
    const CREATE_CUSTOMER = 'CREATE_CUSTOMER';
    const DEACTIVATE = 'DEACTIVATE';
    const EDIT = 'EDIT';
    const LIST_CUSTOMERS = 'LIST_CUSTOMERS';
    const VIEW = 'VIEW';
    const VIEW_STATUS = 'VIEW_STATUS';

    /**
     * @var SellerDetailsRepository
     */
    private $sellerDetailsRepository;

    /**
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * CustomerVoter constructor.
     *
     * @param SellerDetailsRepository $sellerDetailsRepository
     * @param SettingsManager         $settingsManager
     */
    public function __construct(SellerDetailsRepository $sellerDetailsRepository, SettingsManager $settingsManager)
    {
        $this->sellerDetailsRepository = $sellerDetailsRepository;
        $this->settingsManager = $settingsManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return $this->supportsCustomerDetails($subject, $attribute) || $this->supportsAnonymous($subject, $attribute);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $viewAdmin = $user->hasRole('ROLE_ADMIN')
                     && $user->hasPermission(self::PERMISSION_RESOURCE, [PermissionAccess::VIEW]);

        $fullAdmin = $user->hasRole('ROLE_ADMIN')
                     && $user->hasPermission(self::PERMISSION_RESOURCE, [PermissionAccess::VIEW, PermissionAccess::MODIFY]);

        switch ($attribute) {
            case self::ACTIVATE:
                return $fullAdmin || $user->hasRole('ROLE_SELLER');
            case self::ASSIGN_CUSTOMER_LEVEL:
                return $fullAdmin || $user->hasRole('ROLE_SELLER');
            case self::ASSIGN_POS:
                return $fullAdmin || $user->hasRole('ROLE_SELLER');
            case self::CREATE_CUSTOMER:
                return $fullAdmin || $user->hasRole('ROLE_SELLER');
            case self::DEACTIVATE:
                return $fullAdmin || $user->hasRole('ROLE_SELLER');
            case self::EDIT:
                return $fullAdmin || $this->canSellerOrCustomerEdit($user, $subject);
            case self::LIST_CUSTOMERS:
                return $viewAdmin || $user->hasRole('ROLE_SELLER');
            case self::VIEW:
                return $viewAdmin || $this->canSellerOrCustomerView($user, $subject);
            case self::VIEW_STATUS:
                return $viewAdmin || $this->canSellerOrCustomerView($user, $subject);
            default:
                return false;
        }
    }

    /**
     * @param User            $user
     * @param CustomerDetails $customerDetails
     *
     * @return bool
     */
    private function canSellerOrCustomerView(User $user, CustomerDetails $customerDetails): bool
    {
        if ($user->hasRole('ROLE_PARTICIPANT') && $customerDetails->getCustomerId() && (string) $customerDetails->getCustomerId() === $user->getId()) {
            return true;
        }

        if ($user->hasRole('ROLE_SELLER')) {
            return true;
        }

        return false;
    }

    /**
     * @param User            $user
     * @param CustomerDetails $customerDetails
     *
     * @return bool
     */
    private function canSellerOrCustomerEdit(User $user, CustomerDetails $customerDetails): bool
    {
        if ($user->hasRole('ROLE_PARTICIPANT') && $customerDetails->getCustomerId() && (string) $customerDetails->getCustomerId() === $user->getId()) {
            return true;
        }

        if ($user->hasRole('ROLE_SELLER')) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed  $subject
     * @param string $attribute
     *
     * @return bool
     */
    private function supportsCustomerDetails($subject, string $attribute): bool
    {
        return
            $subject instanceof CustomerDetails
            && \in_array(
                $attribute,
                [
                    self::ACTIVATE,
                    self::ASSIGN_CUSTOMER_LEVEL,
                    self::ASSIGN_POS,
                    self::DEACTIVATE,
                    self::EDIT,
                    self::VIEW,
                    self::VIEW_STATUS,
                ],
                true
            )
        ;
    }

    /**
     * @param mixed  $subject
     * @param string $attribute
     *
     * @return bool
     */
    private function supportsAnonymous($subject, string $attribute): bool
    {
        return
            $subject === null
            && \in_array($attribute, [
                self::LIST_CUSTOMERS,
                self::CREATE_CUSTOMER,
            ], true)
        ;
    }
}
