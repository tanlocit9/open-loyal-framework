<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Audit\Domain\Command;

use OpenLoyalty\Component\Audit\Domain\AuditLogId;

/**
 * Class AuditCommand.
 */
abstract class AuditLogCommand
{
    /**
     * @var AuditLogId
     */
    protected $auditLogId;

    /**
     * AuditCommand constructor.
     *
     * @param AuditLogId $auditLogId
     */
    public function __construct(AuditLogId $auditLogId = null)
    {
        $this->auditLogId = $auditLogId;
    }

    /**
     * @return AuditLogId
     */
    public function getAuditLogId(): AuditLogId
    {
        return $this->auditLogId;
    }
}
