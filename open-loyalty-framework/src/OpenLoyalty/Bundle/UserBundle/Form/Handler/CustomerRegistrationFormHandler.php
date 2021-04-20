<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Form\Handler;

use Broadway\CommandHandling\CommandBus;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Doctrine\ORM\EntityManager;
use OpenLoyalty\Bundle\ActivationCodeBundle\Service\ActionTokenManager;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Entity\Status;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Service\RegisterCustomerManager;
use OpenLoyalty\Bundle\UserBundle\Service\UserManager;
use OpenLoyalty\Component\Core\Domain\Model\Label;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Exception\EmailAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Exception\LoyaltyCardNumberAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Exception\PhoneAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Exception\StartLevelNotFoundException;
use OpenLoyalty\Component\Customer\Domain\Validator\CustomerUniqueValidator;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CustomerRegistrationFormHandler.
 */
class CustomerRegistrationFormHandler
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var CustomerUniqueValidator
     */
    protected $customerUniqueValidator;

    /**
     * @var ActionTokenManager
     */
    private $actionTokenManager;

    /**
     * @var RegisterCustomerManager
     */
    protected $registerCustomerManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * CustomerRegistrationFormHandler constructor.
     *
     * @param CommandBus              $commandBus
     * @param UserManager             $userManager
     * @param EntityManager           $em
     * @param UuidGeneratorInterface  $uuidGenerator
     * @param CustomerUniqueValidator $customerUniqueValidator
     * @param ActionTokenManager      $actionTokenManager
     * @param RegisterCustomerManager $registerCustomerManager
     * @param TranslatorInterface     $translator
     */
    public function __construct(
        CommandBus $commandBus,
        UserManager $userManager,
        EntityManager $em,
        UuidGeneratorInterface $uuidGenerator,
        CustomerUniqueValidator $customerUniqueValidator,
        ActionTokenManager $actionTokenManager,
        RegisterCustomerManager $registerCustomerManager,
        TranslatorInterface $translator
    ) {
        $this->commandBus = $commandBus;
        $this->userManager = $userManager;
        $this->em = $em;
        $this->uuidGenerator = $uuidGenerator;
        $this->customerUniqueValidator = $customerUniqueValidator;
        $this->actionTokenManager = $actionTokenManager;
        $this->registerCustomerManager = $registerCustomerManager;
        $this->translator = $translator;
    }

    /**
     * @param CustomerId    $customerId
     * @param FormInterface $form
     *
     * @return Customer
     */
    public function onSuccess(CustomerId $customerId, FormInterface $form)
    {
        $customerData = $form->getData();
        if (!$customerData['company']['name'] && !$customerData['company']['nip']) {
            unset($customerData['company']);
        }
        $password = null;
        if ($form->has('plainPassword')) {
            $password = $customerData['plainPassword'];
            unset($customerData['plainPassword']);
        }
        $labels = [];

        /** @var Label $label */
        foreach ($form->get('labels')->getData() as $label) {
            $labels[] = $label->serialize();
        }

        $customerData['labels'] = $labels;

        try {
            return $this->registerCustomerManager->register($customerId, $customerData, $password);
        } catch (EmailAlreadyExistsException $ex) {
            $form->get('email')->addError(
                new FormError($this->translator->trans($ex->getMessageKey(), $ex->getMessageParams()))
            );
        } catch (LoyaltyCardNumberAlreadyExistsException $ex) {
            $form->get('loyaltyCardNumber')->addError(
                new FormError($this->translator->trans($ex->getMessageKey(), $ex->getMessageParams()))
            );
        } catch (PhoneAlreadyExistsException $ex) {
            $form->get('phone')->addError(
                new FormError($this->translator->trans($ex->getMessageKey(), $ex->getMessageParams()))
            );
        } catch (StartLevelNotFoundException $ex) {
            $form->get('levelId')->addError(
                new FormError($this->translator->trans($ex->getMessageKey(), $ex->getMessageParams()))
            );
        }

        return;
    }

    /**
     * @param User   $user
     * @param string $referralCustomerEmail
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handleCustomerRegisteredByHimself(User $user, $referralCustomerEmail)
    {
        $user->setIsActive(false);
        if ($user instanceof Customer) {
            $user->setStatus(Status::typeNew());
            $user->setActionToken(substr(md5(uniqid(null, true)), 0, 20));
            $user->setReferralCustomerEmail($referralCustomerEmail);
            $this->actionTokenManager
                ->sendActivationMessage($user);
        }

        $this->em->flush();
    }
}
