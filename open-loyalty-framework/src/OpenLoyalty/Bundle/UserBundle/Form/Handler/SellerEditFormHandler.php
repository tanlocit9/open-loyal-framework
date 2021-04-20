<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Form\Handler;

use Broadway\CommandHandling\CommandBus;
use Doctrine\ORM\EntityManager;
use OpenLoyalty\Bundle\UserBundle\Service\UserManager;
use OpenLoyalty\Component\Seller\Domain\Command\UpdateSeller;
use OpenLoyalty\Component\Seller\Domain\Exception\EmailAlreadyExistsException;
use OpenLoyalty\Component\Seller\Domain\SellerId;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SellerEditFormHandler.
 */
class SellerEditFormHandler
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
     * SellerEditFormHandler constructor.
     *
     * @param CommandBus    $commandBus
     * @param UserManager   $userManager
     * @param EntityManager $em
     */
    public function __construct(CommandBus $commandBus, UserManager $userManager, EntityManager $em, TranslatorInterface $translator)
    {
        $this->commandBus = $commandBus;
        $this->userManager = $userManager;
        $this->em = $em;
        $this->translator = $translator;
    }

    public function onSuccess(SellerId $sellerId, FormInterface $form)
    {
        $sellerData = $form->getData();

        if (!empty($sellerData['email'])) {
            $email = strtolower($sellerData['email']);

            if ($this->isUserExistAndIsDifferentThanEdited($sellerId->__toString(), $email)) {
                $form->get('email')->addError(new FormError($this->translator->trans('This email is already taken')));

                return false;
            }
        }

        $command = new UpdateSeller($sellerId, $sellerData);

        try {
            $this->commandBus->dispatch($command);
        } catch (EmailAlreadyExistsException $e) {
            $form->get('email')->addError(new FormError($this->translator->trans($e->getMessage())));

            return false;
        }

        $user = $this->em->getRepository('OpenLoyaltyUserBundle:Seller')->find($sellerId->__toString());
        if (!empty($email)) {
            $user->setEmail($email);
        }
        if (!empty($sellerData['plainPassword'])) {
            $user->setPlainPassword($sellerData['plainPassword']);
        }
        if (array_key_exists('allowPointTransfer', $sellerData)) {
            $user->setAllowPointTransfer($sellerData['allowPointTransfer']);
        }
        $this->userManager->updateUser($user);

        return true;
    }

    private function isUserExistAndIsDifferentThanEdited($id, $email)
    {
        $qb = $this->em->createQueryBuilder()->select('u')->from('OpenLoyaltyUserBundle:Seller', 'u');
        $qb->andWhere('u.email = :email')->setParameter('email', $email);
        $qb->andWhere('u.id != :id')->setParameter('id', $id);

        $result = $qb->getQuery()->getResult();

        return count($result) > 0;
    }
}
