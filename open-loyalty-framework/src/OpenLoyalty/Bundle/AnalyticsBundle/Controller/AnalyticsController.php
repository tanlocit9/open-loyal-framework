<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\AnalyticsBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetailsRepository;
use OpenLoyalty\Component\Customer\Domain\Invitation;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetailsRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class AnalyticsController.
 */
class AnalyticsController extends FOSRestController
{
    /**
     * @var PointsTransferDetailsRepository
     */
    private $pointsTransferDetailsRepository;

    /**
     * AnalyticsController constructor.
     *
     * @param PointsTransferDetailsRepository $pointsTransferDetailsRepository
     */
    public function __construct(PointsTransferDetailsRepository $pointsTransferDetailsRepository)
    {
        $this->pointsTransferDetailsRepository = $pointsTransferDetailsRepository;
    }

    /**
     * Get transactions statistics.
     *
     * Method will return information about total transactions count, total amount, etc.
     *
     * [Example response] <br/>
     * <pre>{ <br/>
     *      "total": 10,<br/>
     *      "amount": 1200,<br/>
     *      "amountWithoutDeliveryCosts": 1000,<br/>
     *      "currency": "PLN"<br/>
     * }</pre>
     *
     * @Route(name="oloy.analytics.transactions", path="/admin/analytics/transactions")
     * @Method("GET")
     * @Security("is_granted('VIEW_STATS')")
     * @ApiDoc(
     *     name="transactions statistics",
     *     section="Analytics"
     * )
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getTransactionsStats()
    {
        /** @var CustomerDetailsRepository $repo */
        $repo = $this->get('oloy.user.read_model.repository.customer_details');
        $currency = $this->get('ol.settings.manager')->getSettingByKey('currency');

        return $this->view([
            'total' => $repo->sumAllByField('transactionsCount'),
            'amount' => $repo->sumAllByField('transactionsAmount'),
            'amountWithoutDeliveryCosts' => $repo->sumAllByField('transactionsAmountWithoutDeliveryCosts'),
            'currency' => $currency ? $currency->getValue() : 'PLN',
        ]);
    }

    /**
     * Get points statistics.
     *
     * Method will return information about total count of spending points operations and total amount of used points.
     *
     * [Example response] <br/>
     * <pre>{ <br/>
     *      "totalSpendingTransfers": 11, // count of spending points operations<br/>
     *      "totalPointsSpent": 100 // total amount of used points<br/>
     * }</pre>
     *
     * @Route(name="oloy.analytics.points", path="/admin/analytics/points")
     * @Method("GET")
     * @Security("is_granted('VIEW_STATS')")
     * @ApiDoc(
     *     name="points statistics",
     *     section="Analytics"
     * )
     *
     * @return View
     */
    public function getPointsStats(): View
    {
        $totalSpendingTransfers = $this->pointsTransferDetailsRepository->countTotalSpendingTransfers();

        $valueOfSpendingTransfers = $this->pointsTransferDetailsRepository->getTotalValueOfSpendingTransfers();

        return $this->view(
            [
                'totalSpendingTransfers' => $totalSpendingTransfers,
                'totalPointsSpent' => $valueOfSpendingTransfers,
            ]
        );
    }

    /**
     * Get customers statistics.
     *
     * Method will return information about total count of customers registered in system.
     *
     * [Example response] <br/>
     * <pre>{ <br/>
     *      "total": 11
     * }</pre>
     *
     * @Route(name="oloy.analytics.customers", path="/admin/analytics/customers")
     * @Method("GET")
     * @Security("is_granted('VIEW_STATS')")
     * @ApiDoc(
     *     name="points statistics",
     *     section="Analytics"
     * )
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getCustomersStats()
    {
        /** @var CustomerDetailsRepository $repo */
        $repo = $this->get('oloy.user.read_model.repository.customer_details');

        return $this->view([
            'total' => $repo->countTotal(),
        ]);
    }

    /**
     * @Route(name="oloy.analytics.referrals", path="/admin/analytics/referrals")
     * @Method("GET")
     * @Security("is_granted('VIEW_STATS')")
     * @ApiDoc(
     *     name="referral statistics",
     *     section="Analytics"
     * )
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getReferralStats()
    {
        /** @var InvitationDetailsRepository $repo */
        $repo = $this->get('oloy.user.read_model.repository.invitation_details');

        $totalCompleted = $repo->countTotal(['status' => Invitation::STATUS_MADE_PURCHASE], true);
        $totalRegistered = $repo->countTotal(['status' => Invitation::STATUS_REGISTERED], true);

        return $this->view([
            'total' => $repo->countTotal(),
            'totalCompleted' => $totalCompleted,
            'totalRegistered' => $totalRegistered,
        ]);
    }
}
