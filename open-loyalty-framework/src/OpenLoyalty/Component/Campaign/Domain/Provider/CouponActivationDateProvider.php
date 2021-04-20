<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Provider;

use OpenLoyalty\Component\Campaign\Domain\Campaign;

/**
 * Class CouponActivationDateProvider.
 */
class CouponActivationDateProvider
{
    /**
     * @param Campaign  $campaign
     * @param \DateTime $createDate
     *
     * @return \DateTime
     */
    public function getActivationDate(Campaign $campaign, \DateTime $createDate): \DateTime
    {
        $activationDate = clone $createDate;
        $activationDate->modify(
            sprintf(
                '+%d days',
                $campaign->getDaysInactive()
            )
        );

        return $activationDate;
    }
}
