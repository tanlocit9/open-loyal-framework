<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Controller\Api;

use Broadway\CommandHandling\CommandBus;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View as FosView;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\UserBundle\Form\Type\CampaignBoughtDeliveryStatusFormType;
use OpenLoyalty\Component\Customer\Domain\Command\ChangeDeliveryStatusCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CampaignBoughtController.
 */
class CampaignBoughtController extends FOSRestController
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
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * CampaignBoughtController constructor.
     *
     * @param CommandBus           $commandBus
     * @param TranslatorInterface  $translator
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(
        CommandBus $commandBus,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory
    ) {
        $this->commandBus = $commandBus;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
    }

    /**
     * Method will change delivery status of bought campaign.
     *
     * @Route(name="oloy.campaign_bought.deliver_status.change", path="/admin/customer/{customerId}/bought/coupon/{couponId}/changeDeliveryStatus")
     * @Security("is_granted('LIST_ALL_CAMPAIGNS')")
     * @Method("PUT")
     * @ApiDoc(
     *     name="Change campaign bought delivery status",
     *     section="Campaign",
     *     input={"class"="OpenLoyalty\Bundle\UserBundle\Form\Type\CampaignBoughtDeliveryStatusFormType", "name"="deliveryStatus"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when there are errors in form",
     *       404="Returned when campaign not found"
     *     }
     * )
     *
     * @param Request $request
     *
     * @View(serializerGroups={"admin", "Default"})
     *
     * @return FosView
     */
    public function changeDeliveryStatusAction(Request $request): FosView
    {
        $form = $this->formFactory->createNamed(
            'deliveryStatus',
            CampaignBoughtDeliveryStatusFormType::class,
            null,
            [
                'method' => 'PUT',
            ]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $couponId = $request->attributes->get('couponId');
            $customerId = $request->attributes->get('customerId');

            $command = new ChangeDeliveryStatusCommand($couponId, $customerId, $form->getData()['status']);
            $this->commandBus->dispatch($command);

            return $this->view(
                ['success' => $this->translator->trans('campaign.bought.deliver_status.changed')],
                Response::HTTP_OK
            );
        }

        return $this->view(['error' => $form->getErrors()], Response::HTTP_BAD_REQUEST);
    }
}
