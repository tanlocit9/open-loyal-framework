<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\UserBundle\Entity\Customer;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Bundle\UserBundle\Form\Type\PasswordResetFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ResetPasswordController.
 */
class ResetPasswordController extends FOSRestController
{
    /**
     * Purpose of this method is to provide "Forgot password" functionality.<br/>Invoking this method will send message to the user with password reset url.
     *
     * @Route(name="oloy.user.reset.request", path="/password/reset/request")
     * @Route(name="oloy.user.reset.request_customer", path="/customer/password/reset/request", defaults={"customer":1})
     * @Method("POST")
     * @ApiDoc(
     *     name="Request reset password",
     *     section="Security",
     *     parameters={{"name"="username", "required"=true, "dataType"="string"}},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when username parameter is not present or resetting password already requested",
     *     }
     * )
     *
     * @param Request             $request
     * @param bool                $customer
     * @param TranslatorInterface $translator
     *
     * @return View
     */
    public function resetRequestAction(Request $request, $customer = false, TranslatorInterface $translator)
    {
        $username = $request->request->get('username');
        if (!$username) {
            return $this->view(['error' => 'field "username" should not be empty'], Response::HTTP_BAD_REQUEST);
        }
        $userManager = $this->get('oloy.user.user_manager');
        if ($customer) {
            $provider = $this->get('oloy.user.customer_provider');
        } else {
            $provider = $this->get('oloy.user.all_users_provider');
        }
        /* @var $user User */
        try {
            $user = $provider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            throw new NotFoundHttpException($translator->trans($e->getMessage()));
        }

        if (null === $user) {
            return $this->view(['success' => true]);
        }

        if ($user->isPasswordRequestNonExpired(86400)) {
            return $this->view(['error' => $translator->trans('resetting password already requested')], Response::HTTP_BAD_REQUEST);
        }
        if (null === $user->getConfirmationToken()) {
            $tokenGenerator = $this->get('oloy.user.token_generator');
            $token = $tokenGenerator->generateToken();
        } else {
            $token = $user->getConfirmationToken();
        }

        if ($user instanceof Customer && !empty($user->getPhone())) {
            $this->get('oloy.action_token_manager')->sendPasswordReset($user, $token);
        } else {
            $this->get('oloy.activation_method.email')->sendPasswordReset($user, $token);
        }

        $userManager->updateUser($user);

        return $this->view(['success' => true]);
    }

    /**
     * Method allows to set new password after reset password requesting.
     *
     * @param Request $request
     *
     * @return View
     * @Route(name="oloy.user.reset", path="/password/reset")
     * @Method("POST")
     * @ApiDoc(
     *     name="Reset password",
     *     section="Security",
     *     input={"class" = "OpenLoyalty\Bundle\UserBundle\Form\Type\PasswordResetFormType", "name" = "reset"},
     *     parameters={{"name"="token", "required"=true, "dataType"="string"}},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when token parameter is not present or user with such token does not exist",
     *     }
     * )
     */
    public function resetAction(Request $request)
    {
        $userManager = $this->get('oloy.user.user_manager');
        $token = $request->get('token');
        $user = null;

        if (!$token) {
            return $this->view(['error' => 'field "token" should not be empty'], Response::HTTP_BAD_REQUEST);
        }

        $code = $this->get('oloy.activation_code_manager')->findValidCode($token, Customer::class);
        if (null !== $code) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('OpenLoyaltyUserBundle:Customer')->find($code->getObjectId());
        } else {
            $user = $userManager->findUserByConfirmationToken($token);
        }

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        $form = $this->get('form.factory')->createNamed('reset', PasswordResetFormType::class, $user, [
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user->setPasswordRequestedAt(null);
            $user->setConfirmationToken(null);
            $userManager->updateUser($user);

            return $this->view(['success' => true]);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }
}
