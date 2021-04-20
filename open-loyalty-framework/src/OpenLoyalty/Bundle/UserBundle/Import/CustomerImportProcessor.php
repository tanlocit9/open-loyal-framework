<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Import;

use Broadway\CommandHandling\CommandBus;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Bundle\ActivationCodeBundle\Service\ActionTokenManager;
use OpenLoyalty\Bundle\UserBundle\Entity\Status;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Service\RegisterCustomerManager;
use OpenLoyalty\Component\Customer\Domain\Command\ActivateCustomer;
use OpenLoyalty\Component\Customer\Domain\Command\AssignPosToCustomer;
use OpenLoyalty\Component\Customer\Domain\Command\AssignSellerToCustomer;
use OpenLoyalty\Component\Customer\Domain\Command\MoveCustomerToLevel;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\LevelId;
use OpenLoyalty\Component\Customer\Domain\PosId;
use OpenLoyalty\Component\Customer\Domain\SellerId;
use OpenLoyalty\Component\Import\Infrastructure\ImporterProcessor;
use OpenLoyalty\Component\Import\Infrastructure\ProcessImportResult;

/**
 * Class CustomerImportProcessor.
 */
class CustomerImportProcessor implements ImporterProcessor
{
    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var ActionTokenManager
     */
    protected $actionTokenManager;

    /**
     * @var RegisterCustomerManager
     */
    protected $registerCustomerManager;

    /**
     * CustomerImportProcessor constructor.
     *
     * @param UuidGeneratorInterface  $uuidGenerator
     * @param CommandBus              $commandBus
     * @param ActionTokenManager      $actionTokenManager
     * @param RegisterCustomerManager $registerCustomerManager
     */
    public function __construct(UuidGeneratorInterface $uuidGenerator, CommandBus $commandBus, ActionTokenManager $actionTokenManager, RegisterCustomerManager $registerCustomerManager)
    {
        $this->uuidGenerator = $uuidGenerator;
        $this->commandBus = $commandBus;
        $this->actionTokenManager = $actionTokenManager;
        $this->registerCustomerManager = $registerCustomerManager;
    }

    /**
     * {@inheritdoc}
     */
    public function processItem($entity): ProcessImportResult
    {
        if (!is_array($entity)) {
            throw new \InvalidArgumentException('Entity object is not array');
        }

        $customerId = new CustomerId($this->uuidGenerator->generate());
        $user = $this->registerCustomerManager->register($customerId, $entity);

        if ($user instanceof User) {
            $user->setStatus(Status::typeNew());

            if (isset($entity['posId'])) {
                $this->commandBus->dispatch(
                    new AssignPosToCustomer($customerId, new PosId($entity['posId']))
                );
            }

            if (isset($entity['levelId'])) {
                $this->commandBus->dispatch(
                    new MoveCustomerToLevel($customerId, new LevelId($entity['levelId']), null, true)
                );
            }

            if (isset($entity['sellerId'])) {
                $this->commandBus->dispatch(
                    new AssignSellerToCustomer($customerId, new SellerId($entity['sellerId']))
                );
            }

            if ($entity['agreement2']) {
                $this->registerCustomerManager->dispatchNewsletterSubscriptionEvent($user, $customerId);
            }

            if ($entity['active']) {
                $this->commandBus->dispatch(
                    new ActivateCustomer($customerId)
                );
                $this->registerCustomerManager->activate($user);
            } elseif ($entity['sendActivationMail']) {
                $this->actionTokenManager->sendActivationMessage($user);
            }
        }

        return new ProcessImportResult($customerId->__toString());
    }
}
