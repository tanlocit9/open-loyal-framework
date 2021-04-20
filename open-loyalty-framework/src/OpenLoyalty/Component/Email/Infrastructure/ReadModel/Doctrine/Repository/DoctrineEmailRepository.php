<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Email\Infrastructure\ReadModel\Doctrine\Repository;

use Doctrine\DBAL\Connection;
use OpenLoyalty\Component\Email\Domain\EmailId;
use OpenLoyalty\Component\Email\Domain\ReadModel\DoctrineEmailRepositoryInterface;
use OpenLoyalty\Component\Email\Domain\ReadModel\Email;

/**
 * Class DoctrineEmailRepository.
 */
class DoctrineEmailRepository implements DoctrineEmailRepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * DoctrineEmailRepository constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getById(EmailId $emailId)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('email.*')
                     ->from('ol__email', 'email')
                     ->where('email.email_id = :emailId')
                     ->setParameter('emailId', $emailId->__toString());

        $emailData = $this->connection->fetchAssoc(
            $queryBuilder->getSQL(),
            $queryBuilder->getParameters()
        );

        return $this->hydrate($emailData);
    }

    /**
     * {@inheritdoc}
     */
    public function getByKey($key): ?Email
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('email.*')
            ->from('ol__email', 'email')
            ->where('email.key = :key')
            ->setParameter('key', $key);

        $emailData = $this->connection->fetchAssoc(
            $queryBuilder->getSQL(),
            $queryBuilder->getParameters()
        );

        if (empty($emailData)) {
            return null;
        }

        return $this->hydrate($emailData);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('email.*')
                     ->from('ol__email', 'email');

        $emailsData = $this->connection->fetchAll(
            $queryBuilder->getSQL(),
            $queryBuilder->getParameters()
        );

        if (empty($emailsData)) {
            return [];
        }

        return array_map([$this, 'hydrate'], $emailsData);
    }

    /**
     * Hydrate array to object.
     *
     * @param array $emailData
     *
     * @return Email
     *
     * @throws \Exception
     */
    protected function hydrate(array $emailData): Email
    {
        return new Email(
            new EmailId($emailData['email_id']),
            $emailData['key'],
            $emailData['subject'],
            $emailData['content'],
            $emailData['sender_name'],
            $emailData['sender_email'],
            new \DateTime($emailData['updated_at']),
            $emailData['receiver_email'] ?? null
        );
    }
}
