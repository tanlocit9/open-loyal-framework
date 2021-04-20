<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UtilityBundle\Tests\Integration\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\UtilityBundle\Security\Voter\UtilityVoter;

/**
 * Class TransactionVoterTest.
 */
class UtilityVoterTest extends BaseVoterTest
{
    const TRANSACTION_ID = '00000000-0000-474c-b092-b0dd880c0700';
    const TRANSACTION2_ID = '00000000-0000-474c-b092-b0dd880c0701';

    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            UtilityVoter::GENERATE_CSV_BY_LEVEL => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
            UtilityVoter::GENERATE_CSV_BY_SEGMENT => ['seller' => false, 'customer' => false, 'admin' => true, 'admin_reporter' => true],
        ];

        $voter = new UtilityVoter();

        $this->assertVoterAttributes($voter, $attributes);
    }

    protected function getSubjectById($id)
    {
        return;
    }
}
