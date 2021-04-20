<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\LevelBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\LevelBundle\Form\Type\LevelFormType;
use OpenLoyalty\Bundle\LevelBundle\Form\Type\LevelPhotoFormType;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomersBelongingToOneLevel;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomersBelongingToOneLevelRepository;
use OpenLoyalty\Component\Level\Domain\Command\ActivateLevel;
use OpenLoyalty\Component\Level\Domain\Command\CreateLevel;
use OpenLoyalty\Component\Level\Domain\Command\DeactivateLevel;
use OpenLoyalty\Component\Level\Domain\Command\RemoveLevelPhoto;
use OpenLoyalty\Component\Level\Domain\Command\SetLevelPhoto;
use OpenLoyalty\Component\Level\Domain\Command\UpdateLevel;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelId;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class LevelController.
 */
class LevelController extends FOSRestController
{
    /**
     * Method allows to create new level.
     *
     * @param Request $request
     * @Route(name="oloy.level.create", path="/level/create")
     * @Method("POST")
     * @Security("is_granted('CREATE_LEVEL')")
     * @ApiDoc(
     *     name="Create new Level",
     *     section="Level",
     *     input={"class" = "OpenLoyalty\Bundle\LevelBundle\Form\Type\LevelFormType", "name" = "level"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors"
     *     }
     * )
     *
     * @return \FOS\RestBundle\View\View
     */
    public function createLevelAction(Request $request)
    {
        $form = $this->get('form.factory')->createNamed('level', LevelFormType::class);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $levelId = new LevelId($this->get('broadway.uuid.generator')->generate());
            /** @var \OpenLoyalty\Bundle\LevelBundle\Model\Level $level */
            $level = $form->getData();
            $command = new CreateLevel($levelId, $level->toArray());
            $commandBus = $this->get('broadway.command_handling.command_bus');
            $commandBus->dispatch($command);

            if ($level->isActive()) {
                $commandBus->dispatch(new ActivateLevel($levelId));
            } else {
                $commandBus->dispatch(new DeactivateLevel($levelId));
            }

            return $this->view($levelId);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method allows to edit existing level.
     *
     * @param Request $request
     * @param Level   $level
     *
     * @return \FOS\RestBundle\View\View
     * @Route(name="oloy.level.update", path="/level/{level}")
     * @Method("PUT")
     * @Security("is_granted('EDIT', level)")
     * @ApiDoc(
     *     name="Update Level",
     *     section="Level",
     *     input={"class" = "OpenLoyalty\Bundle\LevelBundle\Form\Type\LevelFormType", "name" = "level"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors"
     *     }
     * )
     */
    public function updateLevelAction(Request $request, Level $level)
    {
        $form = $this->get('form.factory')->createNamed('level', LevelFormType::class, null, [
            'method' => 'PUT',
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $command = new UpdateLevel($level->getLevelId(), $data->toArray());
            $commandBus = $this->get('broadway.command_handling.command_bus');
            $commandBus->dispatch($command);

            if ($data->isActive() !== $level->isActive()) {
                if ($data->isActive()) {
                    $commandBus->dispatch(new ActivateLevel($level->getLevelId()));
                } else {
                    $commandBus->dispatch(new DeactivateLevel($level->getLevelId()));
                }
            }

            return $this->view($level->getLevelId());
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method will return level details.
     *
     * @Route(name="oloy.level.get", path="/level/{level}")
     * @Route(name="oloy.level.seller.get", path="/seller/level/{level}")
     * @Method("GET")
     * @Security("is_granted('VIEW', level)")
     *
     * @ApiDoc(
     *     name="get Level",
     *     section="Level",
     *     statusCodes={
     *       200="Returned when successful",
     *       404="Returned when level does not exist"
     *     }
     * )
     *
     * @param Level $level
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getLevelAction(Level $level)
    {
        return $this->view(
            $level,
            200
        );
    }

    /**
     * Method will return list of customers assigned to this level.
     *
     * @Route(name="oloy.level.get_customers", path="/level/{level}/customers")
     * @Method("GET")
     * @Security("is_granted('LIST_CUSTOMERS', level)")
     *
     * @ApiDoc(
     *     name="get Level customers",
     *     section="Level",
     * )
     *
     * @param Request $request
     * @param Level   $level
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getLevelCustomersAction(Request $request, Level $level)
    {
        $pagination = $this->get('oloy.pagination')->handleFromRequest($request);

        /** @var CustomersBelongingToOneLevelRepository $repo */
        $repo = $this->get('oloy.user.read_model.repository.customers_belonging_to_one_level');
        $levelId = new \OpenLoyalty\Component\Customer\Domain\LevelId($level->getLevelId()->__toString());

        /** @var CustomersBelongingToOneLevel $levelCustomers */
        $levelCustomers = $repo->findByLevelIdPaginated(
            $levelId,
            $pagination->getPage(),
            $pagination->getPerPage(),
            $pagination->getSort(),
            $pagination->getSortDirection()
        );

        if (!$levelCustomers) {
            return $this->view(['customers' => []]);
        }

        return $this->view(
            [
                'customers' => $levelCustomers->getCustomers(),
                'total' => $repo->countByLevelId($levelId),
            ],
            200
        );
    }

    /**
     * Method will return complete list od levels.
     *
     * @Route(name="oloy.level.list", path="/level")
     * @Route(name="oloy.level.seller.list", path="/seller/level")
     * @Method("GET")
     * @Security("is_granted('LIST_LEVELS')")
     *
     * @ApiDoc(
     *     name="get Level list",
     *     section="Level",
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
     * @return \FOS\RestBundle\View\View
     */
    public function getListAction(Request $request)
    {
        $pagination = $this->get('oloy.pagination')->handleFromRequest($request);

        $levelRepository = $this->get('oloy.level.repository');
        $levels = $levelRepository
            ->findAllPaginated(
                $pagination->getPage(),
                $pagination->getPerPage(),
                $pagination->getSort(),
                $pagination->getSortDirection()
            );
        $total = $levelRepository->countTotal();

        return $this->view(
            [
                'levels' => $levels,
                'total' => $total,
            ],
            200
        );
    }

    /**
     * Method allows to activate or deactivate level.
     *
     * @Route(name="oloy.level.activate", path="/level/{level}/activate")
     * @Method("POST")
     * @Security("is_granted('ACTIVATE', level)")
     *
     * @ApiDoc(
     *     name="activate/deactivate level",
     *     section="Level",
     *     parameters={{"name"="active", "dataType"="boolean", "required"=true}},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when active parameter is not present",
     *       404="Returned when level does not exist"
     *     }
     * )
     *
     * @param Request $request
     * @param Level   $level
     *
     * @return \FOS\RestBundle\View\View
     */
    public function activateLevelAction(Request $request, Level $level)
    {
        $activate = $request->request->get('active', null);
        if (null === $activate) {
            return $this->view(['active' => 'this field is required'], Response::HTTP_BAD_REQUEST);
        }

        $commandBus = $this->get('broadway.command_handling.command_bus');

        if ($activate) {
            $commandBus->dispatch(new ActivateLevel($level->getLevelId()));
        } else {
            $commandBus->dispatch(new DeactivateLevel($level->getLevelId()));
        }

        return $this->view();
    }

    /**
     * Add photo to level.
     *
     * @Route(name="oloy.level.add_photo", path="/level/{level}/photo")
     * @Method("POST")
     * @Security("is_granted('EDIT', level)")
     * @ApiDoc(
     *     name="Add photo to Level",
     *     section="Level",
     *     input={"class" = "OpenLoyalty\Bundle\LevelBundle\Form\Type\LevelPhotoFormType", "name" = "photo"}
     * )
     *
     * @param Request             $request
     * @param Level               $level
     * @param TranslatorInterface $translator
     *
     * @return View
     */
    public function addPhotoAction(Request $request, Level $level, TranslatorInterface $translator)
    {
        $form = $this->get('form.factory')->createNamed('photo', LevelPhotoFormType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->getData()->getFile();
            $uploader = $this->get('oloy.level.photo_uploader');
            try {
                $uploader->remove($level->getPhoto());
                $photo = $uploader->upload($file);
                $command = new SetLevelPhoto($level->getLevelId(), $photo);
                $this->get('broadway.command_handling.command_bus')->dispatch($command);

                return $this->view([], Response::HTTP_OK);
            } catch (\Exception $ex) {
                return $this->view(['error' => $translator->trans($ex->getMessage())], Response::HTTP_BAD_REQUEST);
            }
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Get level photo.
     *
     * @Route(name="oloy.level.get_photo", path="/level/{level}/photo")
     * @Method("GET")
     * @ApiDoc(
     *     name="Get level photo",
     *     section="Level"
     * )
     *
     * @param Level $level
     *
     * @return Response
     */
    public function getPhotoAction(Level $level)
    {
        $photo = $level->getPhoto();
        if (!$photo) {
            throw $this->createNotFoundException();
        }
        $content = $this->get('oloy.level.photo_uploader')->get($photo);
        if (!$content) {
            throw $this->createNotFoundException();
        }

        $response = new Response($content);
        $response->headers->set('Content-Disposition', 'inline');
        $response->headers->set('Content-Type', $photo->getMime());

        return $response;
    }

    /**
     * Remove photo from level.
     *
     * @Route(name="oloy.level.remove_photo", path="/level/{level}/photo")
     * @Method("DELETE")
     * @Security("is_granted('EDIT', level)")
     * @ApiDoc(
     *     name="Delete photo from Level",
     *     section="Level"
     * )
     *
     * @param Level               $level
     * @param TranslatorInterface $translator
     *
     * @return View
     */
    public function removePhotoAction(Level $level, TranslatorInterface $translator)
    {
        $uploader = $this->get('oloy.level.photo_uploader');
        $uploader->remove($level->getPhoto());

        $command = new RemoveLevelPhoto($level->getLevelId());

        try {
            $this->get('broadway.command_handling.command_bus')->dispatch($command);

            return $this->view([], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return $this->view(['error' => $translator->trans($ex->getMessage())], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Method will return complete list of levels.
     *
     * @Route(name="oloy.customer.level.list", path="/customer/level")
     * @Method("GET")
     * @Security("is_granted('CUSTOMER_LIST_LEVELS')")
     * @Rest\View(serializerGroups={"customer"})
     *
     *
     * @ApiDoc(
     *     name="get Level list",
     *     section="Customer Level",
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
     * @return \FOS\RestBundle\View\View
     */
    public function getAllVisibleLevelsAction(Request $request): View
    {
        $pagination = $this->get('oloy.pagination')->handleFromRequest($request);

        $levelRepository = $this->get('oloy.level.repository');

        $levels = $levelRepository
            ->findActivePaginated(
                $pagination->getPage(),
                $pagination->getPerPage(),
                $pagination->getSort(),
                $pagination->getSortDirection()
            );

        return $this->view(
            [
                'levels' => $levels,
            ],
            Response::HTTP_OK
        );
    }
}
