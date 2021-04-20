<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Form\Handler;

use Broadway\CommandHandling\CommandBus;
use Doctrine\ORM\EntityManager;
use OpenLoyalty\Bundle\UserBundle\Service\UserManager;
use OpenLoyalty\Component\Seller\Domain\Command\ActivateSeller;
use OpenLoyalty\Component\Seller\Domain\Command\RegisterSeller;
use OpenLoyalty\Component\Seller\Domain\Exception\EmailAlreadyExistsException;
use OpenLoyalty\Component\Seller\Domain\SellerId;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SellerRegistrationFormHandler.
 */
class SellerRegistrationFormHandler
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
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * SellerRegistrationFormHandler constructor.
     *
     * @param CommandBus          $commandBus
     * @param UserManager         $userManager
     * @param EntityManager       $em
     * @param TranslatorInterface $translator
     */
    public function __construct(
        CommandBus $commandBus,
        UserManager $userManager,
        EntityManager $em,
        TranslatorInterface $translator
    ) {
        $this->commandBus = $commandBus;
        $this->userManager = $userManager;
        $this->em = $em;
        $this->translator = $translator;
    }

    public function onSuccess(SellerId $sellerId, FormInterface $form)
    {
        $sellerData = $form->getData();
        $password = $sellerData['plainPassword'];
        $allowPointTransfer = (array_key_exists('allowPointTransfer', $sellerData))
            ? $sellerData['allowPointTransfer'] : false;
        unset($sellerData['plainPassword']);

        $command = new RegisterSeller($sellerId, $sellerData);

        $email = strtolower($sellerData['email']);

        if ($this->userManager->isSellerExist($email)) {
            $form->get('email')->addError(new FormError('This email is already taken'));

            return $form->getErrors();
        }
        try {
            $this->commandBus->dispatch($command);
        } catch (EmailAlreadyExistsException $e) {
            $form->get('email')->addError(new FormError($this->translator->trans($e->getMessage())));

            return $form->getErrors();
        }

        $activateSellerCommand = new ActivateSeller($sellerId);
        $this->commandBus->dispatch($activateSellerCommand);

        return $this->userManager->createNewSeller($sellerId, $email, $password, $allowPointTransfer);
    }
}
