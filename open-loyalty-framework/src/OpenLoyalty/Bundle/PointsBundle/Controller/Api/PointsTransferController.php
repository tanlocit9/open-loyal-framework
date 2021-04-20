<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Controller\Api;

use Broadway\CommandHandling\CommandBus;
use Broadway\ReadModel\Repository;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View as FosView;
use FOS\RestBundle\View\ViewHandlerInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\ImportBundle\Form\Type\ImportFileFormType;
use OpenLoyalty\Bundle\ImportBundle\Service\ImportFileManager;
use OpenLoyalty\Bundle\PaginationBundle\Service\Paginator;
use OpenLoyalty\Bundle\PointsBundle\Form\Handler\TransferPointsFormHandler;
use OpenLoyalty\Bundle\PointsBundle\Form\Type\AddPointsFormType;
use OpenLoyalty\Bundle\PointsBundle\Form\Type\SpendPointsFormType;
use OpenLoyalty\Bundle\PointsBundle\Form\Type\TransferPointsFormType;
use OpenLoyalty\Bundle\PointsBundle\Import\PointsTransferXmlImporter;
use OpenLoyalty\Bundle\PointsBundle\Service\PointsTransfersManager;
use OpenLoyalty\Bundle\UserBundle\Service\EsParamManager;
use OpenLoyalty\Bundle\UserBundle\Service\MasterAdminProvider;
use OpenLoyalty\Component\Account\Domain\Command\AddPoints;
use OpenLoyalty\Component\Account\Domain\Command\CancelPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Command\SpendPoints;
use OpenLoyalty\Component\Account\Domain\Exception\PointsTransferCannotBeCanceledException;
use OpenLoyalty\Component\Account\Domain\Exception\NotEnoughPointsException;
use OpenLoyalty\Component\Account\Domain\Model\PointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\SpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetails;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetailsRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PointsTransferController.
 */
class PointsTransferController extends FOSRestController
{
    /**
     * @var ParamFetcher
     */
    private $paramFetcher;

    /**
     * @var EsParamManager
     */
    private $paramManager;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @var PointsTransferDetailsRepository
     */
    private $pointsTransferDetailsRepository;

    /**
     * @var ViewHandlerInterface
     */
    private $viewHandler;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PointsTransferXmlImporter
     */
    private $importer;

    /**
     * @var ImportFileManager
     */
    private $importFileManager;

    /**
     * @var TransferPointsFormHandler
     */
    private $transferPointsFormHandler;

    /**
     * PointsTransferController constructor.
     *
     * @param ParamFetcher                    $paramFetcher
     * @param EsParamManager                  $paramManager
     * @param Paginator                       $paginator
     * @param PointsTransferDetailsRepository $pointsTransferDetailsRepository
     * @param ViewHandlerInterface            $viewHandler
     * @param FormFactoryInterface            $formFactory
     * @param CommandBus                      $commandBus
     * @param UuidGeneratorInterface          $uuidGenerator
     * @param TranslatorInterface             $translator
     * @param PointsTransferXmlImporter       $importer
     * @param ImportFileManager               $importFileManager
     * @param TransferPointsFormHandler       $transferPointsFormHandler
     */
    public function __construct(
        ParamFetcher $paramFetcher,
        EsParamManager $paramManager,
        Paginator $paginator,
        PointsTransferDetailsRepository $pointsTransferDetailsRepository,
        ViewHandlerInterface $viewHandler,
        FormFactoryInterface $formFactory,
        CommandBus $commandBus,
        UuidGeneratorInterface $uuidGenerator,
        TranslatorInterface $translator,
        PointsTransferXmlImporter $importer,
        ImportFileManager $importFileManager,
        TransferPointsFormHandler $transferPointsFormHandler
    ) {
        $this->paramFetcher = $paramFetcher;
        $this->paramManager = $paramManager;
        $this->paginator = $paginator;
        $this->pointsTransferDetailsRepository = $pointsTransferDetailsRepository;
        $this->viewHandler = $viewHandler;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;
        $this->translator = $translator;
        $this->importer = $importer;
        $this->importFileManager = $importFileManager;
        $this->transferPointsFormHandler = $transferPointsFormHandler;
    }

