<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\Repository;

use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBought;
use OpenLoyalty\Component\Campaign\Domain\ReadModel\CampaignBoughtRepository;
use OpenLoyalty\Component\Core\Infrastructure\Repository\OloyElasticsearchRepository;

/**
 * Class CampaignBoughtElasticsearchRepository.
 */
class CampaignBoughtElasticsearchRepository extends OloyElasticsearchRepository implements CampaignBoughtRepository
{
    /**
     * {@inheritdoc}
     */
    public function findByTransactionIdAndCustomerId(string $transactionId, string $customerId): array
    {
        $query = [
            'bool' => [
                'must' => [
                    [
                        'match' => [
                            'customerId' => $customerId,
                        ],
                    ],
                    [
                        'match' => [
                            'transactionId' => $transactionId,
                        ],
                    ],
                ],
            ],
        ];

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findByCustomerIdAndUsed(string $customerId, bool $used): array
    {
        $filter = [
            'must_not' => [
                [
                    'term' => [
                        'used' => !$used,
                    ],
                ],
            ],
        ];

        if ($used === true) {
            $filter = [
                'must' => [
                    [
                        'term' => [
                            'used' => $used,
                        ],
                    ],
                ],
            ];
        }
        $filter['must'][]['term'] = ['customerId' => $customerId];

        $query = [
            'bool' => $filter,
        ];

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findByCustomerId(string $customerId): array
    {
        $query = [
            'bool' => [
                'must' => [
                    [
                        'term' => [
                            'customerId' => $customerId,
                        ],
                    ],
                ],
            ],
        ];

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findByCustomerIdAndUsedForTransactionId(string $customerId, string $transactionId, string $reward): array
    {
        $filter = [
            'must' => [
                [
                    'term' => [
                        'used' => true,
                    ],
                ],
            ],
        ];
        $filter['must'][]['term'] = ['customerId' => $customerId];
        $filter['must'][]['term'] = ['usedForTransactionId' => $transactionId];
        $filter['must'][]['term'] = ['campaignType' => $reward];

        $query = [
            'bool' => $filter,
        ];

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByCouponId(string $couponId): CampaignBought
    {
        $query = [
            'bool' => [
                'must' => [
                    [
                        'term' => [
                            'couponId' => $couponId,
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->query($query);

        if (!count($result)) {
            throw new \InvalidArgumentException('Campaign bought not found!');
        }

        return reset($result);
    }
}
