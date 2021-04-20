<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Infrastructure\Repository;

use Broadway\ReadModel\RepositoryFactory;
use Broadway\ReadModel\Repository;
use Broadway\Serializer\Serializer;
use Elasticsearch\Client;

/**
 * Class OloyElasticsearchRepositoryFactory.
 */
class OloyElasticsearchRepositoryFactory implements RepositoryFactory
{
    private $client;
    private $serializer;
    private $maxResultWindowSize;

    public function __construct(Client $client, Serializer $serializer, $maxResultWindowSize = null)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->maxResultWindowSize = $maxResultWindowSize;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $name, string $class, $repositoryClass = null, array $notAnalyzedFields = array()): Repository
    {
        if ($repositoryClass != null) {
            $rClass = new \ReflectionClass($repositoryClass);

            if ($rClass->implementsInterface(Repository::class)) {
                $repo = new $repositoryClass($this->client, $this->serializer, $name, $class, $notAnalyzedFields);
                if ($repo instanceof OloyElasticsearchRepository && $this->maxResultWindowSize) {
                    $repo->setMaxResultWindowSize($this->maxResultWindowSize);
                }

                return $repo;
            }
        }

        $repo = new OloyElasticsearchRepository($this->client, $this->serializer, $name, $class, $notAnalyzedFields);
        if ($this->maxResultWindowSize) {
            $repo->setMaxResultWindowSize($this->maxResultWindowSize);
        }

        return $repo;
    }
}
