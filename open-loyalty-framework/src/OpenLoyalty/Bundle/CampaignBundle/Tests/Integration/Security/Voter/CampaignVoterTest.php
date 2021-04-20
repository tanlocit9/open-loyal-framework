<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Tests\Integration\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\CampaignBundle\Security\Voter\CampaignVoter;
use OpenLoyalty\Bundle\CampaignBundle\Service\CampaignProvider;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;

/**
 * Class CampaignVoterTest.
 */
class CampaignVoterTest extends BaseVoterTest
{
    const CAMPAIGN_ID = '00000000-0000-474c-b092-b0dd880c0700';
    const CAMPAIGN2_ID = '00000000-0000-474c-b092-b0dd880c0702';

    /**
     * @test
     */
    public function it_works()
    {
        $provider = $this->getMockBuilder(CampaignProvider::class)->disableOriginalConstructor()->getMock();
        $provider->method('visibleForCustomers')->with($this->isInstanceOf(Campaign::class))
            ->will($this->returnCallback(function (Campaign $campaign) {
                if ((string) $campaign->getCampaignId() === self::CAMPAIGN_ID) {
                    return [self::USER_ID];
                }
                if ((string) $campaign->getCampaignId() === self::CAMPAIGN2_ID) {
                    return [];
                }
            }))
        ;

        $attributes = [
            CampaignVoter::CREATE_CAMPAIGN => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
            CampaignVoter::EDIT => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => self::CAMPAIGN_ID],
            CampaignVoter::LIST_ALL_CAMPAIGNS => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
            CampaignVoter::LIST_ALL_VISIBLE_CAMPAIGNS => ['seller' => true, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
            CampaignVoter::LIST_ALL_ACTIVE_CAMPAIGNS => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
            CampaignVoter::LIST_ALL_BOUGHT_CAMPAIGNS => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
            CampaignVoter::VIEW => ['seller' => true, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => self::CAMPAIGN2_ID],
            CampaignVoter::LIST_CAMPAIGNS_AVAILABLE_FOR_ME => ['seller' => false, 'customer' => true, 'admin' => false, 'admin_reporter' => false],
            CampaignVoter::LIST_CAMPAIGNS_BOUGHT_BY_ME => ['seller' => false, 'customer' => true, 'admin' => false, 'admin_reporter' => false],
            CampaignVoter::BUY => ['seller' => false, 'customer' => true, 'admin' => false, 'admin_reporter' => false, 'id' => self::CAMPAIGN2_ID],
            CampaignVoter::BUY_FOR_CUSTOMER_SELLER => ['seller' => true, 'customer' => false, 'admin' => false, 'admin_reporter' => false],
            CampaignVoter::BUY_FOR_CUSTOMER_ADMIN => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
            CampaignVoter::MARK_MULTIPLE_COUPONS_AS_USED => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
            CampaignVoter::MARK_SELF_MULTIPLE_COUPONS_AS_USED => ['seller' => false, 'customer' => true, 'admin' => false, 'admin_reporter' => false],
            CampaignVoter::CASHBACK => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
            CampaignVoter::VIEW_BUY_FOR_CUSTOMER_SELLER => ['seller' => true, 'customer' => false, 'admin' => false, 'admin_reporter' => false],
            CampaignVoter::VIEW_BUY_FOR_CUSTOMER_ADMIN => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
            CampaignVoter::LIST_ALL_CAMPAIGNS_CUSTOMERS => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
        ];

        $voter = new CampaignVoter($provider);

        $this->assertVoterAttributes($voter, $attributes);

        $this->assertEquals(true, $voter->vote($this->getCustomerToken(), $this->getSubjectById(self::CAMPAIGN_ID), [CampaignVoter::VIEW]));
    }

    protected function getSubjectById($id)
    {
        $campaign = $this->getMockBuilder(Campaign::class)->disableOriginalConstructor()->getMock();
        $campaign->method('getCampaignId')->willReturn(new CampaignId($id));

        return $campaign;
    }
}