    /**
     * List of all points transfers.
     *
     * @Route(name="oloy.points.transfer.list", path="/points/transfer")
     * @Route(name="oloy.points.transfer.seller.list", path="/seller/points/transfer")
     * @Method("GET")
     * @Security("is_granted('LIST_POINTS_TRANSFERS')")
     *
     * @QueryParam(name="customerFirstName", requirements="[a-zA-Z]+", nullable=true, description="firstName"))
     * @QueryParam(name="customerLastName", requirements="[a-zA-Z]+", nullable=true, description="lastName"))
     * @QueryParam(name="customerPhone", requirements="[a-zA-Z0-9\-]+", nullable=true, description="phone"))
     * @QueryParam(name="customerEmail", nullable=true, description="email"))
     * @QueryParam(name="customerId", nullable=true, description="customerId"))
     * @QueryParam(name="state", map=true, requirements="(canceled|active|expired|pending)", nullable=true, description="state"))
     * @QueryParam(name="type", nullable=true, requirements="(adding|spending|p2p_adding|p2p_spending)", description="type"))
     * @QueryParam(name="willExpireTill", nullable=true, description="willExpireTill"))
     *
     * @ApiDoc(
     *     name="get points transfers list",
     *     section="Points transfers",
     *     parameters={
     *          {"name"="state[]", "dataType"="array", "required"=false, "description"="List of statuses to be filtered"},
     *          {"name"="page", "dataType"="integer", "required"=false, "description"="Page number"},
     *          {"name"="perPage", "dataType"="integer", "required"=false, "description"="Number of elements per page"},
     *          {"name"="sort", "dataType"="string", "required"=false, "description"="Field to sort by"},
     *          {"name"="direction", "dataType"="asc|desc", "required"=false, "description"="Sorting direction"},
     *     }
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request): Response
    {
        $listPointsTransferRequest = $this->paramManager->stripNulls($this->paramFetcher->all());

        $pagination = $this->paginator->handleFromRequest($request);

        $transfersList = $this->pointsTransferDetailsRepository->findByParametersPaginatedAndFiltered(
            $listPointsTransferRequest,
            $pagination
        );

        $transfersTotal = $this->pointsTransferDetailsRepository->countTotal($listPointsTransferRequest);

        return $this->viewHandler->handle(FosView::create(
            [
                'transfers' => $transfersList,
                'total' => $transfersTotal,
            ],
            Response::HTTP_OK
        ));
    }

    /**
     * Method allows to add points to customer.
     *
     * @param Request $request
     * @Route(name="oloy.points.transfer.add", path="/points/transfer/add")
     * @Route(name="oloy.pos.points.transfer.add", path="/pos/points/transfer/add")
     * @Method("POST")
     * @Security("is_granted('ADD_POINTS')")
     * @ApiDoc(
     *     name="Add points",
     *     section="Points transfers",
     *     input={"class" = "OpenLoyalty\Bundle\PointsBundle\Form\Type\AddPointsFormType", "name" = "transfer"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *       404="Returned whend there is no account attached to customer"
     *     }
     * )
     *
     * @return FosView
     *
     * @throws \Exception
     */
    public function addPointsAction(Request $request): FosView
    {
        $form = $this->formFactory->createNamed('transfer', AddPointsFormType::class);
        $currentUser = $this->getUser();

        $manager = $this->get(PointsTransfersManager::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            /** @var Repository $accountDetailsRepo */
            $accountDetailsRepo = $this->get('oloy.points.account.repository.account_details');
            $accounts = $accountDetailsRepo->findBy(['customerId' => $data['customer']]);

            /** @var AccountDetails $account */
            $account = reset($accounts);
            if (!$account instanceof AccountDetails) {
                throw new NotFoundHttpException();
            }

            $pointsTransferId = new PointsTransferId($this->uuidGenerator->generate());
            $command = new AddPoints(
                $account->getAccountId(),
                $manager->createAddPointsTransferInstance(
                    $pointsTransferId,
                    $data['points'],
                    null,
                    false,
                    null,
                    $data['comment'],
                    ($currentUser->getId() == MasterAdminProvider::INTERNAL_ID)
                        ? PointsTransfer::ISSUER_API
                        : (in_array('ROLE_SELLER', $currentUser->getRoles())
                        ? PointsTransfer::ISSUER_SELLER
                        : PointsTransfer::ISSUER_ADMIN)
                )
            );

            $this->commandBus->dispatch($command);

            return $this->view($pointsTransferId);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method allows to spend customer points.
     *
     * @param Request $request
     *
     * @return FosView
     *
     * @Route(name="oloy.points.transfer.spend", path="/points/transfer/spend")
     * @Route(name="oloy.pos.points.transfer.spend", path="/pos/points/transfer/spend")
     * @Method("POST")
     * @Security("is_granted('SPEND_POINTS')")
     * @ApiDoc(
     *     name="Add points",
     *     section="Points transfers",
     *     input={"class" = "OpenLoyalty\Bundle\PointsBundle\Form\Type\AddPointsFormType", "name" = "transfer"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *       404="Returned when there is no account attached to customer"
     *     }
     * )
     */
    public function spendPointsAction(Request $request): FosView
    {
        $form = $this->formFactory->createNamed('transfer', SpendPointsFormType::class);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            /** @var Repository $accountDetailsRepo */
            $accountDetailsRepo = $this->get('oloy.points.account.repository.account_details');
            $accounts = $accountDetailsRepo->findBy(['customerId' => $data['customer']]);
            if (count($accounts) == 0) {
                throw new NotFoundHttpException();
            }

            /** @var AccountDetails $account */
            $account = reset($accounts);
            if (!$account instanceof AccountDetails) {
                throw $this->createNotFoundException();
            }

            $pointsTransferId = new PointsTransferId($this->uuidGenerator->generate());
            $command = new SpendPoints(
                $account->getAccountId(),
                new SpendPointsTransfer(
                    $pointsTransferId,
                    $data['points'],
                    null,
                    false,
                    $data['comment'],
                    PointsTransfer::ISSUER_ADMIN
                )
            );
            try {
                $this->commandBus->dispatch($command);
            } catch (NotEnoughPointsException $e) {
                $form->get('points')->addError(new FormError(
                    $this->translator->trans($e->getMessageKey(), $e->getMessageParams())
                ));

                return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
            }

            return $this->view($pointsTransferId);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method allows to transfer points between customers.
     *
     * @param Request $request
     *
     * @return FosView
     *
     * @Route(name="oloy.points.transfer.p2p-transfer", path="/admin/p2p-points-transfer")
     * @Method("POST")
     * @Security("is_granted('TRANSFER_POINTS')")
     * @ApiDoc(
     *     name="Transfer points",
     *     section="Points transfers",
     *     input={"class" = "OpenLoyalty\Bundle\PointsBundle\Form\Type\TransferPointsFormType", "name" = "transfer"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *       404="Returned when there is no account attached to customer"
     *     }
     * )
     */
    public function transferPointsAction(Request $request): FosView
    {
        $form = $this->formFactory->createNamed('transfer', TransferPointsFormType::class);

        return $this->transferPointsFormHandler->handle($request, $this->getUser(), $form);
    }

    /**
     * Cancel specific points transfer.
     *
     * @Route(name="oloy.points.transfer.cancel", path="/points/transfer/{transfer}/cancel")
     * @Security("is_granted('CANCEL', transfer)")
     * @Method("POST")
     *
     * @ApiDoc(
     *     name="Cancel transfer",
     *     section="Points transfers",
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when points transfer cannot be canceled",
     *       404="Returned when points transfer does not exist"
     *     }
     * )
     *
     * @param PointsTransferDetails $transfer
     *
     * @return FosView
     *
     * @throws \Exception
     */
    public function cancelTransferAction(PointsTransferDetails $transfer)
    {
        try {
            $this->commandBus->dispatch(
                new CancelPointsTransfer(
                    $transfer->getAccountId(),
                    $transfer->getPointsTransferId()
                )
            );
        } catch (PointsTransferCannotBeCanceledException $e) {
            return $this->view([
                'error' => $this->translator->trans('this transfer cannot be canceled'),
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->view([], 200);
    }

    /**
     * Import transfers points.
     *
     * @Route(name="oloy.points.transfer.import", path="/points/transfer/import")
     * @Method("POST")
     * @Security("is_granted('ADD_POINTS') or is_granted('SPEND_POINTS')")
     * @ApiDoc(
     *     name="Import points transfers",
     *     section="Points transfers",
     *     input={"class" = "OpenLoyalty\Bundle\ImportBundle\Form\Type\ImportFileFormType", "name" = "file"}
     * )
     *
     * @param Request $request
     *
     * @return FosView
     *
     * @throws \Exception
     */
    public function importAction(Request $request)
    {
        $form = $this->formFactory->createNamed('file', ImportFileFormType::class);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->getData()->getFile();
            $importFile = $this->importFileManager->upload($file, 'transfers');
            $result = $this->importer->import($this->importFileManager->getAbsolutePath($importFile));

            return $this->view($result, Response::HTTP_OK);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }
}
