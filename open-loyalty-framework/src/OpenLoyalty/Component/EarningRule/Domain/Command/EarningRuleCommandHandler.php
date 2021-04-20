<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Doctrine\ORM\OptimisticLockException;
use OpenLoyalty\Component\EarningRule\Domain\CustomEventEarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRule;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleRepository;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleUsage;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleUsageId;
use OpenLoyalty\Component\EarningRule\Domain\Exception\CustomEventEarningRuleAlreadyExistsException;
use OpenLoyalty\Component\EarningRule\Domain\Exception\EarningRuleDoesNotExistException;

/**
 * Class EarningRuleCommandHandler.
 */
class EarningRuleCommandHandler extends SimpleCommandHandler
{
    /**
     * @var EarningRuleRepository
     */
    protected $earningRuleRepository;

    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * EarningRuleCommandHandler constructor.
     *
     * @param EarningRuleRepository  $earningRuleRepository
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(EarningRuleRepository $earningRuleRepository, UuidGeneratorInterface $uuidGenerator)
    {
        $this->earningRuleRepository = $earningRuleRepository;
        $this->uuidGenerator = $uuidGenerator;
    }

    public function handleCreateEarningRule(CreateEarningRule $command)
    {
        $type = $command->getType();
        $class = EarningRule::TYPE_MAP[$type];
        $rule = new $class($command->getEarningRuleId(), $command->getEarningRuleData());

        if ($rule instanceof CustomEventEarningRule
            && $this->earningRuleRepository->isCustomEventEarningRuleExist($rule->getEventName())
        ) {
            throw new CustomEventEarningRuleAlreadyExistsException();
        }

        $this->earningRuleRepository->save($rule);
    }

    public function handleUpdateEarningRule(UpdateEarningRule $command)
    {
        $data = $command->getEarningRuleData();
        /** @var EarningRule $rule */
        $rule = $this->earningRuleRepository->byId($command->getEarningRuleId());
        $rule::validateRequiredData($data);
        $rule->setFromArray($data);

        if ($rule instanceof CustomEventEarningRule
            && $this->earningRuleRepository->isCustomEventEarningRuleExist($rule->getEventName(), $rule->getEarningRuleId())
        ) {
            throw new CustomEventEarningRuleAlreadyExistsException();
        }

        $this->earningRuleRepository->save($rule);
    }

    public function handleActivateEarningRule(ActivateEarningRule $command)
    {
        /** @var EarningRule $rule */
        $rule = $this->earningRuleRepository->byId($command->getEarningRuleId());
        $rule->setActive(true);
        $this->earningRuleRepository->save($rule);
    }

    public function handleDeactivateEarningRule(DeactivateEarningRule $command)
    {
        /** @var EarningRule $rule */
        $rule = $this->earningRuleRepository->byId($command->getEarningRuleId());
        $rule->setActive(false);
        $this->earningRuleRepository->save($rule);
    }

    public function handleUseCustomEventEarningRule(UseCustomEventEarningRule $command)
    {
        /** @var EarningRule $rule */
        $rule = $this->earningRuleRepository->byId($command->getEarningRuleId());
        $usage = new EarningRuleUsage(
            new EarningRuleUsageId($this->uuidGenerator->generate()),
            $command->getSubject(),
            $rule
        );
        $rule->addUsage($usage);
        $this->earningRuleRepository->save($rule);
    }

    /**
     * @param SetEarningRulePhoto $command
     *
     * @throws EarningRuleDoesNotExistException
     * @throws OptimisticLockException
     */
    public function handleSetEarningRulePhoto(SetEarningRulePhoto $command)
    {
        /** @var EarningRule $earningRule */
        $earningRule = $this->earningRuleRepository->byId($command->getEarningRuleId());
        if (is_null($earningRule)) {
            throw new EarningRuleDoesNotExistException();
        }

        $earningRule->setEarningRulePhoto($command->getEarningRulePhoto());

        $this->earningRuleRepository->save($earningRule);
    }

    /**
     * @param RemoveEarningRulePhoto $command
     *
     * @throws EarningRuleDoesNotExistException
     * @throws OptimisticLockException
     */
    public function handleRemoveEarningRulePhoto(RemoveEarningRulePhoto $command)
    {
        /** @var EarningRule $earningRule */
        $earningRule = $this->earningRuleRepository->byId($command->getEarningRuleId());

        if (is_null($earningRule)) {
            throw new EarningRuleDoesNotExistException();
        }

        $earningRule->removeEarningRulePhoto();
        $this->earningRuleRepository->save($earningRule);
    }
}
