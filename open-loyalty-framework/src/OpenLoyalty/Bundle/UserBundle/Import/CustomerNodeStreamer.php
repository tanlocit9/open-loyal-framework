<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Import;

use OpenLoyalty\Bundle\ImportBundle\Importer\XMLUniqueNodeStringFileStreamer;

/**
 * Class CustomerNodeStreamer.
 */
class CustomerNodeStreamer extends XMLUniqueNodeStringFileStreamer
{
    /**
     * XML node name.
     */
    const XML_NODE_NAME = 'customer';

    /**
     * TransactionNodeStreamer constructor.
     */
    public function __construct()
    {
        parent::__construct(self::XML_NODE_NAME);
    }
}
