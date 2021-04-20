<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\UserBundle\Service\UserSettingsManagerFactory;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class CustomerPushyController.
 */
class CustomerPushyController extends FOSRestController
{
    /**
     * List Pushy tokens.
     *
     * @Route(name="oloy.customer.list_pushy_token", path="/customer/{customer}/pushy-token")
     * @Method("GET")
     * @Security("is_granted('VIEW', customer)")
     * @ApiDoc(
     *     name="List customer's Pushy device tokens",
     *     section="Customer",
     *     statusCodes={
     *          200="Returned when successful",
     *     }
     * )
     *
     * @param CustomerDetails $customer
     *
     * @return View
     */
    public function listPushyTokensAction(CustomerDetails $customer): View
    {
        $userSettingsManager = $this->get(UserSettingsManagerFactory::class)
            ->createForUser($customer->getCustomerId());
        $pushyTokens = $userSettingsManager->getPushyTokens();

        return $this->view(['tokens' => $pushyTokens]);
    }

    /**
     * Add Pushy token.
     *
     * @Route(name="oloy.customer.add_pushy_token", path="/customer/{customer}/pushy-token")
     * @Method("POST")
     * @Security("is_granted('EDIT', customer)")
     * @ApiDoc(
     *     name="Add customer's Pushy device token",
     *     parameters={{"name"="customer[pushyToken]", "dataType"="string", "required"=true}},
     *     section="Customer",
     *     statusCodes={
     *          204="Returned when successful",
     *          400="Returned when customer's token wasn't added.",
     *     }
     * )
     *
     * @param CustomerDetails $customer
     * @param Request         $request
     *
     * @return View
     */
    public function addPushyTokenAction(CustomerDetails $customer, Request $request): View
    {
        $userSettingsManager = $this->get(UserSettingsManagerFactory::class)
            ->createForUser($customer->getCustomerId());
        $pushyTokens = $userSettingsManager->getPushyTokens();

        $newCustomerSettings = $request->request->get('customer');
        $newToken = $newCustomerSettings['pushyToken'];

        if (in_array($newToken, $pushyTokens)) {
            return $this->view(null, Response::HTTP_NO_CONTENT);
        }

        try {
            $userSettingsManager->setPushyTokens(array_merge($pushyTokens, [$newToken]));
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove Pushy token.
     *
     * @Route(name="oloy.customer.remove_pushy_token", path="/customer/{customer}/pushy-token/{tokenToRemove}")
     * @Method("DELETE")
     * @Security("is_granted('EDIT', customer)")
     * @ApiDoc(
     *     name="Remove customer's Pushy device token",
     *     section="Customer",
     *     statusCodes={
     *          204="Returned when successful",
     *          400="Returned when customer's token wasn't removed.",
     *     }
     * )
     *
     * @param CustomerDetails $customer
     * @param string          $tokenToRemove
     *
     * @return View
     */
    public function removePushyTokenAction(CustomerDetails $customer, string $tokenToRemove): View
    {
        $userSettingsManager = $this->get(UserSettingsManagerFactory::class)
            ->createForUser($customer->getCustomerId());
        $pushyTokens = $userSettingsManager->getPushyTokens();

        if (!in_array($tokenToRemove, $pushyTokens)) {
            return $this->view(null, Response::HTTP_NO_CONTENT);
        }

        try {
            $pushyTokens = array_filter(
                $pushyTokens,
                function ($existingToken) use ($tokenToRemove) {
                    return $existingToken !== $tokenToRemove;
                }
            );
            $userSettingsManager->setPushyTokens($pushyTokens);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
