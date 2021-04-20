<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\ActivationCode\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use OpenLoyalty\Component\ActivationCode\Domain\ActivationCodeId;
use OpenLoyalty\Component\ActivationCode\Domain\ActivationCode;
use OpenLoyalty\Component\ActivationCode\Domain\ActivationCodeRepositoryInterface;

/**
 * Class DoctrineActivationCodeRepository.
 */
class DoctrineActivationCodeRepository extends EntityRepository implements ActivationCodeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getById(ActivationCodeId $activationCodeId)
    {
        return $this->find($activationCodeId->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function getByCode($code)
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function save(ActivationCode $activationCode)
    {
        $this->getEntityManager()->persist($activationCode);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param string $objectType
     * @param string $objectId
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByObjectTypeAndObjectId(string $objectType, string $objectId)
    {
        $qb = $this->createQueryBuilder('activation_code');
        $qb->select('COUNT(activation_code.activationCodeId)');

        $qb->where('activation_code.objectType = :objectType');
        $qb->andWhere('activation_code.objectId = :objectId');
        $qb->setParameter('objectType', $objectType);
        $qb->setParameter('objectId', $objectId);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $objectType
     * @param string $objectId
     *
     * @return null|ActivationCode
     */
    public function getLastByObjectTypeAndObjectId(string $objectType, string $objectId)
    {
        return $this->findOneBy([
            'objectType' => $objectType,
            'objectId' => $objectId,
        ], ['createdAt' => 'DESC']);
    }
}
