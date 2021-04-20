<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Audit\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Component\Audit\Domain\AuditLog;
use OpenLoyalty\Component\Audit\Domain\AuditLogId;
use OpenLoyalty\Component\Audit\Domain\AuditLogRepository;
use OpenLoyalty\Component\Audit\Domain\Event\AuditEvents;
use OpenLoyalty\Component\Audit\Domain\Event\AuditLogWasCreated;
use OpenLoyalty\Component\Audit\Domain\SystemEvent\AuditSystemEvents;
use OpenLoyalty\Component\Audit\Domain\SystemEvent\CreatedAuditLogSystemEvent;

/**
 * Class AuditLogHandler.
 */
class AuditLogHandler extends SimpleCommandHandler
{
    /**
     * @var AuditLogRepository
     */
    protected $auditLogRepository;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * AuditLogHandler constructor.
     *
     * @param AuditLogRepository     $auditLogRepository
     * @param UuidGeneratorInterface $uuidGenerator
     * @param EventDispatcher        $eventDispatcher
     */
    public function __construct(
        AuditLogRepository $auditLogRepository,
        UuidGeneratorInterface $uuidGenerator,
        EventDispatcher $eventDispatcher
    ) {
        $this->auditLogRepository = $auditLogRepository;
        $this->uuidGenerator = $uuidGenerator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param CreateAuditLog $command
     */
    public function handleCreateAuditLog(CreateAuditLog $command)
    {
        $auditData = $command->getAuditLogData();

        /** @var AuditLog $auditLog */
        $auditLog = new AuditLog(
            new AuditLogId($this->uuidGenerator->generate()),
            $auditData['eventType'],
            $auditData['entityType'],
            $auditData['entityId'],
            $auditData['createdAt'],
            $auditData['username'],
            $auditData['data']
        );
        $this->auditLogRepository->save($auditLog);

        $this->eventDispatcher->dispatch(
            AuditEvents::AUDIT_LOG_CREATED,
            [new AuditLogWasCreated($auditLog->getAuditLogId(), $auditData)]
        );

        $this->eventDispatcher->dispatch(
            AuditSystemEvents::AUDIT_LOG_CREATED,
            [new CreatedAuditLogSystemEvent($auditLog->getAuditLogId())]
        );
    }
}
