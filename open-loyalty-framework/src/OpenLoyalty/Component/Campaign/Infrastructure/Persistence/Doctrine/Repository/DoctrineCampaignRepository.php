<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use OpenLoyalty\Component\Campaign\Domain\LevelId;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\Functions\Cast;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortByFilter;
use OpenLoyalty\Component\Core\Infrastructure\Persistence\Doctrine\SortFilter;

/**
 * Class DoctrineCampaignRepository.
 */
class DoctrineCampaignRepository extends EntityRepository implements CampaignRepository
{
    use SortFilter;
    use SortByFilter;

    /**
     * {@inheritdoc}
     */
    public function findAll($returnQueryBuilder = false)
    {
        if ($returnQueryBuilder) {
            return $this->createQueryBuilder('e');
        }

        return parent::findAll();
    }

    /**
     * @param array       $params
     * @param string|null $sortField
     * @param string      $direction
     *
     * @return array
     *
     * @throws ORMException
     */
    public function findByParameters(
        array $params,
        $sortField = null,
        $direction = 'ASC'
    ) {
        $qb = $this->getCampaignsByParamsQueryBuilder($params);

        if ($sortField) {
            $qb->orderBy(
                'c.'.$this->validateSort($sortField),
                $this->validateSortBy($direction)
            );
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function byId(CampaignId $campaignId)
    {
        return parent::find($campaignId);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Campaign $campaign)
    {
        $this->getEntityManager()->persist($campaign);
        $campaign->mergeNewTranslations();
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(Campaign $campaign): void
    {
        $this->getEntityManager()->merge($campaign);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Campaign $campaign)
    {
        $this->getEntityManager()->remove($campaign);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllPaginated($page = 1, $perPage = 10, $sortField = null, $direction = 'ASC')
    {
        $qb = $this->createQueryBuilder('e');

        if ($sortField) {
            $qb->orderBy(
                'e.'.$this->validateSort($sortField),
                $this->validateSortBy($direction)
            );
        }

        $qb->setMaxResults($perPage);
        $qb->setFirstResult(($page - 1) * $perPage);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array       $params
     * @param int         $page
     * @param int         $perPage
     * @param string|null $sortField
     * @param string      $direction
     *
     * @return array
     *
     * @throws ORMException
     */
    public function findByParametersPaginated(
        array $params,
        $page = 1,
        $perPage = 10,
        $sortField = null,
        $direction = 'ASC'
    ) {
        $qb = $this->getCampaignsByParamsQueryBuilder($params);

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
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws ORMException
     */
    public function countFindByParameters(array $params)
    {
        $qb = $this->getCampaignsByParamsQueryBuilder($params);
        $qb->select('count(c.campaignId)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllVisiblePaginated($page = 1, $perPage = 10, $sortField = null, $direction = 'ASC', array $filters = [])
    {
        $queryBuilder = $this->createQueryBuilder('c');

        if ($sortField) {
            $queryBuilder->orderBy(
                'e.'.$this->validateSort($sortField),
                $this->validateSortBy($direction)
            );
        }

        if (array_key_exists('isPublic', $filters) && null !== $filters['isPublic']) {
            $queryBuilder->andWhere('c.public = :public')->setParameter('public', $filters['isPublic']);
        }

        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(
                'c.campaignVisibility.allTimeVisible = :true',
                $queryBuilder->expr()->andX(
                    'c.campaignVisibility.visibleFrom <= :now',
                    'c.campaignVisibility.visibleTo >= :now'
                )
            )
        );
        $queryBuilder->andWhere('c.reward != :cashback')->setParameter('cashback', Campaign::REWARD_TYPE_CASHBACK);

        $queryBuilder->andWhere('c.active = :true')->setParameter('true', true);
        $queryBuilder->setParameter('now', new \DateTime());

        $queryBuilder->setMaxResults($perPage);
        $queryBuilder->setFirstResult(($page - 1) * $perPage);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countTotal($onlyVisible = false, array $filters = [])
    {
        $query = $this->createQueryBuilder('e');

        $query->select('count(e.campaignId)');

        if (array_key_exists('isPublic', $filters) && null !== $filters['isPublic']) {
            $query
                ->andWhere('e.public = :public')
                ->setParameter('public', $filters['isPublic'])
            ;
        }

        if ($onlyVisible) {
            $query
                ->andWhere(
                    $query->expr()->orX(
                        'e.campaignVisibility.allTimeVisible = :true',
                        $query->expr()->andX(
                            'e.campaignVisibility.visibleFrom <= :now',
                            'e.campaignVisibility.visibleTo >= :now'
                        )
                    )
                )
                ->andWhere('e.reward != :cashback')
                ->andWhere('e.active = :true')->setParameter('true', true)
                ->setParameter('cashback', Campaign::REWARD_TYPE_CASHBACK)
                ->setParameter('now', new \DateTime())
            ;
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     *
     * @throws ORMException
     */
    public function getActiveCampaignsForLevelAndSegment(
        array $segmentIds = [],
        LevelId $levelId = null,
        array $categoryIds = [],
        $page = 1,
        $perPage = 10,
        $sortField = null,
        $direction = 'ASC'
    ): array {
        $queryBuilder = $this->getCampaignsForLevelAndSegmentQueryBuilder(
            $levelId,
            $segmentIds,
            $categoryIds,
            $page,
            $perPage,
            $sortField,
            $direction
        );

        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(
                'c.campaignActivity.allTimeActive = :true',
                $queryBuilder->expr()->andX(
                    'c.campaignActivity.activeFrom <= :now',
                    'c.campaignActivity.activeTo >= :now'
                )
            )
        );

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     *
     * @throws ORMException
     */
    public function getActiveCashbackCampaignsForLevelAndSegment(array $segmentIds = [], LevelId $levelId = null): array
    {
        $queryBuilder = $this->getCampaignsForLevelAndSegmentQueryBuilder($levelId, $segmentIds);
        $queryBuilder->andWhere('c.reward = :reward')->setParameter('reward', Campaign::REWARD_TYPE_CASHBACK);
        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(
                'c.campaignActivity.allTimeActive = :true',
                $queryBuilder->expr()->andX(
                    'c.campaignActivity.activeFrom <= :now',
                    'c.campaignActivity.activeTo >= :now'
                )
            )
        );

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     *
     * @throws ORMException
     */
    public function getVisibleCampaignsForLevelAndSegment(
        array $segmentIds = [],
        LevelId $levelId = null,
        array $categoryIds = [],
        $page = 1,
        $perPage = 10,
        $sortField = null,
        $direction = 'ASC',
        array $filters = []
    ): array {
        $queryBuilder = $this->getCampaignsForLevelAndSegmentQueryBuilder(
            $levelId,
            $segmentIds,
            $categoryIds,
            $page,
            $perPage,
            $sortField,
            $direction
        );

        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'c.campaignVisibility.allTimeVisible = :visible',
                    $queryBuilder->expr()->andX(
                        'c.campaignVisibility.visibleFrom <= :now',
                        'c.campaignVisibility.visibleTo >= :now'
                    )
                )
            )
            ->setParameter('visible', true)
        ;

        if (array_key_exists('featured', $filters) && null !== $filters['featured']) {
            $queryBuilder
                ->andWhere('c.featured = :featured')
                ->setParameter('featured', $filters['featured'])
            ;
        }

        if (array_key_exists('isPublic', $filters) && null !== $filters['isPublic']) {
            $queryBuilder
                ->andWhere('c.public = :public')
                ->setParameter('public', $filters['isPublic'])
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param array        $segmentIds
     * @param LevelId|null $levelId
     * @param array        $categoryIds
     * @param int          $page
     * @param int          $perPage
     * @param string|null  $sortField
     * @param string       $direction
     *
     * @return QueryBuilder
     *
     * @throws ORMException
     */
    protected function getCampaignsForLevelAndSegmentQueryBuilder(
        LevelId $levelId = null,
        array $segmentIds = [],
        array $categoryIds = [],
        ?int $page = 1,
        ?int $perPage = 10,
        ?string $sortField = null,
        ?string $direction = 'ASC'
    ): QueryBuilder {
        $this
            ->getEntityManager()
            ->getConfiguration()
            ->addCustomStringFunction('cast', Cast::class)
        ;

        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder
            ->andWhere('c.active = :active')
            ->setParameter('active', true)
            ->setParameter('now', new \DateTime())
        ;

        $levelOrSegment = $queryBuilder->expr()->orX();

        if (null !== $levelId) {
            $levelOrSegment->add($queryBuilder->expr()->like('cast(c.levels as text)', ':levelId'));

            $queryBuilder->setParameter('levelId', '%'.(string) $levelId.'%');
        }

        $i = 0;
        foreach ($segmentIds as $segmentId) {
            $levelOrSegment->add(
                $queryBuilder->expr()->like('cast(c.segments as text)', sprintf(':segmentId%d', $i))
            );

            $queryBuilder->setParameter(sprintf('segmentId%s', $i), '%'.(string) $segmentId.'%');

            ++$i;
        }

        $queryBuilder->andWhere($levelOrSegment);

        $this->updateQueryByCategoriesIds($queryBuilder, $categoryIds);

        if (null !== $sortField) {
            $queryBuilder->orderBy(
                'c.'.$this->validateSort($sortField),
                $this->validateSortBy($direction)
            );
        }

        $queryBuilder
            ->setMaxResults($perPage)
            ->setFirstResult(($page - 1) * $perPage)
        ;

        return $queryBuilder;
    }

    /**
     * @param array $params
     *
     * @return QueryBuilder
     *
     * @throws ORMException
     */
    protected function getCampaignsByParamsQueryBuilder(array $params): QueryBuilder
    {
        $this->getEntityManager()->getConfiguration()->addCustomStringFunction('cast', Cast::class);

        $builder = $this->createQueryBuilder('c');

        if (array_key_exists('labels', $params) && is_array($params['labels'])) {
            foreach ($params['labels'] as $label) {
                $searchLabel = '';
                if (array_key_exists('key', $label)) {
                    $searchLabel .= '"key":"'.$label['key'].'"';
                }
                if (array_key_exists('value', $label)) {
                    if (!empty($searchLabel)) {
                        $searchLabel .= ',';
                    }
                    $searchLabel .= '"value":"'.$label['value'].'"';
                }

                if (!empty($searchLabel)) {
                    $builder->andWhere($builder->expr()->like('cast(c.labels as text)', ':label'));
                    $builder->setParameter('label', '%'.$searchLabel.'%');
                }
            }
        }

        if (array_key_exists('active', $params) && null !== $params['active']) {
            $builder->andWhere('c.active = :value')
                    ->setParameter('value', filter_var($params['active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (array_key_exists('isPublic', $params) && null !== $params['isPublic']) {
            $builder->andWhere('c.public = :value')
                    ->setParameter('value', filter_var($params['isPublic'], FILTER_VALIDATE_BOOLEAN));
        }

        if (array_key_exists('isFeatured', $params) && null !== $params['isFeatured']) {
            $builder->andWhere('c.featured = :value')
                    ->setParameter('value', filter_var($params['isFeatured'], FILTER_VALIDATE_BOOLEAN));
        }

        if (array_key_exists('isFulfillmentTracking', $params) && null !== $params['isFulfillmentTracking']) {
            $builder->andWhere('c.fulfillmentTracking = :value')
                    ->setParameter('value', filter_var($params['isFulfillmentTracking'], FILTER_VALIDATE_BOOLEAN));
        }

        if (array_key_exists('campaignType', $params) && null !== $params['campaignType']) {
            $builder->andWhere('c.reward = :campaignType')->setParameter('campaignType', $params['campaignType']);
        }

        if (array_key_exists('name', $params) && null !== $params['name']) {
            $builder
                ->join('c.translations', 't')
                ->andWhere($builder->expr()->like('t.name', ':name'))
                ->andWhere('t.locale = :locale')
                ->setParameter('name', '%'.urldecode($params['name']).'%')
                ->setParameter('locale', $params['_locale'])
            ;
        }

        if (array_key_exists('categoryId', $params) && is_array($params['categoryId'])) {
            $this->updateQueryByCategoriesIds($builder, $params['categoryId']);
        }

        return $builder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $categoryIds
     */
    protected function updateQueryByCategoriesIds(QueryBuilder $queryBuilder, array $categoryIds): void
    {
        if (0 === count($categoryIds)) {
            return;
        }

        $categoriesOrX = $queryBuilder->expr()->orX();

        $i = 0;
        foreach ($categoryIds as $categoryId) {
            $categoriesOrX->add($queryBuilder->expr()->like('cast(c.categories as text)', ':categories'.$i));
            $queryBuilder->setParameter('categories'.$i, '%'.$categoryId.'%');

            ++$i;
        }

        $queryBuilder->andWhere($categoriesOrX);
    }

    /**
     * @return array
     */
    public function getActiveCampaignsWithPushNotificationText(): array
    {
        $builder = $this->createQueryBuilder('c');

        $builder
            ->andWhere('c.active = :active')
            ->setParameter('active', true)
        ;

        $builder
            ->andWhere('c.pushNotificationText != :null')->setParameter('null', serialize(null))
            ->andWhere('c.pushNotificationText != :empty')->setParameter('empty', serialize([]))
        ;

        return $builder->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getActiveCampaigns(): array
    {
        $builder = $this->createQueryBuilder('c');

        $builder
            ->andWhere('c.active = :active')
            ->setParameter('active', true)
        ;

        return $builder->getQuery()->getResult();
    }
}
