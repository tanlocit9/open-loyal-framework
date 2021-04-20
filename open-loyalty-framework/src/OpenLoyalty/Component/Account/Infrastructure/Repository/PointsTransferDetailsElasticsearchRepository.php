<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Infrastructure\Repository;

use OpenLoyalty\Bundle\PaginationBundle\Model\Pagination;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetails;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetailsRepository;
use OpenLoyalty\Component\Core\Infrastructure\Repository\OloyElasticsearchRepository;

/**
 * Class PointsTransferDetailsRepository.
 */
class PointsTransferDetailsElasticsearchRepository extends OloyElasticsearchRepository implements PointsTransferDetailsRepository
{
    /**
     * {@inheritdoc}
     */
    public function findAllActiveAddingTransfersExpiredAfter(\DateTime $dateTime): array
    {
        $filter = [];
        $filter[] = [
            'term' => [
                'state' => PointsTransferDetails::STATE_ACTIVE,
            ],
        ];
        $filter[] = [
            'bool' => [
                'should' => [
                    [
                        'term' => [
                            'type' => PointsTransferDetails::TYPE_ADDING,
                        ],
                    ],
                    [
                        'term' => [
                            'type' => PointsTransferDetails::TYPE_P2P_ADDING,
                        ],
                    ],
                ],
            ],
        ];

        $filter[] = [
            'range' => [
                'expiresAt' => [
                    'lt' => $dateTime->getTimestamp(),
                ],
            ],
        ];

        $query = [
            'bool' => [
                'must' => $filter,
            ],
        ];

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllActiveAddingTransfersExpiredAt(\DateTimeInterface $dateTime): array
    {
        $filter = [];
        $filter[] = [
            'term' => [
                'state' => PointsTransferDetails::STATE_ACTIVE,
            ],
        ];
        $filter[] = [
            'bool' => [
                'should' => [
                    [
                        'term' => [
                            'type' => PointsTransferDetails::TYPE_ADDING,
                        ],
                    ],
                    [
                        'term' => [
                            'type' => PointsTransferDetails::TYPE_P2P_ADDING,
                        ],
                    ],
                ],
            ],
        ];

        $from = (new \DateTime())->setTimestamp(
            strtotime($dateTime->format('Y-m-d'))
        );
        $to = clone $from;
        $to->add(new \DateInterval('P1D'));

        $filter[] = [
            'range' => [
                'expiresAt' => [
                    'gte' => $from->getTimestamp(),
                    'lte' => $to->getTimestamp(),
                ],
            ],
        ];

        $query = [
            'bool' => [
                'must' => $filter,
            ],
        ];

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllPendingAddingTransfersToUnlock(\DateTime $dateTime): array
    {
        $filter = [];
        $filter[] = [
            'term' => [
                'state' => PointsTransferDetails::STATE_PENDING,
            ],
        ];
        $filter[] = [
            'term' => [
                'type' => PointsTransferDetails::TYPE_ADDING,
            ],
        ];

        $filter[] = [
            'range' => [
                'lockedUntil' => [
                    'lt' => $dateTime->getTimestamp(),
                ],
            ],
        ];

        $query = [
            'bool' => [
                'must' => $filter,
            ],
        ];

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllActiveAddingTransfersCreatedAfter(\DateTime $dateTime): array
    {
        $filter = [];
        $filter[] = ['term' => [
            'state' => PointsTransferDetails::STATE_ACTIVE,
        ]];
        $filter[] = ['term' => [
            'type' => PointsTransferDetails::TYPE_ADDING,
        ]];

        $filter[] = ['range' => [
            'createdAt' => [
                'lt' => $dateTime->getTimestamp(),
            ],
        ]];

        $query = [
            'bool' => [
                'must' => $filter,
            ],
        ];

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = 'pointsTransferId', $direction = 'DESC')
    {
        $query = [
            'filtered' => [
                'query' => [
                    'match_all' => [],
                ],
            ],
        ];

        return $this->query($query);
    }

    /**
     * {@inheritdoc}
     */
    public function findByParametersPaginatedAndFiltered(array $parameters, Pagination $pagination): array
    {
        return $this->findByParametersPaginated(
            $this->prepareParameters($parameters),
            true,
            $pagination->getPage(),
            $pagination->getPerPage(),
            $pagination->getSort(),
            $pagination->getSortDirection()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function countTotal(array $params = [], $exact = true): int
    {
        return parent::countTotal($this->prepareParameters($params), $exact);
    }

    /**
     * {@inheritdoc}
     */
    public function countTotalSpendingTransfers(): int
    {
        return $this->countTotal(['type' => 'spending']);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalValueOfSpendingTransfers(): int
    {
        $query = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'term' => ['type' => PointsTransferDetails::TYPE_SPENDING],
                        ],
                        'filter' => [
                            'not' => [
                                'term' => ['state' => PointsTransferDetails::STATE_CANCELED],
                            ],
                        ],
                    ],
                ],
                'aggregations' => [
                    'summary' => [
                        'sum' => ['field' => 'value'],
                    ],
                ],
            ],
            'size' => 0,
        ];

        $result = $this->client->search($query);

        return (int) $result['aggregations']['summary']['value'];
    }

    /**
     * {@inheritdoc}
     */
    private function prepareParameters(array $parameters): array
    {
        if (array_key_exists('state', $parameters) && is_array($parameters['state']) && !empty($parameters['state'])) {
            $states = $parameters['state'];

            unset($parameters['state']);

            $stateFilter = [
                'state' => [
                    'type' => 'match',
                    'value' => sprintf('[%s]', implode(',', $states)),
                ],
            ];
        }

        if (array_key_exists('willExpireTill', $parameters) && null !== $parameters['willExpireTill']) {
            $expiresAt = $parameters['willExpireTill'];

            unset($parameters['willExpireTill']);

            $expiresAtFilter = [
                'expiresAt' => [
                    'type' => 'range',
                    'value' => [
                        'lte' => (new \DateTime($expiresAt))->getTimestamp(),
                    ],
                ],
                'state' => [
                    'type' => 'match',
                    'value' => '[active]',
                ],
            ];
        }

        return array_merge($parameters, $stateFilter ?? [], $expiresAtFilter ?? []);
    }
}
