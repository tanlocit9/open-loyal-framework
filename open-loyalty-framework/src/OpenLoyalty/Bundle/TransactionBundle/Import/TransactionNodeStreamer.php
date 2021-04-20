<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Import;

use OpenLoyalty\Bundle\ImportBundle\Importer\XMLUniqueNodeStringFileStreamer;

/**
 * Class TransactionNodeStreamer.
 */
class TransactionNodeStreamer extends XMLUniqueNodeStringFileStreamer
{
    /**
     * XML node name.
     */
    const XML_NODE_NAME = 'transaction';

    /**
     * TransactionNodeStreamer constructor.
     */
    public function __construct()
    {
        parent::__construct(self::XML_NODE_NAME);
    }
}
