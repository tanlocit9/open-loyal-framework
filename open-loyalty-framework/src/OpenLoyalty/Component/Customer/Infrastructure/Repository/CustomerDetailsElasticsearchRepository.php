<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Customer\Infrastructure\Repository;

use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Customer\Domain\CustomerId;
use OpenLoyalty\Component\Customer\Domain\Model\CampaignPurchase;
use OpenLoyalty\Component\Customer\Domain\Exception\TooManyResultsException;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetailsRepository;
use OpenLoyalty\Component\Core\Infrastructure\Repository\OloyElasticsearchRepository;

/**
 * Class CustomerDetailsElasticsearchRepository.
 */
class CustomerDetailsElasticsearchRepository extends OloyElasticsearchRepository implements CustomerDetailsRepository
{
    protected $dynamicFields = [
        [
            'firstName' => [
                'match' => 'firstName',
                'match_mapping_type' => 'string',
                'mapping' => [
                    'type' => 'string',
                    'analyzer' => 'small_letters',
                ],
            ],
        ],
        [
            'lastName' => [
                'match' => 'lastName',
                'match_mapping_type' => 'string',
                'mapping' => [
                    'type' => 'string',
                    'analyzer' => 'small_letters',
                ],
            ],
        ],
        [
            'nestedCampaignPurchases' => [
                'match' => 'campaignPurchases',
                'mapping' => [
                    'type' => 'nested',
                ],
            ],
        ],
        [
            'nestedCampaignPurchasesReward' => [
                'path_match' => 'campaignPurchases.reward',
                'mapping' => [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                ],
            ],
        ],
        [
            'nestedCampaignPurchasesDeliveryStatus' => [
                'path_match' => 'campaignPurchases.deliveryStatus',
                'mapping' => [
                    'type' => 'string',
                    'index' => 'not_analyzed',
                ],
            ],
        ],
        [
            'transactionsAmount' => [
                'match' => 'transactionsAmount',
                'mapping' => [
                    'type' => 'double',
                ],
            ],
        ],
        [
            'averageTransactionAmount' => [
                'match' => 'averageTransactionAmount',
                'mapping' => [
                    'type' => 'double',
                ],
            ],
        ],
        [
            'transactionsAmountWithoutDeliveryCosts' => [
                'match' => 'transactionsAmountWithoutDeliveryCosts',
                'mapping' => [
                    'type' => 'double',
                ],
            ],
        ],
        [
            'label_value' => [
                'path_match' => 'labels.*',
                'mapping' => [
                    'type' => 'string',
                    'analyzer' => 'small_letters',
                ],
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function findByBirthdayAnniversary(\DateTime $from, \DateTime $to, $onlyActive = true): array
    {
        $filter = [];
        foreach ($this->getTimestamps($from, $to) as $period) {
            $filter[] = ['range' => [
                'birthDate' => [
                    'gte' => $period['from'],
                    'lt' => $period['to'],
                ],
            ]];
        }

        $query = array(
            'bool' => array(
                'must' => [
                    ['bool' => [
                        'should' => $filter,
                    ]],
                ],
            ),
        );

        if ($onlyActive) {
            $query['bool']['must'][] = ['term' => ['active' => true]];
        }

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForLevelRecalculation(\DateTime $currentDate, int $recalculationIntervalInDays): array
    {
        $filter = [];
        $date = clone $currentDate;
        $date->modify(sprintf('-%u days', $recalculationIntervalInDays));

        $filter[] = [
            'bool' => [
                'must' => [
                    [
                        'missing' => [
                            'field' => 'lastLevelRecalculation',
                        ],
                    ],
                    [
                        'range' => [
                            'createdAt' => [
                                'lte' => $date->getTimestamp(),
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $filter[] = [
            'bool' => [
                'must' => [
                    [
                        'exists' => [
                            'field' => 'lastLevelRecalculation',
                        ],
                    ],
                    [
                        'range' => [
                            'lastLevelRecalculation' => [
                                'lte' => $date->getTimestamp(),
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $query = [
            'bool' => [
                'must' => [
                    ['bool' => [
                        'should' => $filter,
                    ]],
                ],
            ],
        ];

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findByCreationAnniversary(\DateTime $from, \DateTime $to, $onlyActive = true): array
    {
        $filter = [];
        foreach ($this->getTimestamps($from, $to) as $period) {
            $filter[] = ['range' => [
                'createdAt' => [
                    'gte' => $period['from'],
                    'lt' => $period['to'],
                ],
            ]];
        }

        $query = [
            'bool' => [
                'must' => [
                    ['bool' => [
                        'should' => $filter,
                    ]],
                ],
            ],
        ];

        if ($onlyActive) {
            $query['bool']['must'][] = ['term' => ['active' => true]];
        }

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findPurchasesByCustomerIdPaginated(
        CustomerId $customerId,
        $page = 1,
        $perPage = 10,
        $sortField = null,
        $direction = 'DESC',
        $showCashback = false,
        string $deliveryStatus = null
    ): array {
        $sort = null;

        if ($sortField) {
            $sort = [
                sprintf('campaignPurchases.%s', $sortField) => [
                    'order' => strtolower($direction),
                    'ignore_unmapped' => true,
                ],
            ];
        }
        $deliveryStatusQuery = [];
        if (null !== $deliveryStatus) {
            $deliveryStatusQuery = [
                'must' => [
                    'term' => [
                        'campaignPurchases.deliveryStatus' => $deliveryStatus,
                    ],
                ],
            ];
        }

        if ($showCashback) {
            $innerHits = [
                'size' => $perPage,
                'from' => ($page - 1) * $perPage,
                'query' => [
                    'match_all' => [],
                ] + $deliveryStatusQuery,
            ];
        } else {
            $innerHits = [
                'size' => $perPage,
                'from' => ($page - 1) * $perPage,
                'query' => [
                    'query' => [
                        'bool' => [
                            'must_not' => [
                                [
                                    'term' => [
                                        'campaignPurchases.reward' => Campaign::REWARD_TYPE_CASHBACK,
                                    ],
                                ],
                            ],
                        ] + $deliveryStatusQuery,
                    ],
                ],
            ];
        }

        if ($sort) {
            $innerHits['sort'] = $sort;
        }

        $query = [
            'ids' => [
                'values' => [
                    (string) $customerId,
                ],
            ],
        ];

        $query = [
            'index' => $this->index,
            'body' => [
                'query' => $query,
                'inner_hits' => [
                    'nested_campaign_purchases' => [
                        'path' => ['campaignPurchases' => $innerHits],
                    ],
                ],
            ],
        ];

        try {
            $result = $this->client->search($query);
        } catch (Missing404Exception $e) {
            return [];
        } catch (BadRequest400Exception $e) {
            if (strpos($e->getMessage(), 'campaignPurchases') !== false) {
                return [];
            }

            throw $e;
        }

        if (!array_key_exists('hits', $result)) {
            return [];
        }
        if (!array_key_exists('hits', $result['hits'])) {
            return [];
        }
        if (count($result['hits']['hits']) == 0) {
            return [];
        }

        if (!array_key_exists('inner_hits', $result['hits']['hits'][0])) {
            return [];
        }

        if (!array_key_exists('nested_campaign_purchases', $result['hits']['hits'][0]['inner_hits'])) {
            return [];
        }
        if (!array_key_exists('_source', $result['hits']['hits'][0])) {
            return [];
        }

        if (!array_key_exists('hits', $result['hits']['hits'][0]['inner_hits']['nested_campaign_purchases'])) {
            return [];
        }
        $purchases = $result['hits']['hits'][0]['inner_hits']['nested_campaign_purchases']['hits']['hits'];
        $data = $this->serializer->deserialize(
            [
                'class' => $result['hits']['hits'][0]['_type'],
                'payload' => $result['hits']['hits'][0]['_source'],
            ]
        );
        $data = $data->serialize();
        $data['campaignPurchases'] = array_map(function ($purchase) {
            return $purchase['_source'];
        }, $purchases);

        /** @var CustomerDetails $result */
        $result = $this->serializer->deserialize([
            'class' => $result['hits']['hits'][0]['_type'],
            'payload' => $data,
        ]);

        if (!$result || $result == null) {
            return [];
        }

        return $result->getCampaignPurchases();
    }

    /**
     * @return CustomerDetails[]
     */
    public function findCustomersWithPurchasesToActivate(): array
    {
        $query = array(
            'index' => $this->index,
            'body' => [
                'query' => [
                    'nested' => [
                        'path' => 'campaignPurchases',
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [
                                        'match' => [
                                            'campaignPurchases.status' => CampaignPurchase::STATUS_INACTIVE,
                                        ],
                                    ],
                                ],
                                'must_not' => [
                                    [
                                        'match' => [
                                            'campaignPurchases.used' => true,
                                        ],
                                    ],
                                ],
                                'filter' => [
                                    'exists' => [
                                        'field' => 'campaignPurchases.activeSince',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        );

        return $this->searchAndDeserializeHits($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findCustomersWithPurchasesToExpire(): array
    {
        $query = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'nested' => [
                        'path' => 'campaignPurchases',
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [
                                        'match' => [
                                            'campaignPurchases.status' => CampaignPurchase::STATUS_ACTIVE,
                                        ],
                                    ],
                                ],
                                'must_not' => [
                                    [
                                        'match' => [
                                            'campaignPurchases.used' => true,
                                        ],
                                    ],
                                ],
                                'filter' => [
                                    'exists' => [
                                        'field' => 'campaignPurchases.activeTo',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $this->searchAndDeserializeHits($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findCustomersWithPurchasesExpiringAt(\DateTimeInterface $dateTime): array
    {
        $activeDateFrom = new \DateTimeImmutable($dateTime->format('Y-m-d'));
        $activeDateTo = $activeDateFrom->add(new \DateInterval('P1D'));

        $query = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'nested' => [
                        'path' => 'campaignPurchases',
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [
                                        'match' => [
                                            'campaignPurchases.status' => CampaignPurchase::STATUS_ACTIVE,
                                        ],
                                    ],
                                    [
                                        'range' => [
                                            'campaignPurchases.activeTo' => [
                                                'gte' => $activeDateFrom->getTimestamp(),
                                                'lte' => $activeDateTo->getTimestamp(),
                                            ],
                                        ],
                                    ],
                                ],
                                'must_not' => [
                                    [
                                        'match' => [
                                            'campaignPurchases.used' => true,
                                        ],
                                    ],
                                ],
                                'filter' => [
                                    'exists' => [
                                        'field' => 'campaignPurchases.activeTo',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $this->searchAndDeserializeHits($query);
    }

    /**
     * {@inheritdoc}
     */
    public function countPurchasesByCustomerId(CustomerId $customerId, $showCashback = false): int
    {
        $query = [
            'ids' => [
                'values' => [
                    (string) $customerId,
                ],
            ],
        ];

        $query = array(
            'index' => $this->index,
            'body' => array(
                'query' => $query,
                'aggregations' => [
                    'campaign_purchases' => [
                        'nested' => ['path' => 'campaignPurchases'],
                        'aggregations' => [
                            'campaign_purchases_count' => [
                                'sum' => ['field' => 'campaignPurchases.isNotCashback'],
                            ],
                        ],
                    ],
                ],
                'size' => 0,
            ),
        );

        try {
            $result = $this->client->search($query);
        } catch (Missing404Exception $e) {
            return 0;
        } catch (BadRequest400Exception $e) {
            if (strpos($e->getMessage(), 'campaignPurchases') !== false) {
                return 0;
            }

            throw $e;
        }

        if (!array_key_exists('aggregations', $result)) {
            return 0;
        }

        if (!array_key_exists('campaign_purchases', $result['aggregations'])) {
            return 0;
        }

        if (!array_key_exists('campaign_purchases_count', $result['aggregations']['campaign_purchases'])) {
            return 0;
        }

        if (!array_key_exists('value', $result['aggregations']['campaign_purchases']['campaign_purchases_count'])) {
            return 0;
        }

        return $result['aggregations']['campaign_purchases']['campaign_purchases_count']['value'];
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return array An array of timestamp ranges, closed from the left, open from the right: [from, to)
     */
    protected function getTimestamps(\DateTime $from, \DateTime $to): array
    {
        $date = clone $from;
        $now = clone $to;
        $date->setTime(0, 0, 0);
        $now->setTime(0, 0, 0);
        $timestamps = [];
        $timestamps[] = ['from' => $date->getTimestamp(), 'to' => $now->getTimestamp()];
        for ($i = 0; $i < 120; ++$i) {
            $date->modify('-1 year');
            $now->modify('-1 year');
            $timestamps[] = [
                'from' => $date->getTimestamp(),
                'to' => $now->getTimestamp(),
            ];
        }

        return $timestamps;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByCriteria($criteria, $limit): array
    {
        $filter = [];
        foreach ($criteria as $key => $value) {
            if ($key == 'id') {
                continue;
            }
            $filter[] = ['term' => [
                $key => $value,
            ]];
        }

        if (count($filter) > 0) {
            $query = [
                'bool' => [
                    'must' => $filter,
                ],
            ];

            if (isset($criteria['id'])) {
                $query['bool']['must'][]['ids'] = ['values' => [$criteria['id']]];
            }
        } else {
            $query = [
                'ids' => ['values' => [$criteria['id']]],
            ];
        }

        $result = $this->query($query);

        if (count($result) > $limit) {
            throw new TooManyResultsException();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findCustomersByParameters(array $fields, int $limit): array
    {
        if (empty($fields)) {
            return [];
        }

        $result = $this->findByParameters($fields, false);

        if (count($result) > $limit) {
            throw new TooManyResultsException();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findByAnyCriteria($criteria): array
    {
        $filter = [];
        foreach ($criteria as $key => $value) {
            if ($key == 'id') {
                continue;
            }
            $filter[] = ['term' => [
                $key => $value,
            ]];
        }

        if (count($filter) > 0) {
            $query = [
                'bool' => [
                    'should' => $filter,
                    'minimum_should_match' => 1,
                ],
            ];

            if (isset($criteria['id'])) {
                $query['bool']['should'][]['ids'] = ['values' => [$criteria['id']]];
            }
        } else {
            $query = [
                'ids' => ['values' => [$criteria['id']]],
            ];
        }

        $result = $this->query($query);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByPhone(string $phoneNumber, ?string $customerId = null): array
    {
        $query = [
            'bool' => [
                'should' => [
                    [
                        'term' => ['phone' => $phoneNumber],
                    ],
                    [
                        'term' => ['phone' => '+'.$phoneNumber],
                    ],
                ],
                'minimum_should_match' => 1,
            ],
        ];
        if (null !== $customerId) {
            $mustNot['bool'] = [
                'must_not' => [
                    'term' => [
                        'customerId' => $customerId,
                    ],
                ],
            ];
            $query = array_merge_recursive($query, $mustNot);
        }

        $result = $this->query($query);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllWithAverageTransactionAmountBetween($from, $to, $onlyActive = true): array
    {
        $filter = [
            [
                'range' => [
                    'averageTransactionAmount' => [
                        'gte' => floatval($from),
                        'lte' => floatval($to),
                    ],
                ],
            ],
        ];

        if ($onlyActive) {
            $filter[] = [
                'term' => [
                    'active' => true,
                ],
            ];
        }

        $query = [
            'filtered' => [
                'query' => [
                    'match_all' => [],
                ],
                'filter' => ['and' => $filter],
            ],
        ];

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllWithTransactionAmountBetween($from, $to, $onlyActive = true): array
    {
        $filter = [['range' => [
            'transactionsAmount' => [
                'gte' => floatval($from),
                'lte' => floatval($to),
            ],
        ]]];
        if ($onlyActive) {
            $filter[] = ['term' => [
                'active' => true,
            ]];
        }

        $query = array(
            'filtered' => array(
                'query' => array(
                    'match_all' => array(),
                ),
                'filter' => ['and' => $filter],
            ),
        );

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllWithTransactionCountBetween($from, $to, $onlyActive = true): array
    {
        $filter = [['range' => [
            'transactionsCount' => [
                'gte' => floatval($from),
                'lte' => floatval($to),
            ],
        ]]];
        if ($onlyActive) {
            $filter[] = ['term' => [
                'active' => true,
            ]];
        }

        $query = array(
            'filtered' => array(
                'query' => array(
                    'match_all' => array(),
                ),
                'filter' => ['and' => $filter],
            ),
        );

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function sumAllByField($fieldName): float
    {
        $allowedFields = [
            'transactionsCount',
            'transactionsAmount',
            'transactionsAmountWithoutDeliveryCosts',
        ];
        if (!in_array($fieldName, $allowedFields)) {
            throw new \InvalidArgumentException($fieldName.' is not allowed');
        }

        $query = array(
            'index' => $this->index,
            'body' => array(
                'aggregations' => [
                    'summary' => [
                        'sum' => ['field' => $fieldName],
                    ],
                ],
            ),
            'size' => 0,
        );

        try {
            $result = $this->client->search($query);
        } catch (Missing404Exception $e) {
            return 0;
        }

        if (!array_key_exists('aggregations', $result)) {
            return 0;
        }

        if (!array_key_exists('summary', $result['aggregations'])) {
            return 0;
        }

        if (!array_key_exists('value', $result['aggregations']['summary'])) {
            return 0;
        }

        return $result['aggregations']['summary']['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function findByLabels(array $labels, $active = null): array
    {
        if (count($labels) == 0) {
            return [];
        }
        $filter = [];
        foreach ($labels as $label) {
            $filter[] = ['bool' => ['must' => [
                ['term' => [
                    'labels.key' => strtolower($label['key']),
                ],
                ],
                ['term' => [
                    'labels.value' => strtolower($label['value']),
                ],
                ],
            ],
            ]];
        }

        $query = [
            'bool' => [
                'must' => [
                    [
                        'bool' => [
                            'should' => $filter,
                        ],
                    ],
                ],
            ],
        ];

        if (null !== $active) {
            $query['bool']['must'][]['term'] = ['active' => $active];
        }

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findWithLabels(array $labels, $active = null): array
    {
        if (count($labels) == 0) {
            return [];
        }
        $filter = [];
        foreach ($labels as $label) {
            $filter[] = ['bool' => ['must' => [
                ['term' => [
                    'labels.key' => strtolower($label['key']),
                ],
                ],
            ],
            ]];
        }

        $query = array(
            'bool' => array(
                'must' => [[
                    'bool' => [
                        'should' => $filter,
                    ],
                ]],
            ),
        );

        if (null !== $active) {
            $query['bool']['must'][]['term'] = ['active' => $active];
        }

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $customerIds): array
    {
        $query = array(
            'ids' => [
                'values' => $customerIds,
            ],
        );

        return $this->query($query);
    }
}
