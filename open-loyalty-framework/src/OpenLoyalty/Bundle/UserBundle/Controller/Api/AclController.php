<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Bundle\UserBundle\Controller\Api;

use Broadway\CommandHandling\CommandBus;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\UserBundle\CQRS\Command\ChangeRole;
use OpenLoyalty\Bundle\UserBundle\CQRS\Command\CreateRole;
use OpenLoyalty\Bundle\UserBundle\CQRS\Command\DeleteRole;
use OpenLoyalty\Bundle\UserBundle\Entity\Role;
use OpenLoyalty\Bundle\UserBundle\Form\Type\RoleFormType;
use OpenLoyalty\Bundle\UserBundle\Service\AclManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AclController.
 *
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AclController extends FOSRestController
{
    /**
     * @var AclManagerInterface
     */
    private $aclManager;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * AclController constructor.
     *
     * @param AclManagerInterface  $aclManager
     * @param CommandBus           $commandBus
     * @param FormFactoryInterface $formFactory
     * @param TranslatorInterface  $translator
     */
    public function __construct(AclManagerInterface $aclManager, CommandBus $commandBus, FormFactoryInterface $formFactory, TranslatorInterface $translator)
    {
        $this->aclManager = $aclManager;
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
    }

    /**
     * Method will return list of roles.
     *
     * @Route(name="oloy.user.acl.role.list", path="/admin/acl/role")
     * @Method("GET")
     * @Security("is_granted('LIST_ROLES')")
     * @ApiDoc(
     *     name="Role list",
     *     section="Acl"
     * )
     *
     * @return View
     */
    public function listRoleAction(): View
    {
        $roles = $this->aclManager->getAdminRoles();

        return $this->view([
            'roles' => $roles,
            'total' => count($roles),
        ], Response::HTTP_OK);
    }

    /**
     * Method allows to create new role.
     *
     * @Route(name="oloy.user.role.create", path="/admin/acl/role")
     * @Security("is_granted('CREATE_ROLE')")
     * @Method("POST")
     * @ApiDoc(
     *     name="Create Role",
     *     section="Acl",
     *     input={"class" = "OpenLoyalty\Bundle\UserBundle\Form\Type\RoleFormType", "name" = "role"},
     *     statusCodes={
     *       204="Returned when successful",
     *       400="Returned when form contains errors",
     *     }
     * )
     *
     * @param Request $request
     *
     * @return View
     */
    public function createRole(Request $request): View
    {
        $form = $this->formFactory->createNamed('role', RoleFormType::class);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->commandBus->dispatch(new CreateRole(
                null,
                $form->get('name')->getData(),
                $form->get('permissions')->getData()
            ));

            return $this->view(null, Response::HTTP_NO_CONTENT);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method allows to modify role.
     *
     * @Route(name="oloy.user.role.edit", path="/admin/acl/role/{role}")
     * @Security("is_granted('EDIT', role)")
     * @Method("PUT")
     * @ApiDoc(
     *     name="Modify Role",
     *     section="Acl",
     *     input={"class" = "OpenLoyalty\Bundle\UserBundle\Form\Type\RoleFormType", "name" = "role"},
     *     statusCodes={
     *       204="Returned when successful",
     *       400="Returned when form contains errors",
     *     }
     * )
     *
     * @param Request $request
     * @param Role    $role
     *
     * @return View
     */
    public function modifyRole(Request $request, Role $role): View
    {
        $form = $this->formFactory->createNamed('role', RoleFormType::class, null, [
            'method' => 'PUT',
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->commandBus->dispatch(new ChangeRole(
                $role->getId(),
                $form->get('name')->getData(),
                $form->get('permissions')->getData()
            ));

            return $this->view(null, Response::HTTP_NO_CONTENT);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method will return role details.
     *
     * @Route(name="oloy.user.acl.role.get", path="/admin/acl/role/{role}")
     * @Method("GET")
     * @Security("is_granted('VIEW', role)")
     * @ApiDoc(
     *     name="Get role details",
     *     section="Acl",
     * )
     *
     * @param Role $role
     *
     * @return View
     */
    public function getRoleAction(Role $role): View
    {
        return $this->view(
            $role,
            Response::HTTP_OK
        );
    }

    /**
     * Method will delete role.
     *
     * @Route(name="oloy.user.acl.role.delete", path="/admin/acl/role/{role}")
     * @Method("DELETE")
     * @Security("is_granted('EDIT', role)")
     * @ApiDoc(
     *     name="Delete role details",
     *     section="Acl",
     * )
     *
     * @param Role $role
     *
     * @return View
     */
    public function getDeleteAction(Role $role): View
    {
        if ($role->isMaster()) {
            return $this->view(
                ['error' => $this->translator->trans('user.acl.can_not_delete_master_role')],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->commandBus->dispatch(new DeleteRole(
            $role->getId()
        ));

        return $this->view(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * Method will return available permission accesses.
     *
     * @Route(name="oloy.user.acl.access.list", path="/admin/acl/accesses")
     * @Method("GET")
     * @Security("is_granted('LIST_ROLES')")
     * @ApiDoc(
     *     name="Available permission access list",
     *     section="Acl"
     * )
     *
     * @return View
     */
    public function listAvailablePermissionAccessAction(): View
    {
        $accesses = $this->aclManager->getAvailableAccesses();

        return $this->view([
            'accesses' => $accesses,
            'total' => count($accesses),
        ], Response::HTTP_OK);
    }

    /**
     * Method will return available permission resources.
     *
     * @Route(name="oloy.user.acl.resource.list", path="/admin/acl/resources")
     * @Method("GET")
     * @Security("is_granted('LIST_ROLES')")
     * @ApiDoc(
     *     name="Available permission resource list",
     *     section="Acl"
     * )
     *
     * @return View
     */
    public function listAvailablePermissionResourcesAction(): View
    {
        $resources = $this->aclManager->getAvailableResources();

        return $this->view([
            'resources' => $resources,
            'total' => count($resources),
        ], Response::HTTP_OK);
    }
}
