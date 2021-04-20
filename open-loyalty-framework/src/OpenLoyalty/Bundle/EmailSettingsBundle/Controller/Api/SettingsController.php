<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EmailSettingsBundle\Controller\Api;

use Broadway\CommandHandling\CommandBus;
use FOS\RestBundle\View\View;
use OpenLoyalty\Bundle\EmailSettingsBundle\Form\Type\EmailFormType;
use OpenLoyalty\Bundle\EmailSettingsBundle\Service\EmailSettingsInterface;
use OpenLoyalty\Component\Email\Domain\Command\UpdateEmail;
use OpenLoyalty\Component\Email\Domain\Email;
use OpenLoyalty\Component\Email\Domain\EmailId;
use OpenLoyalty\Component\Email\Domain\ReadModel\DoctrineEmailRepositoryInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SettingsController.
 */
class SettingsController extends FOSRestController
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var DoctrineEmailRepositoryInterface
     */
    private $repository;

    /**
     * @var EmailSettingsInterface
     */
    private $settings;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * SettingsController constructor.
     *
     * @param FormFactory                      $formFactory
     * @param DoctrineEmailRepositoryInterface $repository
     * @param EmailSettingsInterface           $settings
     * @param CommandBus                       $commandBus
     */
    public function __construct(
        FormFactory $formFactory,
        DoctrineEmailRepositoryInterface $repository,
        EmailSettingsInterface $settings,
        CommandBus $commandBus
    ) {
        $this->formFactory = $formFactory;
        $this->repository = $repository;
        $this->settings = $settings;
        $this->commandBus = $commandBus;
    }

    /**
     * Method will return complete list of available email settings.
     *
     * @Route(name="oloy.email_settings.list", path="/settings/emails")
     * @Method("GET")
     * @Security("is_granted('VIEW_SETTINGS')")
     *
     * @ApiDoc(
     *     name="System e-mail list",
     *     section="Settings"
     * )
     *
     * @return View
     */
    public function getListAction(): View
    {
        $emails = $this->repository->getAll();

        return $this->view(
            [
                'emails' => $emails,
                'total' => count($emails),
            ],
            200
        );
    }

    /**
     * Method will return details of particular email setting.
     *
     * @Route(name="oloy.email_settings.get", path="/settings/emails/{emailId}")
     * @Method("GET")
     * @Security("is_granted('VIEW_SETTINGS')")
     *
     * @ApiDoc(
     *     name="Get single system e-mail",
     *     section="Settings",
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when emailId not provided or there is no object with such id"
     *     }
     * )
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    public function getAction(Request $request): View
    {
        try {
            $emailEntity = $this->repository->getById(new EmailId($request->get('emailId')));
        } catch (\Exception $e) {
            return $this->view(null, Response::HTTP_BAD_REQUEST);
        }

        return $this->view(
            [
                'entity' => $emailEntity,
                'additional' => $this->settings->getAdditionalParams($emailEntity),
            ]
        );
    }

    /**
     * @Route(name="oloy.email_settings.update", path="/settings/emails/{email}")
     * @Method("PUT")
     * @Security("is_granted('EDIT_SETTINGS')")
     *
     * @ApiDoc(
     *     name="Update single system e-mail",
     *     section="Settings",
     *     input={"class" = "OpenLoyalty\Bundle\EmailSettingsBundle\Form\Type\EmailFormType", "name" = "email"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors"
     *     }
     * )
     *
     * @param Request $request
     * @param Email   $email
     *
     * @return View
     *
     * @throws \Exception
     */
    public function updateAction(Request $request, Email $email): View
    {
        $form = $this->formFactory->createNamed('email', EmailFormType::class, $email, ['method' => 'PUT']);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->commandBus->dispatch(UpdateEmail::withEmailEntity($email->getEmailId(), $data));

            return $this->view($email->getEmailId());
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }
}
