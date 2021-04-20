<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Form\Handler;

use Broadway\CommandHandling\CommandBus;
use Doctrine\ORM\EntityManager;
use OpenLoyalty\Bundle\ActivationCodeBundle\Service\ActionTokenManager;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Service\UserManager;
use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerAddress;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerCompanyDetails;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerDetails;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerLoyaltyCardNumber;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Exception\EmailAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Exception\LoyaltyCardNumberAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Exception\PhoneAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Model\AccountActivationMethod;
use OpenLoyalty\Component\Customer\Domain\Validator\CustomerUniqueValidator;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CustomerEditFormHandler.
 */
class CustomerEditFormHandler
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CustomerUniqueValidator
     */
    private $customerUniqueValidator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ActionTokenManager
     */
    private $actionTokenManager;

    /**
     * CustomerEditFormHandler constructor.
     *
     * @param CommandBus              $commandBus
     * @param UserManager             $userManager
     * @param EntityManager           $entityManager
     * @param CustomerUniqueValidator $customerUniqueValidator
     * @param TranslatorInterface     $translator
     * @param ActionTokenManager      $actionTokenManager
     */
    public function __construct(
        CommandBus $commandBus,
        UserManager $userManager,
        EntityManager $entityManager,
        CustomerUniqueValidator $customerUniqueValidator,
        TranslatorInterface $translator,
        ActionTokenManager $actionTokenManager
    ) {
        $this->commandBus = $commandBus;
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->customerUniqueValidator = $customerUniqueValidator;
        $this->translator = $translator;
        $this->actionTokenManager = $actionTokenManager;
    }

    /**
     * @param CustomerId    $customerId
     * @param FormInterface $form
     *
     * @return bool
     */
    public function onSuccess(CustomerId $customerId, FormInterface $form): bool
    {
        $email = null;

        $customerData = $form->getData();

        // because of activation method, email address cannot be removed
        if ($this->actionTokenManager->getCurrentMethod() === AccountActivationMethod::METHOD_EMAIL
            && array_key_exists('email', $customerData)
            && empty($customerData['email'])) {
            $form->get('email')->addError(
                new FormError($this->translator->trans('customer.registration.invalid_email'))
            );
        }

        // because of activation method, phone number cannot be removed
        if ($this->actionTokenManager->getCurrentMethod() === AccountActivationMethod::METHOD_SMS
            && array_key_exists('phone', $customerData)
            && empty($customerData['phone'])) {
            $form->get('phone')->addError(
                new FormError($this->translator->trans('customer.registration.invalid_phone_number'))
            );
        }

        if (isset($customerData['email']) && !empty($customerData['email'])) {
            $email = strtolower($customerData['email']);

            try {
                if ($this->isDifferentUserExistsWithThisEmail((string) $customerId, $email)) {
                    throw new EmailAlreadyExistsException();
                }

                $this->customerUniqueValidator->validateEmailUnique($email, $customerId);
            } catch (EmailAlreadyExistsException $e) {
                $form->get('email')->addError(
                    new FormError($this->translator->trans($e->getMessageKey(), $e->getMessageParams()))
                );
            }
        }

        if (isset($customerData['phone']) && $customerData['phone']) {
            try {
                $this->customerUniqueValidator->validatePhoneUnique($customerData['phone'], $customerId);
            } catch (PhoneAlreadyExistsException $e) {
                $form->get('phone')->addError(
                    new FormError($this->translator->trans($e->getMessageKey(), $e->getMessageParams()))
                );
            }
        }

        if (array_key_exists('phone', $customerData) && null === $customerData['phone']) {
            $customerData['phone'] = '';
        }

        if (isset($customerData['loyaltyCardNumber'])) {
            try {
                $this->customerUniqueValidator->validateLoyaltyCardNumberUnique(
                    $customerData['loyaltyCardNumber'],
                    $customerId
                );
            } catch (LoyaltyCardNumberAlreadyExistsException $e) {
                $form->get('loyaltyCardNumber')->addError(
                    new FormError($this->translator->trans($e->getMessageKey(), $e->getMessageParams()))
                );
            }
        }

        if (isset($customerData['company'])
            && array_key_exists('name', $customerData['company'])
            && array_key_exists('nip', $customerData['company'])
            && empty($customerData['company']['name'])
            && empty($customerData['company']['nip'])
        ) {
            // user wants to delete the company details
            // alternatively, users may send company = [] by themselves.
            $customerData['company'] = [];
        }

        if (array_key_exists('labels', $customerData)) {
            try {
                $labelsData = $form->get('labels')->getData();

                $customerData['labels'] = array_map(function (Label $label): array {
                    return $label->serialize();
                }, $labelsData ?? []);
            } catch (\Throwable $e) {
                $form->get('labels')->addError(
                    new FormError($this->translator->trans(
                        'customer.profile_edit.invalid_value_type',
                        ['%field%' => 'labels', '%type%' => 'labels as a string']
                    ))
                );
            }
        }

        if ($form->getErrors(true)->count() > 0) {
            return false;
        }

        $this->commandBus->dispatch(new UpdateCustomerDetails($customerId, $customerData));

        if (isset($customerData['address'])) {
            $this->commandBus->dispatch(new UpdateCustomerAddress(
                $customerId,
                $customerData['address']
            ));
        }

        if (isset($customerData['company'])) {
            $this->commandBus->dispatch(new UpdateCustomerCompanyDetails(
                $customerId,
                $customerData['company']
            ));
        }

        if (array_key_exists('loyaltyCardNumber', $customerData)) {
            $this->commandBus->dispatch(new UpdateCustomerLoyaltyCardNumber(
                $customerId,
                $customerData['loyaltyCardNumber']
            ));
        }

        if (null === $email) {
            return true;
        }

        /** @var User $user */
        $user = $this->entityManager->getRepository('OpenLoyaltyUserBundle:Customer')->find((string) $customerId);
        $user->setEmail($email);

        $this->userManager->updateUser($user);

        return true;
    }

    /**
     * @param string $id
     * @param string $email
     *
     * @return bool
     */
    private function isDifferentUserExistsWithThisEmail(string $id, string $email): bool
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from('OpenLoyaltyUserBundle:Customer', 'u')
            ->andWhere('u.email = :email')->setParameter('email', $email)
            ->andWhere('u.id != :id')->setParameter('id', $id)
        ;

        $result = $queryBuilder->getQuery()->getResult();

        return count($result) > 0;
    }
}
