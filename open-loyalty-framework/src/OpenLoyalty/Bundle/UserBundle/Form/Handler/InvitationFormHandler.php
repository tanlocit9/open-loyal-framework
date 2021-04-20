<?php

namespace OpenLoyalty\Bundle\UserBundle\Form\Handler;

use Broadway\CommandHandling\CommandBus;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Bundle\UserBundle\Service\NotificationService;
use OpenLoyalty\Component\Customer\Domain\Command\CreateInvitation;
use OpenLoyalty\Component\Customer\Domain\Invitation;
use OpenLoyalty\Component\Customer\Domain\InvitationId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetailsRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class InvitationFormHandler.
 */
class InvitationFormHandler
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var InvitationDetailsRepository
     */
    protected $invitationDetailsRepository;

    /**
     * @var CustomerDetailsRepository
     */
    protected $customerDetailsRepository;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * InvitationFormHandler constructor.
     *
     * @param CommandBus                  $commandBus
     * @param InvitationDetailsRepository $invitationDetailsRepository
     * @param CustomerDetailsRepository   $customerDetailsRepository
     * @param UuidGeneratorInterface      $uuidGenerator
     * @param NotificationService         $notificationService
     * @param TranslatorInterface         $translator
     */
    public function __construct(
        CommandBus $commandBus,
        InvitationDetailsRepository $invitationDetailsRepository,
        CustomerDetailsRepository $customerDetailsRepository,
        UuidGeneratorInterface $uuidGenerator,
        TranslatorInterface $translator,
        NotificationService $notificationService
    ) {
        $this->commandBus = $commandBus;
        $this->invitationDetailsRepository = $invitationDetailsRepository;
        $this->customerDetailsRepository = $customerDetailsRepository;
        $this->uuidGenerator = $uuidGenerator;
        $this->translator = $translator;
        $this->notificationService = $notificationService;
    }

    /**
     * @param CustomerDetails $currentCustomer
     * @param FormInterface   $form
     *
     * @return bool
     */
    public function onSuccess(CustomerDetails $currentCustomer, FormInterface $form): bool
    {
        // if customer exists or if there is an invitation from current customer to this email -> return
        $invitationFormType = $form->get('type')->getData();
        $invitationType = !empty($invitationFormType) ? $invitationFormType : Invitation::EMAIL_TYPE;

        if ($invitationType === Invitation::MOBILE_TYPE) {
            $recipient = $form->get('recipientPhone')->getData();
        } else {
            $recipient = $form->get('recipientEmail')->getData();
        }

        if (!$this->checkIfInvitationCanBeCreated($currentCustomer, $invitationType, $recipient)) {
            $form->addError(new FormError($this->translator->trans('Invitation exists')));

            return false;
        }

        $invitationId = new InvitationId($this->uuidGenerator->generate());
        $this->commandBus->dispatch(new CreateInvitation(
            $invitationId,
            $currentCustomer->getCustomerId(),
            $invitationType,
            $recipient
        ));

        $invitationDetails = $this->invitationDetailsRepository->find((string) $invitationId);

        if (!$invitationDetails instanceof InvitationDetails) {
            return false;
        }

        $this->notificationService->sendInvitation($invitationDetails);

        return true;
    }

    /**
     * @param CustomerDetails $currentCustomer
     * @param string          $type
     * @param string          $recipient
     *
     * @return bool
     */
    protected function checkIfInvitationCanBeCreated(CustomerDetails $currentCustomer, string $type, string $recipient): bool
    {
        // check if recipient exists.
        $fieldName = $type === Invitation::MOBILE_TYPE ? 'phone' : 'email';
        $customers = $this->customerDetailsRepository->findOneByCriteria(
            [
                $fieldName => strtolower($recipient),
            ],
            1
        );

        if (count($customers) > 0) {
            return false;
        }

        // check if invitation exists.
        $fieldName = $type === Invitation::MOBILE_TYPE ? 'recipientPhone' : 'recipientEmail';
        $invitations = $this->invitationDetailsRepository->findByParametersPaginated(
            [
                $fieldName => $recipient,
                'referrerId' => (string) $currentCustomer->getCustomerId(),
            ],
            true
        );

        if (count($invitations) > 0) {
            return false;
        }

        return true;
    }
}
