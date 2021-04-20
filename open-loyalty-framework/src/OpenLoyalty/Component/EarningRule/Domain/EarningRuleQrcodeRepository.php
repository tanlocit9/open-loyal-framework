<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Component\EarningRule\Domain;

/**
 * Interface EarningRuleQrcodeRepository.
 */
interface EarningRuleQrcodeRepository
{
    /**
     * @param string         $code
     * @param string|null    $earningRuleId
     * @param array          $segmentIds
     * @param string|null    $levelId
     * @param \DateTime|null $date
     * @param string|null    $posId
     *
     * @return array
     */
    public function findAllActiveQrcodeRules(
        string $code,
        ?string $earningRuleId,
        array $segmentIds = [],
        ?string $levelId = null,
        ?\DateTime $date = null,
        ?string $posId = null
    ): array;
}
