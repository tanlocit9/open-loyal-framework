<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View as FosView;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\PaginationBundle\Service\Paginator;
use OpenLoyalty\Bundle\PointsBundle\Form\Handler\TransferPointsFormHandler;
use OpenLoyalty\Bundle\PointsBundle\Form\Type\TransferPointsByCustomerFormType;
use OpenLoyalty\Bundle\UserBundle\Service\EsParamManager;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetailsRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CustomerPointsTransferController.
 *
 * @Security("is_granted('ROLE_PARTICIPANT')")
 */
class CustomerPointsTransferController extends FOSRestController
{
    /**
     * @var ParamFetcher
     */
    private $paramFetcher;

    /**
     * @var PointsTransferDetailsRepository
     */
    private $pointsTransferDetailsRepository;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var EsParamManager
     */
    private $paramManager;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @var TransferPointsFormHandler
     */
    private $transferPointsFormHandler;

    /**
     * CustomerPointsTransferController constructor.
     *
     * @param ParamFetcher                    $paramFetcher
     * @param PointsTransferDetailsRepository $pointsTransferDetailsRepository
     * @param FormFactoryInterface            $formFactory
     * @param EsParamManager                  $paramManager
     * @param Paginator                       $paginator
     * @param TransferPointsFormHandler       $transferPointsFormHandler
     */
    public function __construct(
        ParamFetcher $paramFetcher,
        PointsTransferDetailsRepository $pointsTransferDetailsRepository,
        FormFactoryInterface $formFactory,
        EsParamManager $paramManager,
        Paginator $paginator,
        TransferPointsFormHandler $transferPointsFormHandler
    ) {
        $this->paramFetcher = $paramFetcher;
        $this->pointsTransferDetailsRepository = $pointsTransferDetailsRepository;
        $this->formFactory = $formFactory;
        $this->paramManager = $paramManager;
        $this->paginator = $paginator;
        $this->transferPointsFormHandler = $transferPointsFormHandler;
    }

    /**
     * List of all logged in customer points transfer.
     *
     * @Route(name="oloy.points.transfer.customer.list", path="/customer/points/transfer")
     * @Method("GET")
     * @Security("is_granted('LIST_CUSTOMER_POINTS_TRANSFERS')")
     *
     * @QueryParam(name="state", nullable=true, requirements="[a-zA-Z0-9\-]+", description="state"))
     * @QueryParam(name="type", nullable=true, requirements="[a-zA-Z0-9\-]+", description="type"))
     *
     * @ApiDoc(
     *     name="get customer points transfers list",
     *     section="Customer Points transfers",
     *     parameters={
     *          {"name"="page", "dataType"="integer", "required"=false, "description"="Page number"},
     *          {"name"="perPage", "dataType"="integer", "required"=false, "description"="Number of elements per page"},
     *          {"name"="sort", "dataType"="string", "required"=false, "description"="Field to sort by"},
     *          {"name"="direction", "dataType"="asc|desc", "required"=false, "description"="Sorting direction"},
     *     }
     * )
     *
     * @param Request $request
     *
     * @return FosView
     */
    public function listAction(Request $request): FosView
    {
        $params = $this->paramManager->stripNulls($this->paramFetcher->all(), true, false);
        $params['customerId'] = $this->getUser()->getId();
        $pagination = $this->paginator->handleFromRequest($request, 'createdAt', 'DESC');

        $transfers = $this->pointsTransferDetailsRepository->findByParametersPaginated(
            $params,
            false,
            $pagination->getPage(),
            $pagination->getPerPage(),
            $pagination->getSort(),
            $pagination->getSortDirection()
        );

        $total = $this->pointsTransferDetailsRepository->countTotal($params, false);

        return $this->view(
            [
                'transfers' => $transfers,
                'total' => $total,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Method allows to transfer points between customers.
     *
     * @param Request $request
     *
     * @return FosView
     *
     * @Route(name="oloy.points.transfer.customer.p2p", path="/customer/points/p2p-transfer")
     * @Method("POST")
     * @Security("is_granted('TRANSFER_POINTS')")
     * @ApiDoc(
     *     name="Transfer points",
     *     section="Customer Points transfers",
     *     input={"class" = "OpenLoyalty\Bundle\PointsBundle\Form\Type\TransferPointsByCustomerFormType", "name" = "transfer"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *       404="Returned when there is no account attached to customer"
     *     }
     * )
     */
    public function transferPointsAction(Request $request): FosView
    {
        $form = $this->formFactory->createNamed('transfer', TransferPointsByCustomerFormType::class);

        return $this->transferPointsFormHandler->handle($request, $this->getUser(), $form);
    }
}
