<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Infrastructure\Repository;

use Broadway\ReadModel\ElasticSearch\ElasticSearchRepository;
use Broadway\Serializer\Serializer;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use OpenLoyalty\Component\Core\Domain\ReadModel\VersionableReadModel;
use Webmozart\Assert\Assert;
use Assert\Assertion;
use Broadway\ReadModel\Identifiable;

/**
 * Class OloyElasticsearchRepository.
 */
class OloyElasticsearchRepository extends ElasticSearchRepository
{
    /** @var int */
    private const MAX_QUERY_SIZE = 500;

    /** @var Client */
    protected $client;

    /** @var Serializer */
    protected $serializer;

    /** @var string */
    protected $index;

    /** @var string */
    protected $class;

    /** @var array */
    protected $notAnalyzedFields;

    /** @var array */
    protected $dynamicFields = [];

    /** @var int */
    private $maxResultWindowSize = 10000;

    /**
     * @param Client     $client
     * @param Serializer $serializer
     * @param string     $index
     * @param string     $class
     * @param array      $notAnalyzedFields
     */
    public function __construct(
        Client $client,
        Serializer $serializer,
        $index,
        $class,
        array $notAnalyzedFields = []
    ) {
        parent::__construct($client, $serializer, $index, $class, $notAnalyzedFields);

        $this->client = $client;
        $this->serializer = $serializer;
        $this->index = $index;
        $this->class = $class;
        $this->notAnalyzedFields = $notAnalyzedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Identifiable $data)
    {
        Assertion::isInstanceOf($data, $this->class);

        $serializedReadModel = $this->serializer->serialize($data);

        $params = [
            'index' => $this->index,
            'type' => $serializedReadModel['class'],
            'id' => $data->getId(),
            'body' => $serializedReadModel['payload'],
            'refresh' => true,
        ];

        if ($data instanceof VersionableReadModel) {
            $params['version'] = $serializedReadModel['version'];
        }

        $this->client->index($params);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $params = [
            'index' => $this->index,
            'type' => $this->class,
            'id' => $id,
        ];

        try {
            $result = $this->client->get($params);
        } catch (Missing404Exception $e) {
            return null;
        }

        return $this->deserializeHit($result);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $fields): array
    {
        if (empty($fields)) {
            return [];
        }

        return $this->query($this->buildFindByQuery($fields));
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return $this->query($this->buildFindAllQuery());
    }

