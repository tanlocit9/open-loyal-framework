<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategory;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryId;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryRepository;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortByFilter;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortFilter;

/**
 * Class DoctrineCampaignRepository.
 */
class DoctrineCampaignCategoryRepository extends EntityRepository implements CampaignCategoryRepository
{
    use SortFilter;
    use SortByFilter;

    /**
     * {@inheritdoc}
     */
    public function byId(CampaignCategoryId $campaignCategoryId): CampaignCategory
    {
        return parent::find($campaignCategoryId);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return parent::findBy([]);
    }

    /**
     * {@inheritdoc}
     */
    public function save(CampaignCategory $campaignCategory): void
    {
        $this->getEntityManager()->persist($campaignCategory);
        $campaignCategory->mergeNewTranslations();
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function countTotal(): int
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select('count(l.campaignCategoryId)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(CampaignCategory $campaignCategory): void
    {
        $this->getEntityManager()->remove($campaignCategory);
    }

    /**
     * @param array       $params
     * @param int         $page
     * @param int         $perPage
     * @param string|null $sortField
     * @param string      $direction
     *
     * @return CampaignCategory[]
     */
    public function findByParametersPaginated(
        array $params,
        int $page = 1,
        int $perPage = 10,
        ?string $sortField = null,
        string $direction = 'ASC'
    ): array {
        $qb = $this->getByParamsQueryBuilder($params);

        if ($sortField) {
            $qb->orderBy(
                'c.'.$this->validateSort($sortField),
                $this->validateSortBy($direction)
            );
        }

        $qb->setMaxResults($perPage);
        $qb->setFirstResult(($page - 1) * $perPage);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $params
     *
     * @return int
     */
    public function countFindByParameters(array $params): int
    {
        $qb = $this->getByParamsQueryBuilder($params);
        $qb->select('count(c.campaignCategoryId)');

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (ORMException $ex) {
            return 0;
        }
    }

    /**
     * @param array $params
     *
     * @return QueryBuilder
     */
    protected function getByParamsQueryBuilder(array $params): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        if (array_key_exists('active', $params) && !is_null($params['active'])) {
            $qb->andWhere('c.active = :active')->setParameter('active', (bool) $params['active']);
        }

        if (array_key_exists('name', $params) && !is_null($params['name'])) {
            $qb->join('c.translations', 't');
            $qb->andWhere($qb->expr()->like('t.name', ':name'))
                ->setParameter('name', '%'.urldecode($params['name']).'%')
                ->andWhere('t.locale = :locale')
                ->setParameter('locale', $params['_locale']);
        }

        return $qb;
    }
}
