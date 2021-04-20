<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Translation\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use OpenLoyalty\Component\Translation\Domain\Language;
use OpenLoyalty\Component\Translation\Domain\LanguageId;
use OpenLoyalty\Component\Translation\Domain\LanguageRepository;

/**
 * Class DoctrineLanguageRepository.
 */
class DoctrineLanguageRepository extends EntityRepository implements LanguageRepository
{
    /**
     * {@inheritdoc}
     */
    public function byId(LanguageId $languageId): Language
    {
        return parent::find($languageId);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return parent::findBy([], ['order' => 'asc']);
    }

    /**
     * {@inheritdoc}
     */
    public function byCode(string $code): ?Language
    {
        return parent::findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault(): ?Language
    {
        return parent::findOneBy(['default' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Language $language): void
    {
        if ($language->isDefault()) {
            $query = $this->createQueryBuilder('u')
                ->update()
                ->set('u.default', '?1')
                ->where('u.languageId <> ?2')
                ->setParameter(2, (string) $language->getLanguageId())
                ->setParameter(1, 'false')
                ->getQuery();

            $query->execute();
        }

        $this->getEntityManager()->persist($language);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Language $language): void
    {
        $this->getEntityManager()->remove($language);
        $this->getEntityManager()->flush();
    }
}
