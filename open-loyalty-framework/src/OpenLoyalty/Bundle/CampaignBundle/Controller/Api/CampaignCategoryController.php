<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Controller\Api;

use Assert\AssertionFailedException;
use Broadway\CommandHandling\CommandBus;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View as FosView;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\PaginationBundle\Service\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use OpenLoyalty\Bundle\CampaignBundle\Form\Type\CampaignCategoryFormType;
use OpenLoyalty\Bundle\CampaignBundle\Form\Type\EditCampaignCategoryFormType;
use OpenLoyalty\Bundle\CampaignBundle\Model\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategory as DomainCampaignCategory;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryId;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryRepository;
use OpenLoyalty\Component\Campaign\Domain\Command\ChangeCampaignCategoryState;
use OpenLoyalty\Component\Campaign\Domain\Command\CreateCampaignCategory;
use OpenLoyalty\Component\Campaign\Domain\Command\UpdateCampaignCategory;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CampaignCategoryController.
 */
class CampaignCategoryController extends FOSRestController
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
     * @var CampaignCategoryRepository
     */
    private $campaignCategoryRepository;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * CampaignController constructor.
     *
     * @param CommandBus                 $commandBus
     * @param TranslatorInterface        $translator
     * @param CampaignCategoryRepository $campaignCategoryRepository
     * @param Paginator                  $paginator
     * @param UuidGeneratorInterface     $uuidGenerator
     * @param FormFactory                $formFactory
     */
    public function __construct(
        CommandBus $commandBus,
        TranslatorInterface $translator,
        CampaignCategoryRepository $campaignCategoryRepository,
        Paginator $paginator,
        UuidGeneratorInterface $uuidGenerator,
        FormFactory $formFactory
    ) {
        $this->commandBus = $commandBus;
        $this->translator = $translator;
        $this->campaignCategoryRepository = $campaignCategoryRepository;
        $this->paginator = $paginator;
        $this->uuidGenerator = $uuidGenerator;
        $this->formFactory = $formFactory;
    }

    /**
     * Method will return category details.
     *
     * @Route(name="oloy.campaign.category.get", path="/campaignCategory/{campaignCategory}")
     * @Method("GET")
     * @Security("is_granted('VIEW', campaignCategory)")
     * @ApiDoc(
     *     name="get campaign category details",
     *     section="Campaign",
     *     statusCodes={
     *       200="Returned when successful",
     *       404="Returned when campaign category does not exist"
     *     }
     * )
     *
     * @param DomainCampaignCategory $campaignCategory
     * @View(serializerGroups={"admin", "Default"})
     *
     * @return FosView
     */
    public function getCampaignCategoryAction(DomainCampaignCategory $campaignCategory): FosView
    {
        return $this->view(
            $campaignCategory,
            Response::HTTP_OK
        );
    }

    /**
     * Method will return complete list of campaign categories.
     *
     * @Route(name="oloy.campaign.category.list", path="/campaignCategory")
     * @Security("is_granted('LIST_ALL_CAMPAIGN_CATEGORIES')")
     * @Method("GET")
     * @ApiDoc(
     *     name="get campaign category list",
     *     section="Campaign",
     *     parameters={
     *          {"name"="page", "dataType"="integer", "required"=false, "description"="Page number"},
     *          {"name"="perPage", "dataType"="integer", "required"=false, "description"="Number of elements per page"},
     *          {"name"="sort", "dataType"="string", "required"=false, "description"="Field to sort by"},
     *          {"name"="direction", "dataType"="asc|desc", "required"=false, "description"="Sorting direction"},
     *          {"name"="active", "dataType"="boolean", "required"=false, "description"="Filter by activity"},
     *          {"name"="name", "dataType"="string", "required"=false, "description"="Filter by campaign category name"},
     *     }
     * )
     *
     * @param Request      $request
     * @param ParamFetcher $paramFetcher
     *
     * @View(serializerGroups={"admin", "Default"})
     *
     * @QueryParam(name="active", nullable=true, description="filter by activity"))
     * @QueryParam(name="name", nullable=true, description="filter by name"))
     *
     * @return FosView
     */
    public function getCategoryListAction(Request $request, ParamFetcher $paramFetcher): FosView
    {
        $pagination = $this->paginator->handleFromRequest($request);
        $params = $paramFetcher->all();
        $params['_locale'] = $request->getLocale();

        $campaigns = $this->campaignCategoryRepository
            ->findByParametersPaginated(
                $params,
                $pagination->getPage(),
                $pagination->getPerPage(),
                $pagination->getSort(),
                $pagination->getSortDirection()
            );

        $total = $this->campaignCategoryRepository->countFindByParameters($params);

        $view = $this->view(
            [
                'categories' => $campaigns,
                'total' => $total,
            ],
            Response::HTTP_OK
        );

        return $view;
    }

    /**
     * Method allows to create new category.
     *
     * @param Request $request
     * @Route(name="oloy.campaign.category.create", path="/campaignCategory")
     * @Method("POST")
     * @Security("is_granted('CREATE_CAMPAIGN_CATEGORY')")
     * @ApiDoc(
     *     name="Create new Campaign Category",
     *     section="Campaign",
     *     input={"class" = "OpenLoyalty\Bundle\CampaignBundle\Form\Type\CampaignCategoryFormType", "name" =
     *     "campaign_category"}, statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors"
     *     }
     * )
     *
     * @return FosView
     */
    public function createCategoryAction(Request $request): FosView
    {
        $form = $this->formFactory->createNamed('campaign_category', CampaignCategoryFormType::class);

        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $campaignCategoryId = new CampaignCategoryId($this->uuidGenerator->generate());
            } catch (AssertionFailedException $ex) {
                return $this->view(['error' => $this->translator->trans('Invalid identifier')], Response::HTTP_BAD_REQUEST);
            }

            $campaignCategory = $form->getData();
            $command = new CreateCampaignCategory($campaignCategoryId, $campaignCategory->toArray());
            $this->commandBus->dispatch($command);

            return $this->view($campaignCategoryId);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Edit campaign category.
     *
     * @Route(name="oloy.campaign.category.edit", path="/campaignCategory/{campaignCategory}")
     * @Method("PUT")
     * @Security("is_granted('EDIT', campaignCategory)")
     * @ApiDoc(
     *     name="Edit campaign category",
     *     section="Campaign",
     *     input={"class" = "OpenLoyalty\Bundle\CampaignBundle\Form\Type\EditCampaignCategoryFormType", "name" =
     *     "campaign_category"}, statusCodes={
     *       200="Returned when successful",
     *       400="Returned when there are errors in form",
     *       404="Returned when campaign not found"
     *     }
     * )
     *
     * @param Request                $request
     * @param DomainCampaignCategory $campaignCategory
     * @View(serializerGroups={"admin", "Default"})
     *
     * @return FosView
     */
    public function editCategoryAction(Request $request, DomainCampaignCategory $campaignCategory): FosView
    {
        $form = $this->formFactory->createNamed('campaign_category', EditCampaignCategoryFormType::class, null, [
            'method' => 'PUT',
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var Campaign $data */
            $data = $form->getData();
            $this->commandBus->dispatch(
                new UpdateCampaignCategory($campaignCategory->getCampaignCategoryId(), $data->toArray())
            );

            return $this->view(['campaignCategoryId' => (string) $campaignCategory->getCampaignCategoryId()]);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method allows to activate or deactivate campaign category.
     *
     * @Route(name="oloy.campaign.category.change_state", path="/campaignCategory/{campaignCategory}/active")
     * @Method("POST")
     * @Security("is_granted('EDIT', campaignCategory)")
     *
     * @ApiDoc(
     *     name="Change Campaign Category state",
     *     section="Campaign",
     *     parameters={{"name"="active", "dataType"="boolean", "required"=true}},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when active parameter is not present",
     *       404="Returned when campaign category does not exist"
     *     }
     * )
     *
     * @param Request                $request
     * @param DomainCampaignCategory $campaignCategory
     *
     * @return FosView
     * @View(serializerGroups={"admin", "Default"})
     */
    public function changeCategoryStateAction(Request $request, DomainCampaignCategory $campaignCategory): FosView
    {
        $activate = $request->request->get('active', null);
        if (null === $activate) {
            return $this->view(
                ['active' => $this->translator->trans('this field is required')],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->commandBus->dispatch(
            new ChangeCampaignCategoryState($campaignCategory->getCampaignCategoryId(), $activate)
        );

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
