<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Import;

use OpenLoyalty\Bundle\ImportBundle\Importer\XMLUniqueNodeStringFileStreamer;

/**
 * Class PointsTransferNodeStreamer.
 */
class PointsTransferNodeStreamer extends XMLUniqueNodeStringFileStreamer
{
    /**
     * XML node name.
     */
    const XML_NODE_NAME = 'pointsTransfer';

    /**
     * PointsTransferNodeStreamer constructor.
     */
    public function __construct()
    {
        parent::__construct(self::XML_NODE_NAME);
    }
}
