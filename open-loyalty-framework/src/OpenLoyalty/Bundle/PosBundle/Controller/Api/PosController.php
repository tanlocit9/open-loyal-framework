<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PosBundle\Controller\Api;

use Broadway\CommandHandling\CommandBus;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\PosBundle\Form\Type\CreatePosFormType;
use OpenLoyalty\Bundle\PosBundle\Form\Type\EditPosFormType;
use OpenLoyalty\Bundle\PosBundle\Model\Pos;
use OpenLoyalty\Component\Pos\Domain\Command\CreatePos;
use OpenLoyalty\Component\Pos\Domain\Command\UpdatePos;
use OpenLoyalty\Component\Pos\Domain\Pos as DomainPos;
use OpenLoyalty\Component\Pos\Domain\PosId;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PosController.
 */
class PosController extends FOSRestController
{
    /**
     * Method allows to create new POS.
     *
     * @Route(name="oloy.pos.create", path="/pos")
     * @Security("is_granted('CREATE_POS')")
     * @Method("POST")
     * @ApiDoc(
     *     name="Create new POS",
     *     section="POS",
     *     input={"class" = "OpenLoyalty\Bundle\PosBundle\Form\Type\CreatePosFormType", "name" = "pos"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *     }
     * )
     *
     * @param Request $request
     *
     * @return View
     */
    public function createAction(Request $request): View
    {
        $form = $this->get('form.factory')->createNamed('pos', CreatePosFormType::class);
        $uuidGenerator = $this->get('broadway.uuid.generator');

        /** @var CommandBus $commandBus */
        $commandBus = $this->get('broadway.command_handling.command_bus');

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var Pos $data */
            $data = $form->getData();
            $id = new PosId($uuidGenerator->generate());

            $commandBus->dispatch(
                new CreatePos($id, $data->toArray())
            );

            return $this->view(['posId' => $id->__toString()]);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method allows to update POS data.
     *
     * @Route(name="oloy.pos.update", path="/pos/{pos}")
     * @Method("PUT")
     * @Security("is_granted('EDIT', pos)")
     * @ApiDoc(
     *     name="Edit POS",
     *     section="POS",
     *     input={"class" = "OpenLoyalty\Bundle\PosBundle\Form\Type\EditPosFormType", "name" = "pos"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *       404="Returned when POS does not exits"
     *     }
     * )
     *
     * @param Request   $request
     * @param DomainPos $pos
     *
     * @return View
     */
    public function updateAction(Request $request, DomainPos $pos): View
    {
        $form = $this->get('form.factory')->createNamed('pos', EditPosFormType::class, null, [
            'method' => 'PUT',
        ]);

        /** @var CommandBus $commandBus */
        $commandBus = $this->get('broadway.command_handling.command_bus');

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var Pos $data */
            $data = $form->getData();

            $commandBus->dispatch(
                new UpdatePos($pos->getPosId(), $data->toArray())
            );

            return $this->view(['posId' => (string) $pos->getPosId()]);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method will return POS details.
     *
     * @Route(name="oloy.pos.get", path="/pos/{pos}")
     * @Route(name="oloy.pos.seller.get", path="/seller/pos/{pos}")
     * @Method("GET")
     * @Security("is_granted('VIEW', pos)")
     * @ApiDoc(
     *     name="get POS",
     *     section="POS"
     * )
     *
     * @param DomainPos $pos
     *
     * @return View
     */
    public function getAction(DomainPos $pos): View
    {
        return $this->view($pos);
    }

    /**
     * Method will return POS details. <br/>
     * You need to provide POS identifier.
     *
     * @Route(name="oloy.pos.get_by_identifier", path="/pos/identifier/{pos}")
     * @Method("GET")
     * @Security("is_granted('VIEW', pos)")
     * @ApiDoc(
     *     name="get POS by identifier",
     *     section="POS",
     *     requirements={{"name": "pos", "required"=true, "description"="POS identifier", "dataType"="string"}}
     * )
     * @ParamConverter(class="OpenLoyalty\Component\Pos\Domain\Pos", name="pos", options={"identifier":true})
     *
     * @param DomainPos $pos
     *
     * @return View
     */
    public function getByIdentifierAction(DomainPos $pos): View
    {
        return $this->view($pos);
    }

    /**
     * Method will return complete list of POS.
     *
     * @Route(name="oloy.pos.list", path="/pos")
     * @Route(name="oloy.pos.seller.list", path="/seller/pos")
     * @Method("GET")
     * @Security("is_granted('LIST_POS')")
     * @ApiDoc(
     *     name="get POS list",
     *     section="POS",
     *     parameters={
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
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getListAction(Request $request): View
    {
        $pagination = $this->get('oloy.pagination')->handleFromRequest($request);

        $posRepository = $this->get('oloy.pos.repository');
        $pos = $posRepository
            ->findAllPaginated(
                $pagination->getPage(),
                $pagination->getPerPage(),
                $pagination->getSort(),
                $pagination->getSortDirection()
            );
        $total = $posRepository->countTotal();

        return $this->view(
            [
                'pos' => $pos,
                'total' => $total,
            ],
            200
        );
    }
}
