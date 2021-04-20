<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Controller\Api;

use Broadway\CommandHandling\CommandBus;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\SettingsBundle\Service\GeneralSettingsManagerInterface;
use OpenLoyalty\Bundle\UserBundle\Entity\User;
use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\Account\Domain\SystemEvent\AccountSystemEvents;
use OpenLoyalty\Component\Account\Domain\SystemEvent\CustomEventOccurredSystemEvent;
use OpenLoyalty\Component\Account\Infrastructure\Exception\EarningRuleLimitExceededException;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\EarningRule\Domain\Command\UseCustomEventEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleRepository;
use OpenLoyalty\Component\EarningRule\Domain\Model\UsageSubject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CustomerEarningRuleController.
 */
class CustomerEarningRuleController extends FOSRestController
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
     * @var GeneralSettingsManagerInterface
     */
    private $settingsManager;

    /**
     * @var EarningRuleRepository
     */
    private $earningRuleRepository;

    /**
     * @var CustomerDetailsRepository
     */
    private $customerDetailsRepository;

    /**
     * CustomerEarningRuleController constructor.
     *
     * @param CommandBus                      $commandBus
     * @param TranslatorInterface             $translator
     * @param GeneralSettingsManagerInterface $generalSettingsManager
     * @param EarningRuleRepository           $earningRuleRepository
     * @param CustomerDetailsRepository       $customerDetailsRepository
     */
    public function __construct(
        CommandBus $commandBus,
        TranslatorInterface $translator,
        GeneralSettingsManagerInterface $generalSettingsManager,
        EarningRuleRepository $earningRuleRepository,
        CustomerDetailsRepository $customerDetailsRepository
    ) {
        $this->commandBus = $commandBus;
        $this->translator = $translator;
        $this->settingsManager = $generalSettingsManager;
        $this->earningRuleRepository = $earningRuleRepository;
        $this->customerDetailsRepository = $customerDetailsRepository;
    }

    /**
     * Method will return all active earning rules.
     *
     * @Route(name="oloy.earning_rule.customer.list", path="/customer/earningRule")
     * @Method("GET")
     * @Security("is_granted('LIST_ACTIVE_EARNING_RULES')")
     *
     * @ApiDoc(
     *     name="get earning rules for customer",
     *     section="Customer Earning Rule"
     * )
     *
     * @return View
     */
    public function getListAction(): View
    {
        $rules = $this->earningRuleRepository->findAllActive();

        $currency = $this->settingsManager->getCurrency();

        return $this->view(
            [
                'earningRules' => $rules,
                'currency' => $currency ?? 'PLN',
            ],
            Response::HTTP_OK
        );
    }

    /**
     * This method allows to use a custom event earning rule.<br/>
     * All you need to do is call this api endpoint with proper parameters.
     *
     * @Route(name="oloy.earning_rule.customer.report_custom_event", path="/customer/earnRule/{eventName}")
     * @Method("POST")
     * @Security("is_granted('CUSTOMER_USE')")
     * @ApiDoc(
     *     name="report custom event by customer and earn points",
     *     section="Customer Earning Rule",
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when earning rule for event does not exist or limit was exceeded. Additional info provided in response.",
     *       404="Returned when customer does not exist"
     *     }
     *
     * )
     *
     * @param string $eventName
     *
     * @return View
     */
    public function reportCustomEventAction(string $eventName): View
    {
        $customer = $this->getLoggedCustomer();

        try {
            $event = new CustomEventOccurredSystemEvent(
                new CustomerId((string) $customer->getCustomerId()),
                $eventName
            );

            $this->get('broadway.event_dispatcher')->dispatch(
                AccountSystemEvents::CUSTOM_EVENT_OCCURRED,
                [$event]
            );
        } catch (EarningRuleLimitExceededException $e) {
            return $this->view(['error' => $this->translator->trans('limit exceeded')], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->view(null, Response::HTTP_BAD_REQUEST);
        }

        if ($event->getEvaluationResult() === null) {
            return $this->view(['error' => $this->translator->trans('event does not exist')], Response::HTTP_BAD_REQUEST);
        }

        $this->commandBus->dispatch(
            new UseCustomEventEarningRule(
                 new EarningRuleId($event->getEvaluationResult()->getEarningRuleId()),
                 new UsageSubject((string) $customer->getCustomerId())
             )
        );

        return $this->view(['points' => $event->getEvaluationResult()->getPoints()], Response::HTTP_OK);
    }

    /**
     * @return CustomerDetails
     */
    protected function getLoggedCustomer(): CustomerDetails
    {
        /** @var User $user */
        $user = $this->getUser();
        $customer = $this->customerDetailsRepository->find($user->getId());
        if (!$customer instanceof CustomerDetails) {
            throw $this->createNotFoundException();
        }

        return $customer;
    }
}
