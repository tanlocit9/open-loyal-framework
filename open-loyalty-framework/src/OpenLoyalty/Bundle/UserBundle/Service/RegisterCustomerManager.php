<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Service;

use Broadway\CommandHandling\CommandBus;
use Broadway\ReadModel\Repository;
use Doctrine\ORM\EntityManager;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Entity\Status;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Component\Customer\Domain\Command\ActivateCustomer;
use OpenLoyalty\Component\Customer\Domain\Command\MoveCustomerToLevel;
use OpenLoyalty\Component\Customer\Domain\Command\NewsletterSubscription;
use OpenLoyalty\Component\Customer\Domain\Command\RegisterCustomer;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerAddress;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerCompanyDetails;
use OpenLoyalty\Component\Customer\Domain\Command\UpdateCustomerLoyaltyCardNumber;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Exception\EmailAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Exception\LoyaltyCardNumberAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Exception\PhoneAlreadyExistsException;
use OpenLoyalty\Component\Customer\Domain\Exception\StartLevelNotFoundException;
use OpenLoyalty\Component\Customer\Domain\LevelId;
use OpenLoyalty\Component\Customer\Domain\LevelIdProvider;
use OpenLoyalty\Component\Customer\Domain\Validator\CustomerUniqueValidator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class RegisterCustomerManager.
 */
class RegisterCustomerManager
{
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var CustomerUniqueValidator
     */
    protected $customerUniqueValidator;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var Repository
     */
    protected $customerRepository;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var LevelIdProvider
     */
    private $levelIdProvider;

    /**
     * RegisterCustomerManager constructor.
     *
     * @param UserManager             $userManager
     * @param CustomerUniqueValidator $customerUniqueValidator
     * @param CommandBus              $commandBus
     * @param Repository              $customerRepository
     * @param EntityManager           $entityManager
     * @param TranslatorInterface     $translator
     * @param LevelIdProvider         $levelIdProvider
     */
    public function __construct(
        UserManager $userManager,
        CustomerUniqueValidator $customerUniqueValidator,
        CommandBus $commandBus,
        Repository $customerRepository,
        EntityManager $entityManager,
        TranslatorInterface $translator,
        LevelIdProvider $levelIdProvider
    ) {
        $this->userManager = $userManager;
        $this->customerUniqueValidator = $customerUniqueValidator;
        $this->commandBus = $commandBus;
        $this->customerRepository = $customerRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->levelIdProvider = $levelIdProvider;
    }

    /**
     * @param CustomerId  $customerId
     * @param array       $customerData
     * @param null|string $plainPassword
     *
     * @throws EmailAlreadyExistsException
     * @throws LoyaltyCardNumberAlreadyExistsException
     * @throws PhoneAlreadyExistsException
     * @throws StartLevelNotFoundException
     *
     * @return Customer
     */
    public function register(CustomerId $customerId, array $customerData, ?string $plainPassword = null): Customer
    {
        $email = null;

        if (isset($customerData['email']) && !empty($customerData['email'])) {
            $email = strtolower($customerData['email']);
            if ($this->userManager->isCustomerExist($email)) {
                throw new EmailAlreadyExistsException();
            }
            $this->customerUniqueValidator->validateEmailUnique($email, $customerId);
        }

        if (isset($customerData['loyaltyCardNumber'])) {
            $this->customerUniqueValidator->validateLoyaltyCardNumberUnique(
                $customerData['loyaltyCardNumber'],
                $customerId
            );
        }
        if (isset($customerData['phone']) && $customerData['phone']) {
            $this->customerUniqueValidator->validatePhoneUnique($customerData['phone']);
        }

        if (!isset($customerData['levelId']) && !$this->levelIdProvider->findLevelIdByConditionValueWithTheBiggestReward(0)) {
            throw new StartLevelNotFoundException();
        }

        $this->commandBus->dispatch(new RegisterCustomer($customerId, $customerData));

        if (isset($customerData['address'])) {
            $this->commandBus->dispatch(new UpdateCustomerAddress($customerId, $customerData['address']));
        }

        if (isset($customerData['company']) && $customerData['company'] && $customerData['company']['name']
            && $customerData['company']['nip']) {
            $updateCompanyDataCommand = new UpdateCustomerCompanyDetails($customerId, $customerData['company']);
            $this->commandBus->dispatch($updateCompanyDataCommand);
        }

        if (isset($customerData['loyaltyCardNumber'])) {
            $this->commandBus->dispatch(new UpdateCustomerLoyaltyCardNumber(
                $customerId,
                $customerData['loyaltyCardNumber'])
            );
        }

        if (isset($customerData['level'])) {
            $this->commandBus->dispatch(
                new MoveCustomerToLevel($customerId, new LevelId($customerData['level']), null, true)
            );
        }

        return $this->userManager->createNewCustomer(
            $customerId,
            $email,
            $plainPassword,
            isset($customerData['phone']) ? $customerData['phone'] : null
        );
    }

    /**
     * @param Customer $user
     */
    public function activate(Customer $user): void
    {
        $user->setIsActive(true);
        $user->setStatus(Status::typeActiveNoCard());

        $this->commandBus->dispatch(
            new ActivateCustomer(new CustomerId($user->getId()))
        );

        $this->userManager->updateUser($user);

        $customerId = new CustomerId($user->getId());
        $customer = $this->customerRepository->find($user->getId());

        if ($customer->isAgreement2()) {
            $this->dispatchNewsletterSubscriptionEvent($user, $customerId);
        }
    }

    /**
     * @param User       $user
     * @param CustomerId $customerId
     */
    public function dispatchNewsletterSubscriptionEvent(User $user, CustomerId $customerId): void
    {
        if (!$user->getNewsletterUsedFlag()) {
            $user->setNewsletterUsedFlag(true);

            $this->entityManager->flush();

            $this->commandBus->dispatch(new NewsletterSubscription($customerId));
        }
    }
}
