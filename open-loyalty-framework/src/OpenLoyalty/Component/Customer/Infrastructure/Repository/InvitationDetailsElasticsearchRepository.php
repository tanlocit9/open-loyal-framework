<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Infrastructure\Repository;

use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\InvitationDetailsRepository;
use OpenLoyalty\Component\Core\Infrastructure\Repository\OloyElasticsearchRepository;

/**
 * Class InvitationDetailsElasticsearchRepository.
 */
class InvitationDetailsElasticsearchRepository extends OloyElasticsearchRepository implements InvitationDetailsRepository
{
    protected $dynamicFields = [
        [
            'token' => [
                'match' => 'token',
                'match_mapping_type' => 'string',
                'mapping' => [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                ],
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function findByToken($token): array
    {
        return $this->findByParameters([
            'token' => $token,
        ], true);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByRecipientId(CustomerId $recipientId): ?InvitationDetails
    {
        $invitations = $this->findByParameters([
            'recipientId' => (string) $recipientId,
        ], true);

        if (count($invitations) == 0) {
            return null;
        }

        return reset($invitations);
    }
}
