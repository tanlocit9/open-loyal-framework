<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Tests\Integration\Security\Voter;

use OpenLoyalty\Bundle\CampaignBundle\Security\Voter\CampaignCategoryVoter;
use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\CampaignBundle\Security\Voter\CampaignVoter;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategory;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryId;

/**
 * Class CampaignCategoryVoterTest.
 */
class CampaignCategoryVoterTest extends BaseVoterTest
{
    const CAMPAIGN_CATEGORY_ID = '00000000-0000-474c-b092-b0dd880c0700';
    const CAMPAIGN_CATEGORY2_ID = '00000000-0000-474c-b092-b0dd880c0702';

    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            CampaignCategoryVoter::CREATE_CAMPAIGN_CATEGORY => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
            CampaignCategoryVoter::EDIT => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => self::CAMPAIGN_CATEGORY_ID],
            CampaignCategoryVoter::VIEW => ['seller' => true, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => self::CAMPAIGN_CATEGORY2_ID],
            CampaignCategoryVoter::LIST_ALL_CAMPAIGN_CATEGORIES => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
        ];

        $voter = new CampaignCategoryVoter();

        $this->assertVoterAttributes($voter, $attributes);

        $this->assertEquals(true, $voter->vote($this->getCustomerToken(), $this->getSubjectById(self::CAMPAIGN_CATEGORY_ID), [CampaignVoter::VIEW]));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubjectById($id)
    {
        $campaign = $this->getMockBuilder(CampaignCategory::class)->disableOriginalConstructor()->getMock();
        $campaign->method('getCampaignCategoryId')->willReturn(new CampaignCategoryId($id));

        return $campaign;
    }
}
