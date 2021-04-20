<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace OpenLoyalty\Component\Customer\Domain\Event;

use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Class CustomerAvatarWasRemoved.
 */
class CustomerAvatarWasRemoved extends CustomerEvent
{
    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data): CustomerAvatarWasRemoved
    {
        $event = new self(
            new CustomerId($data['customerId'])
        );

        return $event;
    }
}
