<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Domain\ReadModel;

use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Interface InvitationDetailsRepository.
 */
interface InvitationDetailsRepository extends Repository
{
    /**
     * @param array  $params
     * @param bool   $exact
     * @param int    $page
     * @param int    $perPage
     * @param null   $sortField
     * @param string $direction
     *
     * @return InvitationDetails[]
     */
    public function findByParametersPaginated(array $params, $exact = true, $page = 1, $perPage = 10, $sortField = null, $direction = 'DESC');

    /**
     * @param array $params
     * @param bool  $exact
     *
     * @return int
     */
    public function countTotal(array $params = [], $exact = true): int;

    /**
     * @param $token
     *
     * @return InvitationDetails[]
     */
    public function findByToken($token): array;

    /**
     * @param CustomerId $recipient
     *
     * @return InvitationDetails|null
     */
    public function findOneByRecipientId(CustomerId $recipient): ?InvitationDetails;
}
