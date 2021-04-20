<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Tests\Unit\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\UserBundle\Security\Voter\UserVoter;

/**
 * Class UserVoterTest.
 */
class UserVoterTest extends BaseVoterTest
{
    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            UserVoter::PASSWORD_CHANGE => ['seller' => true, 'customer' => true, 'admin' => true],
            UserVoter::REVOKE_REFRESH_TOKEN => ['seller' => true, 'customer' => true, 'admin' => true],
        ];

        $voter = new UserVoter();

        $this->assertVoterAttributes($voter, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSubjectById($id)
    {
        return;
    }
}
