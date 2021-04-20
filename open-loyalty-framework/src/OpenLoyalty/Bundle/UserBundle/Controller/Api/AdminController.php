<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Controller\Api;

use Broadway\CommandHandling\SimpleCommandBus;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\UserBundle\CQRS\AdminId;
use OpenLoyalty\Bundle\UserBundle\CQRS\Command\AdminCommand;
use OpenLoyalty\Bundle\UserBundle\CQRS\Command\CreateAdmin;
use OpenLoyalty\Bundle\UserBundle\CQRS\Command\EditAdmin;
use OpenLoyalty\Bundle\UserBundle\CQRS\Command\SelfEditAdmin;
use OpenLoyalty\Bundle\UserBundle\Entity\Admin;
use OpenLoyalty\Bundle\UserBundle\Entity\Repository\AdminRepository;
use OpenLoyalty\Bundle\UserBundle\Exception\EmailAlreadyExistException;
use OpenLoyalty\Bundle\UserBundle\Form\Type\AdminFormType;
use OpenLoyalty\Bundle\UserBundle\Form\Type\AdminSelfEditFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminController.
 */
class AdminController extends FOSRestController
{
    /**
     * List all admins.
     *
     * @Route(name="oloy.user.list", path="/admin")
     * @Method("GET")
     * @Security("is_granted('LIST_ADMINS')")
     *
     * @ApiDoc(
     *     name="Admins list",
     *     section="Admin",
     *     parameters={
     *      {"name"="strict", "dataType"="boolean", "required"=false, "description"="Strict filtering"},
     *      {"name"="page", "dataType"="integer", "required"=false, "description"="Page number"},
     *      {"name"="perPage", "dataType"="integer", "required"=false, "description"="Number of elements per page"},
     *      {"name"="sort", "dataType"="string", "required"=false, "description"="Field to sort by"},
     *      {"name"="direction", "dataType"="asc|desc", "required"=false, "description"="Sorting direction"},
     *     }
     * )
     *
     * @param Request $request
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        $pagination = $this->get('oloy.pagination')->handleFromRequest($request, 'lastName', 'asc');

        /** @var AdminRepository $repo */
        $repo = $this->get('oloy.user.repository.admin');
        $users = $repo->findAllPaginated(
            $pagination->getPage(),
            $pagination->getPerPage(),
            $pagination->getSort(),
            $pagination->getSortDirection()
        );
        $total = $repo->countTotal();

        return $this->view([
            'users' => $users,
            'total' => $total,
        ], Response::HTTP_OK);
    }

    /**
     * Method allows to update admin data.
     *
     * @param Request    $request
     * @param Admin|null $admin
     *
     * @return View
     * @Route(name="oloy.user.edit_admin", path="/admin/data/{admin}")
     *
     * @Method("PUT")
     * @ApiDoc(
     *     name="Edit Admin",
     *     section="Admin",
     *     input={"class" = "OpenLoyalty\Bundle\UserBundle\Form\Type\AdminFormType", "name" = "admin"},
     *     requirements={{"name"="admin", "description"="admin id which you want to edit, if empty - currently logged in admin will be edited", "dataType"="string"}},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *     }
     * )
     */
    public function editAction(Request $request, Admin $admin = null)
    {
        if (!$admin) {
            $admin = $this->getUser();
        } else {
            $this->denyAccessUnlessGranted('EDIT', $admin);
        }

        if ($admin->getId() == $this->getUser()->getId()) {
            $type = AdminSelfEditFormType::class;
            $command = new SelfEditAdmin(new AdminId($admin->getId()));
        } else {
            $type = AdminFormType::class;
            $command = new EditAdmin(new AdminId($admin->getId()));
        }

        $form = $this->get('form.factory')->createNamed('admin', $type, $command, [
            'method' => 'PUT',
            'validation_groups' => function (FormInterface $form) {
                if ($form->has('external') && !$form->get('external')->getData()) {
                    return ['Default', 'internal'];
                } else {
                    return ['Default', 'external'];
                }
            },
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handleAdminCommand($command, $form);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method allows to create new admin.
     *
     * @param Request $request
     *
     * @return View
     * @Route(name="oloy.user.create_admin", path="/admin/data")
     *
     * @Method("POST")
     * @ApiDoc(
     *     name="Create Admin",
     *     section="Admin",
     *     input={"class" = "OpenLoyalty\Bundle\UserBundle\Form\Type\AdminFormType", "name" = "admin"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *     }
     * )
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted('CREATE_USER');

        $adminId = new AdminId($this->get('broadway.uuid.generator')->generate());
        $command = new CreateAdmin($adminId);

        $form = $this->get('form.factory')->createNamed('admin', AdminFormType::class, $command, [
            'method' => 'POST',
            'validation_groups' => function (FormInterface $form) {
                if (!$form->get('external')->getData()) {
                    return ['Default', 'internal'];
                } else {
                    return ['Default', 'external'];
                }
            },
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handleAdminCommand($command, $form);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method will return admin details.
     *
     * @param Admin|null $admin
     *
     * @return View
     * @Route(name="oloy.user.get_admin", path="/admin/data/{admin}")
     *
     * @Method("GET")
     * @ApiDoc(
     *     name="Get Admin",
     *     section="Admin",
     *     requirements={{"name"="admin", "description"="admin id which you want to view, if empty - currently logged in admin data will be returned", "dataType"="string"}},
     * )
     */
    public function getAction(Admin $admin = null)
    {
        /* @var Admin $user */
        if (!$admin) {
            $admin = $this->getUser();
        } else {
            $this->denyAccessUnlessGranted('VIEW', $admin);
        }

        return $this->view($admin, Response::HTTP_OK);
    }

    /**
     * @param AdminCommand  $command
     * @param FormInterface $form
     *
     * @return View
     *
     * @throws \Exception
     */
    protected function handleAdminCommand(AdminCommand $command, FormInterface $form)
    {
        /** @var SimpleCommandBus $commandBus */
        $commandBus = $this->get('broadway.command_handling.command_bus');
        $translator = $this->get('translator');
        try {
            $commandBus->dispatch($command);
        } catch (EmailAlreadyExistException $e) {
            $form->get('email')->addError(new FormError($translator->trans($e->getMessage())));

            return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
        } catch (\DomainException $e) {
            $form->addError(new FormError($translator->trans($e->getMessage())));

            return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
        }

        return $this->view($command->getAdminId(), Response::HTTP_OK);
    }
}
