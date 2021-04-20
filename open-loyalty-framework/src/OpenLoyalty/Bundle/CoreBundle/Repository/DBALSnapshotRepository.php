<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\CoreBundle\Repository;

use Broadway\Domain\DateTime;
use Broadway\Serializer\Serializer;
use Broadway\Snapshotting\Snapshot\Snapshot;
use Broadway\Snapshotting\Snapshot\SnapshotRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;

/**
 * Class SnapshotRepository.
 */
class DBALSnapshotRepository implements SnapshotRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Statement
     */
    private $loadStatement;

    /**
     * @var Serializer
     */
    private $payloadSerializer;

    /**
     * @var string
     */
    private $tableName;

    /**
     * DBALSnapshotRepository constructor.
     *
     * @param Connection $connection
     * @param Serializer $payloadSerializer
     * @param string     $tableName
     */
    public function __construct(Connection $connection, Serializer $payloadSerializer, string $tableName)
    {
        $this->connection = $connection;
        $this->payloadSerializer = $payloadSerializer;
        $this->tableName = $tableName;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id): ?Snapshot
    {
        $statement = $this->prepareLoadStatement();
        $statement->bindValue(1, $id);
        $statement->execute();

        $row = $statement->fetch();

        if (false == $row || null == $row) {
            return null;
        }

        $snapShot = $this->payloadSerializer->deserialize(json_decode($row['payload'], true));

        if (!$snapShot instanceof Snapshot) {
            return null;
        }

        return $snapShot;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Snapshot $snapshot): void
    {
        $recordedOn = DateTime::now();
        $data = [
            'uuid' => (string) $snapshot->getAggregateRoot()->getAggregateRootId(),
            'playhead' => $snapshot->getPlayhead(),
            'payload' => json_encode($this->payloadSerializer->serialize($snapshot)),
            'recorded_on' => $recordedOn->toString(),
        ];

        $this->connection->insert($this->tableName, $data);
    }

    /**
     * @return bool
     */
    public function createSchema(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();
        $schema = $schemaManager->createSchema();

        if ($schema->hasTable($this->tableName)) {
            return false;
        }

        $table = $schema->createTable($this->tableName);

        $uuidColumnDefinition = [
            'type' => 'guid',
            'params' => [
                'length' => 36,
            ],
        ];

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('uuid', $uuidColumnDefinition['type'], $uuidColumnDefinition['params']);
        $table->addColumn('playhead', 'integer', ['unsigned' => true]);
        $table->addColumn('payload', 'text');
        $table->addColumn('recorded_on', 'string', ['length' => 32]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['uuid', 'playhead']);

        $schemaManager->createTable($table);

        return true;
    }

    /**
     * @return bool
     */
    public function dropSchema(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();
        $schema = $schemaManager->createSchema();

        if (!$schema->hasTable($this->tableName)) {
            return false;
        }

        $schemaManager->dropTable($this->tableName);

        return true;
    }

    /**
     * @return Statement
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function prepareLoadStatement(): Statement
    {
        if (null === $this->loadStatement) {
            $query = sprintf('SELECT uuid, playhead, payload, recorded_on
                FROM %s
                WHERE uuid = ?
                ORDER BY playhead DESC LIMIT 1', $this->tableName);
            $this->loadStatement = $this->connection->prepare($query);
        }

        return $this->loadStatement;
    }
}
