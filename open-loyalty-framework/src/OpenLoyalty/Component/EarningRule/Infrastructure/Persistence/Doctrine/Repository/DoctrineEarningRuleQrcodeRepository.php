<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\EarningRule\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleQrcodeRepository;

/**
 * Class DoctrineEarningRuleQrcodeRepository.
 */
class DoctrineEarningRuleQrcodeRepository extends EntityRepository implements EarningRuleQrcodeRepository
{
    use DoctrineEarningRuleRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function findAllActiveQrcodeRules(
        string $code,
        ?string $earningRuleId,
        array $segmentIds = [],
        ?string $levelId = null,
        ?\DateTime $date = null,
        string $posId = null
    ): array {
        $queryBuilder = $this->getEarningRulesForLevelAndSegmentQueryBuilder($segmentIds, $levelId, $date, $posId);

        if ($code) {
            $queryBuilder->andWhere('e.code = :code')->setParameter('code', $code);
        }
        if ($earningRuleId) {
            $queryBuilder->andWhere('e.earningRuleId = :earningRuleId')->setParameter('earningRuleId', $earningRuleId);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
