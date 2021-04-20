<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Security\Voter;

use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Security\PermissionAccess;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class TransactionVoter.
 */
class TransactionVoter extends Voter
{
    const PERMISSION_RESOURCE = 'TRANSACTION';

    const LIST_TRANSACTIONS = 'LIST_TRANSACTIONS';
    const LIST_CURRENT_CUSTOMER_TRANSACTIONS = 'LIST_CURRENT_CUSTOMER_TRANSACTIONS';
    const LIST_CURRENT_POS_TRANSACTIONS = 'LIST_CURRENT_POS_TRANSACTIONS';
    const LIST_ITEM_LABELS = 'LIST_ITEM_LABELS';
    const VIEW = 'VIEW';
    const EDIT_TRANSACTION_LABELS = 'EDIT_TRANSACTION_LABELS';
    const CREATE_TRANSACTION = 'CREATE_TRANSACTION';
    const ASSIGN_CUSTOMER_TO_TRANSACTION = 'ASSIGN_CUSTOMER_TO_TRANSACTION';
    const APPEND_LABELS_TO_TRANSACTION = 'APPEND_LABELS_TO_TRANSACTION';
    const LIST_CUSTOMER_TRANSACTIONS = 'LIST_CUSTOMER_TRANSACTIONS';

    /**
     * @var SellerDetailsRepository
     */
    private $sellerDetailsRepository;

    /**
     * TransactionVoter constructor.
     *
     * @param SellerDetailsRepository $sellerDetailsRepository
     */
    public function __construct(SellerDetailsRepository $sellerDetailsRepository)
    {
        $this->sellerDetailsRepository = $sellerDetailsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return $subject instanceof TransactionDetails && in_array($attribute, [
            self::VIEW, self::APPEND_LABELS_TO_TRANSACTION,
        ]) || $subject instanceof Transaction && in_array($attribute, [
                self::ASSIGN_CUSTOMER_TO_TRANSACTION,
        ]) || $subject == null && in_array($attribute, [
            self::LIST_TRANSACTIONS, self::LIST_CURRENT_CUSTOMER_TRANSACTIONS, self::LIST_CURRENT_POS_TRANSACTIONS,
            self::LIST_ITEM_LABELS, self::CREATE_TRANSACTION, self::EDIT_TRANSACTION_LABELS,
        ]) || $subject instanceof CustomerDetails && in_array($attribute, [
            self::LIST_CUSTOMER_TRANSACTIONS,
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
            case self::LIST_TRANSACTIONS:
                return $viewAdmin;
            case self::CREATE_TRANSACTION:
                return $fullAdmin;
            case self::EDIT_TRANSACTION_LABELS:
                return $fullAdmin;
            case self::ASSIGN_CUSTOMER_TO_TRANSACTION:
                return $fullAdmin || $this->canSellerOrCustomerAssign($user, $subject);
            case self::APPEND_LABELS_TO_TRANSACTION:
                return $this->canAppendLabels($user, $subject);
            case self::LIST_CURRENT_POS_TRANSACTIONS:
                return $user->hasRole('ROLE_SELLER');
            case self::LIST_CURRENT_CUSTOMER_TRANSACTIONS:
                return $user->hasRole('ROLE_PARTICIPANT');
            case self::LIST_CUSTOMER_TRANSACTIONS:
                return $viewAdmin || $user->hasRole('ROLE_PARTICIPANT');
            case self::VIEW:
                return $viewAdmin || $this->canSellerOrCustomerView($user, $subject);
            case self::LIST_ITEM_LABELS:
                return true;
            default:
                return false;
        }
    }

    /**
     * @param User               $user
     * @param TransactionDetails $subject
     *
     * @return bool
     */
    protected function canSellerOrCustomerView(User $user, TransactionDetails $subject): bool
    {
        if ($user->hasRole('ROLE_SELLER')) {
            return true;
        }

        if ($user->hasRole('ROLE_PARTICIPANT') && $subject->getCustomerId() && (string) $subject->getCustomerId() == $user->getId()) {
            return true;
        }

        return false;
    }

    /**
     * @param User        $user
     * @param Transaction $subject
     *
     * @return bool
     */
    protected function canSellerOrCustomerAssign(User $user, Transaction $subject): bool
    {
        if ($user->hasRole('ROLE_SELLER')) {
            return true;
        }

        if ($user->hasRole('ROLE_PARTICIPANT')) {
            return true;
        }

        return false;
    }

    /**
     * @param User               $user
     * @param TransactionDetails $subject
     *
     * @return bool
     */
    protected function canAppendLabels(User $user, TransactionDetails $subject): bool
    {
        if (!$user->hasRole('ROLE_PARTICIPANT')) {
            return false;
        }

        if ($subject->getCustomerId() && (string) $subject->getCustomerId() === $user->getId()) {
            return true;
        }

        return false;
    }
}
