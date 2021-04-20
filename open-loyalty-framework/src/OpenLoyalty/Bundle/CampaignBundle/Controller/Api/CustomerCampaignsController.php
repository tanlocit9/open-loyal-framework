<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Controller\Api;

use Broadway\CommandHandling\CommandBus;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View as FosView;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\CampaignBundle\Exception\CampaignLimitException;
use OpenLoyalty\Bundle\CampaignBundle\Exception\CampaignUsageChange\CampaignUsageChangeException;
use OpenLoyalty\Bundle\CampaignBundle\Exception\NoCouponsLeftException;
use OpenLoyalty\Bundle\CampaignBundle\Exception\NotAllowedException;
use OpenLoyalty\Bundle\CampaignBundle\Exception\NotEnoughPointsException;
use OpenLoyalty\Bundle\CampaignBundle\ResponseModel\CouponUsageResponse;
use OpenLoyalty\Bundle\CampaignBundle\Service\CampaignProvider;
use OpenLoyalty\Bundle\CampaignBundle\Service\CampaignValidator;
use OpenLoyalty\Bundle\CampaignBundle\Service\MultipleCampaignCouponUsageProvider;
use OpenLoyalty\Bundle\PaginationBundle\Service\Paginator;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Service\EmailProvider;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Command\BuyCampaign;
use OpenLoyalty\Component\Campaign\Domain\Coupon\CouponCodeProvider;
use OpenLoyalty\Component\Campaign\Domain\CustomerId;
use OpenLoyalty\Component\Campaign\Domain\LevelId;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Campaign\Domain\SegmentId;
use OpenLoyalty\Component\Campaign\Infrastructure\Persistence\Doctrine\Repository\DoctrineCampaignRepository;
use OpenLoyalty\Component\Customer\Domain\Command\ChangeCampaignUsage;
use OpenLoyalty\Component\Customer\Domain\Command\ChangeDeliveryStatusCommand;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Segment\Domain\ReadModel\SegmentedCustomers;
use OpenLoyalty\Component\Segment\Domain\ReadModel\SegmentedCustomersRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;

/**
 * Class CustomerCampaignsController.
 *
 * @Security("is_granted('ROLE_PARTICIPANT')")
 */
