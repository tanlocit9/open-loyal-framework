<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace OpenLoyalty\Bundle\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\UserBundle\Form\Type\CustomerSearchFormType;
use OpenLoyalty\Bundle\UserBundle\Model\SearchCustomer;
use OpenLoyalty\Component\Customer\Domain\Exception\TooManyResultsException;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CustomerSearchController.
 */
class CustomerSearchController extends FOSRestController
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * CustomerSearchController constructor.
     *
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * This method should be used to search customers.
     *
     * @Route(name="oloy.user.search", path="/pos/search/customer")
     * @Method("POST")
     * @Security("is_granted('SEARCH_CUSTOMER')")
     *
     * @ApiDoc(
     *     name="Search customer",
     *     section="Customer",
     *     input={"class" = "OpenLoyalty\Bundle\UserBundle\Form\Type\CustomerSearchFormType", "name" = "search"},
     *     statusCodes={
     *          200="Returned when successful",
     *          400="Returned when form contains errors or there are to many results and search query should be more specific",
     *     }
     * )
     *
     * @param Request $request
     *
     * @return View
     */
    public function findAction(Request $request): View
    {
        $form = $this->formFactory->createNamed('search', CustomerSearchFormType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var CustomerDetailsRepository $customerDetailsRepository */
            $customerDetailsRepository = $this->get('oloy.user.read_model.repository.customer_details');

            /** @var SearchCustomer $data */
            $data = $form->getData();

            try {
                $customers = $customerDetailsRepository->findCustomersByParameters(
                    $data->toCriteriaArray(),
                    $this->container->getParameter('es_max_result_window_size')
                );
            } catch (TooManyResultsException $exception) {
                return $this->view(['error' => 'too many results'], Response::HTTP_BAD_REQUEST);
            }

            return $this->view(['customers' => $customers], Response::HTTP_OK);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }
}
