<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\WorldTextBundle\Service;

use OpenLoyalty\Bundle\WorldTextBundle\Lib\WorldTextSms;

/**
 * Class WorldTextClientFactory.
 */
class WorldTextClientFactory
{
    /**
     * @param string $id
     * @param string $apiKey
     *
     * @return WorldTextSms
     */
    public static function create(string $id, string $apiKey)
    {
        return WorldTextSms::CreateSmsInstance($id, $apiKey);
    }
}
