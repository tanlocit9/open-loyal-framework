<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\AuditBundle\Service;

use OpenLoyalty\Component\Customer\Domain\CustomerId;

/**
 * Interface AuditManagerInterface.
 */
interface AuditManagerInterface
{
    const VIEW_CUSTOMER_EVENT_TYPE = 'ViewCustomer';
    const AGREEMENTS_UPDATED_CUSTOMER_EVENT_TYPE = 'AgreementsUpdated';

    public function auditCustomerEvent($eventType, CustomerId $customerId, array $data);
}