class CustomerCampaignsController extends FOSRestController
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
     * @var Paginator
     */
    private $paginator;

    /**
     * @var SegmentedCustomersRepository
     */
    private $segmentedCustomersRepository;

    /**
     * @var DoctrineCampaignRepository
     */
    private $campaignRepository;

    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    /**
     * @var CampaignValidator
     */
    private $campaignValidator;

    /**
     * @var CampaignProvider
     */
    private $campaignProvider;

    /**
     * @var EmailProvider
     */
    private $customerEmailProvider;

    /**
     * @var MultipleCampaignCouponUsageProvider
     */
    private $multipleCampaignCouponUsageProvider;

    /**
     * @var CouponCodeProvider
     */
    private $couponCodeProvider;

    /**
     * CustomerCampaignsController constructor.
     *
     * @param CommandBus                          $commandBus
     * @param TranslatorInterface                 $translator
     * @param Paginator                           $paginator
     * @param SegmentedCustomersRepository        $segmentedCustomersRepository
     * @param DoctrineCampaignRepository          $campaignRepository
     * @param CustomerDetailsRepository           $customerDetailsRepository
     * @param CampaignValidator                   $campaignValidator
     * @param CampaignProvider                    $campaignProvider
     * @param EmailProvider                       $customerEmailProvider
     * @param MultipleCampaignCouponUsageProvider $multipleCampaignCouponUsageProvider
     * @param CouponCodeProvider                  $codeProvider
     */
    public function __construct(
        CommandBus $commandBus,
        TranslatorInterface $translator,
        Paginator $paginator,
        SegmentedCustomersRepository $segmentedCustomersRepository,
        DoctrineCampaignRepository $campaignRepository,
        CustomerDetailsRepository $customerDetailsRepository,
        CampaignValidator $campaignValidator,
        CampaignProvider $campaignProvider,
        EmailProvider $customerEmailProvider,
        MultipleCampaignCouponUsageProvider $multipleCampaignCouponUsageProvider,
        CouponCodeProvider $codeProvider
    ) {
        $this->commandBus = $commandBus;
        $this->translator = $translator;
        $this->paginator = $paginator;
        $this->segmentedCustomersRepository = $segmentedCustomersRepository;
        $this->campaignRepository = $campaignRepository;
        $this->customerDetailsRepository = $customerDetailsRepository;
        $this->campaignValidator = $campaignValidator;
        $this->campaignProvider = $campaignProvider;
        $this->customerEmailProvider = $customerEmailProvider;
        $this->multipleCampaignCouponUsageProvider = $multipleCampaignCouponUsageProvider;
        $this->couponCodeProvider = $codeProvider;
    }

    /**
     * Get all campaigns available for logged in customer.
     *
     * @Route(name="oloy.campaign.customer.available", path="/customer/campaign/available")
     * @Method("GET")
     * @Security("is_granted('LIST_CAMPAIGNS_AVAILABLE_FOR_ME')")
     *
     * @ApiDoc(
     *     name="get customer available campaigns list",
     *     section="Customer Campaign",
     *     parameters={
     *          {"name"="isFeatured", "dataType"="boolean", "required"=false, "description"="Filter by featured tag"},
     *          {"name"="hasSegment", "dataType"="boolean", "required"=false, "description"="Whether campaign is offered exclusively to some segments"},
     *          {"name"="isPublic", "dataType"="boolean", "required"=false},
     *          {"name"="page", "dataType"="integer", "required"=false, "description"="Page number"},
     *          {"name"="perPage", "dataType"="integer", "required"=false, "description"="Number of elements per page"},
     *          {"name"="sort", "dataType"="string", "required"=false, "description"="Field to sort by"},
     *          {"name"="direction", "dataType"="asc|desc", "required"=false, "description"="Sorting direction"},
     *          {"name"="categoryId[]", "dataType"="string", "required"=false, "description"="Filter by categories"},
     *     }
     * )
     *
     * @View(serializerGroups={"customer", "Default"})
     *
     * @param Request $request
     *
     * @return FosView
     */
    public function availableCampaigns(Request $request): FosView
    {
        $pagination = $this->paginator->handleFromRequest($request);
        $customer = $this->getLoggedCustomer();
        $availablePoints = null;
        $categoryIds = $request->query->get('categoryId', []);

        $customerSegments = $this->segmentedCustomersRepository
            ->findBy(['customerId' => (string) $customer->getCustomerId()]);
        $segments = array_map(
            function (SegmentedCustomers $segmentedCustomers) {
                return new SegmentId((string) $segmentedCustomers->getSegmentId());
            },
            $customerSegments
        );

        try {
            $campaigns = $this->campaignRepository
                ->getVisibleCampaignsForLevelAndSegment(
                    $segments,
                    new LevelId((string) $customer->getLevelId()),
                    $categoryIds,
                    null,
                    null,
                    $pagination->getSort(),
                    $pagination->getSortDirection(),
                    [
                        'featured' => $request->query->get('isFeatured'),
                        'isPublic' => $request->query->get('isPublic'),
                    ]
                );
        } catch (ORMException $exception) {
            return $this->view($this->translator->trans($exception->getMessage()), Response::HTTP_BAD_REQUEST);
        }

        // filter by segment exclusiveness
        $mustHaveSegments = $request->query->get('hasSegment', null);
        if (null !== $mustHaveSegments) {
            $campaigns = array_filter($campaigns, function (Campaign $campaign) use ($mustHaveSegments) {
                return $mustHaveSegments ? $campaign->hasSegments() : !$campaign->hasSegments();
            });
        }

        // filter by usage left
        $campaigns = array_filter($campaigns, function (Campaign $campaign) use ($customer) {
            $usageLeft = $this->campaignProvider->getUsageLeft($campaign);
            $usageLeftForCustomer = $this->campaignProvider
                ->getUsageLeftForCustomer($campaign, new CustomerId($customer->getId()));

            return $usageLeft > 0 && $usageLeftForCustomer > 0;
        });

        $view = $this->view(
            [
                'campaigns' => array_slice(
                    $campaigns,
                    ($pagination->getPage() - 1) * $pagination->getPerPage(),
                    $pagination->getPerPage()
                ),
                'total' => count($campaigns),
            ],
            Response::HTTP_OK
        );

        $context = new Context();
        $context->setGroups(['Default']);
        $context->setAttribute('customerId', $customer->getId());

        $view->setContext($context);

        return $view;
    }

    /**
     * Get all campaigns bought by logged in customer.
     *
     * @Route(name="oloy.campaign.customer.bought", path="/customer/campaign/bought")
     * @Method("GET")
     * @Security("is_granted('LIST_CAMPAIGNS_BOUGHT_BY_ME')")
     *
     * @ApiDoc(
     *     name="get customer bough campaigns list",
     *     section="Customer Campaign",
     *     parameters={
     *          {"name"="includeDetails", "dataType"="boolean", "required"=false},
     *          {"name"="page", "dataType"="integer", "required"=false, "description"="Page number"},
     *          {"name"="perPage", "dataType"="integer", "required"=false, "description"="Number of elements per page"},
     *          {"name"="sort", "dataType"="string", "required"=false, "description"="Field to sort by"},
     *          {"name"="direction", "dataType"="asc|desc", "required"=false, "description"="Sorting direction"},
     *     }
     * )
     *
     * @QueryParam(name="deliveryStatus", requirements="(ordered|canceled|shipped|delivered)", nullable=true, description="Delivery status"))
     *
     * @View(serializerGroups={"customer", "Default"})
     *
     * @param Request $request
     *
     * @return FosView
     */
    public function boughtCampaigns(Request $request): FosView
    {
        $pagination = $this->paginator->handleFromRequest($request);
        $customer = $this->getLoggedCustomer();
        if (count($customer->getCampaignPurchases()) == 0) {
            return $this->view(
                [
                    'campaigns' => [],
                    'total' => 0,
                ],
                Response::HTTP_OK
            );
        }
        $campaigns = $this->customerDetailsRepository
            ->findPurchasesByCustomerIdPaginated(
                $customer->getCustomerId(),
                $pagination->getPage(),
                $pagination->getPerPage(),
                $pagination->getSort(),
                $pagination->getSortDirection(),
                false,
                $request->attributes->get('deliveryStatus', null)
            );

        if ($request->get('includeDetails', false)) {
            $campaigns = array_map(function (CampaignPurchase $campaignPurchase) {
                $campaignPurchase->setCampaign($this->campaignRepository->byId(new CampaignId((string) $campaignPurchase->getCampaignId())));

                return $campaignPurchase;
            }, $campaigns);
        }

        return $this->view(
            [
                'campaigns' => $campaigns,
                'total' => $this->customerDetailsRepository->countPurchasesByCustomerId($customer->getCustomerId()),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Buy campaign by logged in customer.
     *
     * @Route(name="oloy.campaign.customer.buy", path="/customer/campaign/{campaign}/buy")
     * @Method("POST")
     * @Security("is_granted('BUY', campaign)")
     *
     * @ApiDoc(
     *     name="buy campaign",
     *     section="Customer Campaign",
     *     statusCodes={
     *          200="Returned when successful",
     *          400="With error 'No coupons left' returned when campaign cannot be bought because of lack of coupons. With error 'Not enough points' returned when campaign cannot be bought because of not enough points on customer account. With empty error returned when campaign limits exceeded."
     *     }
     * )
     *
     * @View(serializerGroups={"customer", "Default"})
     *
     * @param Request  $request
     * @param Campaign $campaign
     *
     * @return FosView
     */
    public function buyCampaign(Request $request, Campaign $campaign): FosView
    {
        if (!$campaign->canBeBoughtManually()) {
            throw new BadRequestHttpException();
        }

        if (!$this->campaignValidator->isCampaignActive($campaign) || !$this->campaignValidator->isCampaignVisible($campaign)) {
            throw $this->createNotFoundException();
        }
        /** @var CustomerDetails $customer */
        $customer = $this->getLoggedCustomer();
        $quantity = 1;
        if (!$campaign->isPercentageDiscountCode() && !$campaign->isCashback()) {
            $quantity = $request->get('quantity', 1);
        }

        $coupons = [];
        try {
            $this->campaignValidator->validateCampaignLimits(
                $campaign,
                new CustomerId($customer->getId()),
                $quantity
            );
            $this->campaignValidator->checkIfCustomerStatusIsAllowed($customer->getStatus());
            $this->campaignValidator->checkIfCustomerHasEnoughPoints(
                $campaign,
                new CustomerId($customer->getId()),
                $quantity
            );

            for ($i = 0; $i < $quantity; ++$i) {
                $coupon = $this->couponCodeProvider->getCoupon($campaign);

                $this->commandBus->dispatch(
                    new BuyCampaign(
                        $campaign->getCampaignId(),
                        new CustomerId($customer->getId()),
                        $coupon
                    )
                );

                $this->customerEmailProvider->customerBoughtCampaign(
                    $customer,
                    $campaign,
                    $coupon
                );
                $coupons[] = $coupon;
            }

            return $this->view(['coupons' => $coupons]);
        } catch (CampaignLimitException | NoCouponsLeftException | NotEnoughPointsException | NotAllowedException $e) {
            return $this->view(['error' => $this->translator->trans($e->getMessage())], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Mark multiple coupons as used/unused by customer.
     *
     * @Route(name="oloy.campaign.customer.coupon_multiple_usage", path="/customer/campaign/coupons/mark_as_used")
     * @Method("POST")
     * @Security("is_granted('MARK_SELF_MULTIPLE_COUPONS_AS_USED')")
     *
     * @ApiDoc(
     *     name="mark multiple coupons as used",
     *     section="Customer Campaign",
     *     parameters={
     *          {"name"="coupons", "dataType"="array", "required"=true, "description"="List of coupons to mark as used"},
     *          {"name"="coupons[][used]", "dataType"="boolean", "required"=true, "description"="If coupon is used or not"},
     *          {"name"="coupons[][campaignId]", "dataType"="string", "required"=true, "description"="CampaignId value"},
     *          {"name"="coupons[][code]", "dataType"="string", "required"=true, "description"="Coupon code"},
     *          {"name"="coupons[][couponId]", "dataType"="string", "required"=true, "description"="Coupon ID"},
     *          {"name"="coupons[][transactionId]", "dataType"="string", "required"=false, "description"="Id of transaction in which this coupon was used"},
     *     },
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when data is invalid",
     *       404="Returned when customer or campaign not found"
     *     }
     * )
     *
     * @View(serializerGroups={"admin", "Default"})
     *
     * @param Request $request
     *
     * @return FosView
     */
    public function campaignCouponListUsage(Request $request): FosView
    {
        $coupons = $request->request->get('coupons', []);

        if (empty($coupons)) {
            throw new BadRequestHttpException($this->translator->trans('campaign.invalid_data'));
        }

        /** @var CustomerDetails $customer */
        $customer = $this->getLoggedCustomer();
        try {
            $commands = $this->multipleCampaignCouponUsageProvider->validateRequestForCustomer($coupons, $customer);
        } catch (NoCouponsLeftException $e) {
            return $this->view(['error' => $this->translator->trans($e->getMessage())], Response::HTTP_BAD_REQUEST);
        } catch (CampaignUsageChangeException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        $result = [];

        /** @var ChangeCampaignUsage $command */
        foreach ($commands as $command) {
            $this->commandBus->dispatch($command);

            if ($command->isUsed()) {
                // change coupon status to delivered
                $changeDeliveryStatusCommand = new ChangeDeliveryStatusCommand(
                    $command->getCoupon()->getId(),
                    $command->getCustomerId(),
                    CampaignBought::DELIVERY_STATUS_ORDERED
                );
                $this->commandBus->dispatch($changeDeliveryStatusCommand);
            }

            $result[] = new CouponUsageResponse(
                $command->getCoupon()->getCode(),
                $command->isUsed(),
                (string) $command->getCampaignId(),
                (string) $command->getCustomerId()
            );
        }

        return $this->view(['coupons' => $result]);
    }

    /**
     * @return CustomerDetails
     */
    protected function getLoggedCustomer(): CustomerDetails
    {
        /** @var User $user */
        $user = $this->getUser();
        $customer = $this->customerDetailsRepository->find($user->getId());
        if (!$customer instanceof CustomerDetails) {
            throw $this->createNotFoundException();
        }

        return $customer;
    }
}
