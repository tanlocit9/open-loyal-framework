<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Form\Handler;

use Broadway\CommandHandling\CommandBus;
use Broadway\ReadModel\Repository;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use FOS\RestBundle\View\View;
use OpenLoyalty\Component\Account\Domain\Command\TransferPoints;
use OpenLoyalty\Component\Account\Domain\Exception\NotEnoughPointsException;
use OpenLoyalty\Component\Account\Domain\Model\P2PSpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TransferPointsFormHandler.
 */
class TransferPointsFormHandler
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * @var Repository
     */
    private $accountDetailsRepo;

    /**
     * TransferPointsFormHandler constructor.
     *
     * @param CommandBus             $commandBus
     * @param TranslatorInterface    $translator
     * @param UuidGeneratorInterface $uuidGenerator
     * @param Repository             $accountDetailsRepo
     */
    public function __construct(
        CommandBus $commandBus,
        TranslatorInterface $translator,
        UuidGeneratorInterface $uuidGenerator,
        Repository $accountDetailsRepo
    ) {
        $this->commandBus = $commandBus;
        $this->translator = $translator;
        $this->uuidGenerator = $uuidGenerator;
        $this->accountDetailsRepo = $accountDetailsRepo;
    }

    public function handle(Request $request, UserInterface $user, FormInterface $form): View
    {
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            if (!$form->has('sender')) {
                $senders = $this->accountDetailsRepo->findBy(['customerId' => (string) $user->getId()]);
            } else {
                $senders = $this->accountDetailsRepo->findBy(['customerId' => (string) $data['sender']->getId()]);
            }

            $receivers = $this->accountDetailsRepo->findBy(['customerId' => (string) $data['receiver']->getId()]);
            if (count($senders) == 0) {
                $form->get('receiver')->addError(new FormError($this->translator
                    ->trans('account.points_transfer.sender_does_not_exist')));

                return View::create($form->getErrors(), Response::HTTP_BAD_REQUEST);
            }
            if (count($receivers) == 0) {
                $form->get('receiver')->addError(new FormError($this->translator
                    ->trans('account.points_transfer.receiver_does_not_exist')));

                return View::create($form->getErrors(), Response::HTTP_BAD_REQUEST);
            }

            /** @var AccountDetails $sender */
            $sender = reset($senders);
            /** @var AccountDetails $receiver */
            $receiver = reset($receivers);

            if (!$sender instanceof AccountDetails) {
                $form->get('receiver')->addError(new FormError($this->translator
                    ->trans('account.points_transfer.sender_does_not_exist')));

                return View::create($form->getErrors(), Response::HTTP_BAD_REQUEST);
            }
            if (!$receiver instanceof AccountDetails) {
                $form->get('receiver')->addError(new FormError($this->translator
                    ->trans('account.points_transfer.receiver_does_not_exist')));

                return View::create($form->getErrors(), Response::HTTP_BAD_REQUEST);
            }

            if ($sender->getAccountId()->__toString() === $receiver->getAccountId()->__toString()) {
                $form->get('receiver')->addError(new FormError($this->translator
                    ->trans('account.points_transfer.cannot_transfer_to_yourself')));

                return View::create($form->getErrors(), Response::HTTP_BAD_REQUEST);
            }

            $pointsTransferId = new PointsTransferId($this->uuidGenerator->generate());

            $command = new TransferPoints(
                $sender->getAccountId(),
                new P2PSpendPointsTransfer(
                    $receiver->getAccountId(),
                    $pointsTransferId,
                    $data['points'],
                    null,
                    false,
                    null,
                    P2PSpendPointsTransfer::ISSUER_API
                )
            );
            try {
                $this->commandBus->dispatch($command);
            } catch (NotEnoughPointsException $e) {
                $form->get('points')->addError(new FormError(
                    $this->translator->trans($e->getMessageKey(), $e->getMessageParams())
                ));

                return View::create($form->getErrors(), Response::HTTP_BAD_REQUEST);
            }

            return View::create($pointsTransferId);
        }

        return View::create($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }
}
