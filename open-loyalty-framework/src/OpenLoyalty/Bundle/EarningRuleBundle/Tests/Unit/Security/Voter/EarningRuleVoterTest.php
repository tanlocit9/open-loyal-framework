<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\EarningRuleBundle\Security\Voter\EarningRuleVoter;
use OpenLoyalty\Component\EarningRule\Domain\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;

/**
 * Class EarningRuleVoterTest.
 */
final class EarningRuleVoterTest extends BaseVoterTest
{
    const EARNING_RULE_ID = '00000000-0000-474c-b092-b0dd880c0700';
    const EARNING_RULE2_ID = '00000000-0000-474c-b092-b0dd880c0702';

    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            EarningRuleVoter::CREATE_EARNING_RULE => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
            EarningRuleVoter::USE => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false],
            EarningRuleVoter::CUSTOMER_USE => ['seller' => false, 'customer' => true, 'admin' => false, 'admin_reporter' => false],
            EarningRuleVoter::EDIT => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => false, 'id' => self::EARNING_RULE_ID],
            EarningRuleVoter::VIEW => ['seller' => true, 'customer' => false, 'admin' => true, 'admin_reporter' => true, 'id' => self::EARNING_RULE2_ID],
            EarningRuleVoter::LIST_ALL_EARNING_RULES => ['seller' => true, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
            EarningRuleVoter::LIST_ACTIVE_EARNING_RULES => ['seller' => false, 'customer' => true, 'admin' => true, 'admin_reporter' => true],
        ];

        $voter = new EarningRuleVoter();

        $this->assertVoterAttributes($voter, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubjectById($id)
    {
        $earningRule = $this->getMockBuilder(EarningRule::class)->disableOriginalConstructor()->getMock();
        $earningRule->method('getEarningRuleId')->willReturn(new EarningRuleId($id));

        return $earningRule;
    }
}
