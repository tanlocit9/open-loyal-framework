<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\CampaignBundle\Controller\Api;

use Assert\InvalidArgumentException as AssertInvalidArgumentException;
use Broadway\CommandHandling\CommandBus;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View as FosView;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\CampaignBundle\Form\Type\CampaignPhotoFormType;
use OpenLoyalty\Bundle\CampaignBundle\Service\CampaignPhotoContentGeneratorInterface;
use OpenLoyalty\Component\Campaign\Domain\Campaign as DomainCampaign;
use OpenLoyalty\Component\Campaign\Domain\Command\AddPhotoCommand;
use OpenLoyalty\Component\Campaign\Domain\Command\RemovePhotoCommand;
use OpenLoyalty\Component\Campaign\Domain\PhotoId;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CampaignPhotoController.
 */
class CampaignPhotoController extends FOSRestController
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var CampaignPhotoContentGeneratorInterface
     */
    private $photoService;

    /**
     * CampaignController constructor.
     *
     * @param CommandBus                             $commandBus
     * @param FormFactory                            $formFactory
     * @param CampaignPhotoContentGeneratorInterface $photoService
     */
    public function __construct(
        CommandBus $commandBus,
        FormFactory $formFactory,
        CampaignPhotoContentGeneratorInterface $photoService
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->photoService = $photoService;
    }

    /**
     * Get campaign photo.
     *
     * @Route(name="oloy.campaign.get_photo", path="/campaign/{campaign}/photo/{photoId}")
     * @Method("GET")
     * @ApiDoc(
     *     name="Get campaign photo",
     *     section="Campaign"
     * )
     *
     * @param Request $request
     *
     * @return Response
     * @View(serializerGroups={"admin", "Default"})
     */
    public function getPhotoAction(Request $request): Response
    {
        $photoId = $request->attributes->get('photoId');
        $campaignId = $request->attributes->get('campaign');
        try {
            $response = $this->photoService->getPhotoContent($campaignId, $photoId);
        } catch (\InvalidArgumentException $exception) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return $response;
    }

    /**
     * Add photo to campaign.
     *
     * @Route(name="oloy.campaign.add_photo", path="/campaign/{campaign}/photo")
     * @Method("POST")
     * @Security("is_granted('EDIT', campaign)")
     * @ApiDoc(
     *     name="Add photo to Campaign",
     *     section="Campaign",
     *     input={"class" = "OpenLoyalty\Bundle\CampaignBundle\Form\Type\CampaignPhotoFormType", "name" = "photo"}
     * )
     *
     * @param Request        $request
     * @param DomainCampaign $campaign
     *
     * @return FosView
     *
     * @View(serializerGroups={"admin", "Default"})
     */
    public function addPhotoAction(Request $request, DomainCampaign $campaign): FosView
    {
        $form = $this->formFactory->createNamed('photo', CampaignPhotoFormType::class);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $form->getData()['file'];
                $this->commandBus->dispatch(AddPhotoCommand::withData($file, $campaign->getCampaignId()));

                return $this->view([], Response::HTTP_OK);
            } catch (AssertInvalidArgumentException $exception) {
                return $this->view([], Response::HTTP_NOT_FOUND);
            }
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove photo from campaign.
     *
     * @Route(name="oloy.campaign.remove_photo", path="/campaign/{campaign}/photo/{photoId}")
     * @Method("DELETE")
     * @Security("is_granted('EDIT', campaign)")
     * @ApiDoc(
     *     name="Delete photo from Campaign",
     *     section="Campaign"
     * )
     *
     * @View(serializerGroups={"admin", "Default"})
     *
     * @param Request        $request
     * @param DomainCampaign $campaign
     *
     * @return FosView
     */
    public function removePhotoAction(Request $request, DomainCampaign $campaign): FosView
    {
        $photoId = $request->attributes->get('photoId');
        try {
            $command = RemovePhotoCommand::byCampaignIdAndPhotoId($campaign->getCampaignId(), new PhotoId($photoId));
            $this->commandBus->dispatch($command);
        } catch (AssertInvalidArgumentException | \InvalidArgumentException $exception) {
            return $this->view(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->view([], Response::HTTP_OK);
    }
}