    /**
     * @return array
     */
    private function buildFindAllQuery(): array
    {
        return [
            'match_all' => new \stdClass(),
        ];
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    private function buildFindByQuery(array $fields): array
    {
        return [
            'bool' => [
                'must' => $this->buildFilter($fields),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createIndex(): bool
    {
        $class = $this->class;

        $indexParams = ['index' => $this->index];

        if (count($this->notAnalyzedFields)) {
            $indexParams['body']['mappings']['properties'] = $this->createNotAnalyzedFieldsMapping($this->notAnalyzedFields);
        }

        $defaultDynamicFields = [
            [
                'email' => [
                    'match' => 'email',
                    'match_mapping_type' => 'string',
                    'mapping' => [
                        'type' => 'string',
                        'analyzer' => 'email',
                    ],
                ],
            ],
            [
                'someemail' => [
                    'match' => '*Email',
                    'match_mapping_type' => 'string',
                    'mapping' => [
                        'type' => 'string',
                        'analyzer' => 'email',
                    ],
                ],
            ],
            [
                'notanalyzed' => [
                    'match' => '*Id',
                    'match_mapping_type' => 'string',
                    'mapping' => [
                        'type' => 'string',
                        'index' => 'not_analyzed',
                    ],
                ],
            ],
            [
                'loyaltyCard' => [
                    'match' => 'loyaltyCardNumber',
                    'match_mapping_type' => 'string',
                    'mapping' => [
                        'type' => 'string',
                        'index' => 'not_analyzed',
                    ],
                ],
            ],
            [
                'postal' => [
                    'match' => 'postal',
                    'match_mapping_type' => 'string',
                    'mapping' => [
                        'type' => 'string',
                        'index' => 'not_analyzed',
                    ],
                ],
            ],
            [
                'phone' => [
                    'match' => 'phone',
                    'match_mapping_type' => 'string',
                    'mapping' => [
                        'type' => 'string',
                        'index' => 'not_analyzed',
                    ],
                ],
            ],
        ];

        $indexParams['body'] = [
            'settings' => [
                'analysis' => [
                    'analyzer' => [
                        'email' => [
                            'tokenizer' => 'uax_url_email',
                            'filter' => ['lowercase'],
                        ],
                        'small_letters' => [
                            'tokenizer' => 'keyword',
                            'filter' => ['lowercase'],
                        ],
                    ],
                    'filter' => [
                        'translation' => [
                            'type' => 'nGram',
                            'min_gram' => 2,
                            'max_gram' => 100,
                        ],
                    ],
                ],
            ],
            'mappings' => [
                $class => [
                    '_source' => [
                        'enabled' => true,
                    ],
                    'dynamic_templates' => array_merge($this->dynamicFields, $defaultDynamicFields),
                ],
            ],
        ];

        $this->client->indices()->create($indexParams);

        $response = $this->client
            ->cluster()
            ->health([
                'index' => $this->index,
                'wait_for_status' => 'yellow',
                'timeout' => '5s',
            ])
        ;

        return isset($response['status']) && 'red' !== $response['status'];
    }

    /**
     * Deletes the index for this repository's ReadModel.
     *
     * @return True, if the index was successfully deleted
     */
    public function deleteIndex(): bool
    {
        $indexParams = [
            'index' => $this->index,
            'timeout' => '5s',
        ];

        $this->client->indices()->delete($indexParams);

        return true;
    }

    /**
     * @param array $params
     * @param bool  $exact
     *
     * @return array
     */
    public function findByParameters(array $params, $exact = true): array
    {
        $filter = $this->buildFilter($params, $exact);

        $query = [
            'bool' => [
                'must' => $filter,
            ],
        ];

        return $this->query($query);
    }

    /**
     * @param array  $params
     * @param bool   $exact
     * @param int    $page
     * @param int    $perPage
     * @param null   $sortField
     * @param string $direction
     *
     * @return array
     */
    public function findByParametersPaginated(
        array $params,
        $exact = true,
        $page = 1,
        $perPage = 10,
        $sortField = null,
        $direction = 'DESC'
    ): array {
        if ($page < 1) {
            $page = 1;
        }

        $filter = $this->buildFilter($params, $exact);

        if ($sortField) {
            $sort = [
                $sortField => ['order' => strtolower($direction), 'ignore_unmapped' => true],
            ];
        } else {
            $sort = null;
        }

        if (count($filter) > 0) {
            $query = [
                'bool' => [
                    'must' => $filter,
                ],
            ];
        } else {
            $query = [
                'filtered' => [
                    'query' => [
                        'match_all' => [],
                    ],
                ],
            ];
        }

        $startPage = ($perPage === null ? null : ($page - 1) * $perPage);

        return $this->paginatedQuery($query, $startPage, $perPage, $sort);
    }

    /**
     * @param array $params
     * @param bool  $exact
     *
     * @return int
     */
    public function countTotal(array $params = [], $exact = true): int
    {
        $filter = $this->buildFilter($params, $exact);

        if (count($filter) > 0) {
            $query = [
                'bool' => [
                    'must' => $filter,
                ],
            ];
        } else {
            $query = [
                'filtered' => [
                    'query' => [
                        'match_all' => [],
                    ],
                ],
            ];
        }

        return $this->count($query);
    }

    /**
     * @return int
     */
    public function getMaxResultWindowSize(): int
    {
        return $this->maxResultWindowSize;
    }

    /**
     * @param int $maxResultWindowSize
     */
    public function setMaxResultWindowSize(int $maxResultWindowSize): void
    {
        $this->maxResultWindowSize = $maxResultWindowSize;
    }

    /**
     * @param array      $query
     * @param int|null   $from
     * @param int|null   $size
     * @param array|null $sort
     *
     * @return array
     */
    protected function paginatedQuery(array $query, ?int $from = 0, ?int $size = self::MAX_QUERY_SIZE, ?array $sort = null): array
    {
        $query = [
            'index' => $this->index,
            'body' => [
                'query' => $query,
                'size' => null === $size ? $this->getMaxResultWindowSize() : $size,
                'from' => $from,
            ],
        ];

        if ($sort) {
            $query['body']['sort'] = $sort;
        }

        return $this->searchAndDeserializeHits($query);
    }

    /**
     * @param array $query
     *
     * @return array
     */
    protected function searchAndDeserializeHits(array $query): array
    {
        try {
            $result = $this->client->search($query);

            Assert::keyExists($result, 'hits');
        } catch (Missing404Exception | \InvalidArgumentException $exception) {
            return [];
        }

        return $this->deserializeHits($result['hits']['hits']);
    }

    /**
     * @param array $hits
     *
     * @return array
     */
    protected function deserializeHits(array $hits): array
    {
        return array_map([$this, 'deserializeHit'], $hits);
    }

    /**
     * @param array $query
     *
     * @return int
     */
    protected function count(array $query): int
    {
        $query = [
            'index' => $this->index,
            'body' => [
                'query' => $query,
            ],
        ];

        try {
            $result = $this->client->count($query);

            Assert::keyExists($result, 'count');
        } catch (Missing404Exception | \InvalidArgumentException $exception) {
            return 0;
        }

        return $result['count'];
    }

    /**
     * {@inheritdoc}
     */
    protected function search(array $query, array $facets = [], int $size = self::MAX_QUERY_SIZE): array
    {
        if (null === $size) {
            $size = $this->getMaxResultWindowSize();
        }

        try {
            return $this->client->search([
                'index' => $this->index,
                'body' => [
                    'query' => $query,
                    'facets' => $facets,
                ],
                'size' => $size,
            ]);
        } catch (Missing404Exception $e) {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function query(array $query): array
    {
        return $this->searchAndDeserializeHits(
            [
                'index' => $this->index,
                'body' => [
                    'query' => $query,
                ],
                'size' => $this->getMaxResultWindowSize(),
            ]
        );
    }

    /**
     * @param array $hit
     *
     * @return mixed
     */
    private function deserializeHit(array $hit)
    {
        $data = [
            'class' => $hit['_type'],
            'payload' => $hit['_source'],
        ];

        if (array_key_exists('_version', $hit)) {
            $data['version'] = $hit['_version'];
        }

        return $this->serializer->deserialize($data);
    }

    /**
     * @param array $notAnalyzedFields
     *
     * @return array
     */
    private function createNotAnalyzedFieldsMapping(array $notAnalyzedFields): array
    {
        $fields = [];

        foreach ($notAnalyzedFields as $field) {
            $fields[$field] = [
                'type' => 'string',
                'index' => 'not_analyzed',
            ];
        }

        return $fields;
    }

    /**
     * @param array $params
     * @param bool  $exact
     *
     * @return array
     */
    private function buildFilter(array $params = [], bool $exact = true): array
    {
        $filter = [];

        foreach ($params as $key => $value) {
            if (null === $value) {
                continue;
            }

            if (is_array($value) && isset($value['type'])) {
                if ('number' === $value['type']) {
                    $filter[] = [
                        'term' => [
                            $key => (float) $value['value'],
                        ],
                    ];
                } elseif ('range' === $value['type']) {
                    $filter[] = [
                        'range' => [
                            $key => $value['value'],
                        ],
                    ];
                } elseif ('exists' === $value['type']) {
                    $filter[] = [
                        'exists' => [
                            'field' => $key,
                        ],
                    ];
                } elseif ('exact' === $value['type']) {
                    $filter[] = [
                        'term' => [
                            $key => $value['value'],
                        ],
                    ];
                } elseif ('match' === $value['type']) {
                    $filter[] = [
                        'match' => [
                            $key => $value['value'],
                        ],
                    ];
                } elseif ('allow_null' === $value['type']) {
                    $filter[] = [
                        'bool' => [
                            'should' => [
                                ['term' => [$key => $value['value']]],
                                ['missing' => ['field' => $key]],
                            ],
                        ],
                    ];
                } elseif ('multiple' === $value['type']) {
                    $bool = ['should' => [], 'minimum_should_match' => 1];
                    foreach ($value['fields'] as $k => $v) {
                        if (!$exact) {
                            $bool['should'][] = ['wildcard' => [$k => sprintf('*%s*', $v)]];
                        } else {
                            $bool['should'][] = ['term' => [$k => sprintf('%s', str_replace('\\', '', $v))]];
                        }
                    }
                    $filter[] = ['bool' => $bool];
                } elseif ('multiple_all' === $value['type']) {
                    $bool = ['must' => []];
                    foreach ($value['fields'] as $k => $v) {
                        if (!isset($value['exact']) || !$value['exact']) {
                            $bool['must'][] = ['wildcard' => [$k => sprintf('*%s*', $v)]];
                        } else {
                            $bool['must'][] = ['term' => [$k => $v]];
                        }
                    }
                    $filter[] = ['bool' => $bool];
                } elseif ('terms' === $value['type']) {
                    $filter[] = [
                        'terms' => [
                            $key => $value['value'],
                        ],
                    ];
                }
            } elseif (!$exact) {
                $filter[] = [
                    'wildcard' => [
                        $key => sprintf('*%s*', $value),
                    ],
                ];
            } else {
                $filter[] = [
                    'term' => [
                        // term must not contain escaping chars as it search exact values
                        $key => str_replace('\\', '', $value),
                    ],
                ];
            }
        }

        return $filter;
    }
}
