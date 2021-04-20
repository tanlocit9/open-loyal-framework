<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain;

use OpenLoyalty\Component\Core\Domain\Model\Identifier;

/**
 * Interface EarningRuleGeoRepository.
 */
interface EarningRuleGeoRepository
{
    /**
     * @param string|null            $earningRuleId
     * @param array                  $segmentIds
     * @param null                   $levelId
     * @param \DateTime|null         $date
     * @param string|null|Identifier $posId
     *
     * @return array
     */
    public function findGeoRules(string $earningRuleId = null, array $segmentIds = [], $levelId = null, \DateTime $date = null, $posId = null): array;

    /**
     * @param EarningRuleId $earningRuleId
     *
     * @return null|EarningRuleGeo
     */
    public function byId(EarningRuleId $earningRuleId): ?EarningRuleGeo;
}
