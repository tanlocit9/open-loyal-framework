<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Provider;

use OpenLoyalty\Component\Campaign\Domain\Campaign;

/**
 * Class CouponExpirationDateProvider.
 */
class CouponExpirationDateProvider
{
    /**
     * @param Campaign  $campaign
     * @param \DateTime $createDate
     *
     * @return \DateTime
     */
    public function getExpirationDate(Campaign $campaign, \DateTime $createDate): \DateTime
    {
        $expirationDate = clone $createDate;
        $expirationDate->modify(
            sprintf(
                '+%d days',
                $campaign->getDaysInactive() + $campaign->getDaysValid()
            )
        );

        return $expirationDate;
    }
}
