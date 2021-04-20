<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Level\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use Doctrine\ORM\OptimisticLockException;
use OpenLoyalty\Component\Level\Domain\Level;
use OpenLoyalty\Component\Level\Domain\LevelRepository;
use OpenLoyalty\Component\Level\Domain\LevelTranslation;
use OpenLoyalty\Component\Level\Domain\Model\Reward;
use OpenLoyalty\Component\Level\Domain\SpecialReward;
use OpenLoyalty\Component\Level\Domain\SpecialRewardId;
use OpenLoyalty\Component\Level\Domain\SpecialRewardRepository;

/**
 * Class LevelCommandHandler.
 */
class LevelCommandHandler extends SimpleCommandHandler
{
    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * @var SpecialRewardRepository
     */
    private $specialRewardRepository;

    /**
     * LevelCommandHandler constructor.
     *
     * @param LevelRepository         $levelRepository
     * @param SpecialRewardRepository $specialRewardRepository
     */
    public function __construct(LevelRepository $levelRepository, SpecialRewardRepository $specialRewardRepository)
    {
        $this->levelRepository = $levelRepository;
        $this->specialRewardRepository = $specialRewardRepository;
    }

    public function handleActivateLevel(ActivateLevel $command)
    {
        /** @var Level $level */
        $level = $this->levelRepository->byId($command->getLevelId());
        $level->setActive(true);
        $this->levelRepository->save($level);
    }

    public function handleDeactivateLevel(DeactivateLevel $command)
    {
        /** @var Level $level */
        $level = $this->levelRepository->byId($command->getLevelId());
        $level->setActive(false);
        $this->levelRepository->save($level);
    }

    /**
     * @param Level $level
     * @param array $data
     */
    protected function assignLevelTranslations(Level $level, array $data): void
    {
        if (!array_key_exists('translations', $data)) {
            return;
        }

        foreach ($data['translations'] as $locale => $transData) {
            if (array_key_exists('name', $transData)) {
                $level->translate($locale, false)->setName($transData['name']);
            }
            if (array_key_exists('description', $transData)) {
                $level->translate($locale, false)->setDescription($transData['description']);
            }
        }
        /** @var LevelTranslation $translation */
        foreach ($level->getTranslations() as $translation) {
            if (!isset($data['translations'][$translation->getLocale()])) {
                $level->removeTranslation($translation);
            }
        }
    }

    public function handleCreateLevel(CreateLevel $command)
    {
        $data = $command->getLevelData();
        $level = new Level($command->getLevelId(), $data['conditionValue']);
        $this->assignLevelTranslations($level, $data);

        if (isset($data['minOrder'])) {
            $level->setMinOrder($data['minOrder']);
        }

        $rewardData = $data['reward'];
        $level->setReward(new Reward($rewardData['name'], $rewardData['value'], $rewardData['code']));
        if (isset($data['specialRewards']) && is_array($data['specialRewards'])) {
            foreach ($data['specialRewards'] as $specialReward) {
                $newReward = new SpecialReward(
                    new SpecialRewardId($specialReward['id']),
                    $level,
                    $specialReward['name'],
                    $specialReward['value'],
                    $specialReward['code']
                );
                $newReward->setActive($specialReward['active']);
                $newReward->setStartAt(isset($specialReward['startAt']) ? $specialReward['startAt'] : null);
                $newReward->setEndAt(isset($specialReward['endAt']) ? $specialReward['endAt'] : null);

                $level->addSpecialReward($newReward);
            }
        }

        $this->levelRepository->save($level);
    }

    public function handleUpdateLevel(UpdateLevel $command)
    {
        /** @var Level $level */
        $level = $this->levelRepository->byId($command->getLevelId());
        $data = $command->getLevelData();
        $this->assignLevelTranslations($level, $data);

        $level->setConditionValue(isset($data['conditionValue']) ? $data['conditionValue'] : null);
        $rewardData = $data['reward'];
        $level->setReward(new Reward($rewardData['name'], $rewardData['value'], $rewardData['code']));
        $oldSpecialRewards = $level->getSpecialRewards();
        $newSpecialRewards = [];
        if (isset($data['minOrder'])) {
            $level->setMinOrder($data['minOrder']);
        }
        if (isset($data['specialRewards']) && is_array($data['specialRewards']) && count($data['specialRewards']) > 0) {
            foreach ($data['specialRewards'] as $key => $specialReward) {
                if (isset($oldSpecialRewards[$key]) && $oldSpecialRewards[$key] instanceof SpecialReward) {
                    /** @var SpecialReward $newReward */
                    $newReward = $oldSpecialRewards[$key];
                    $newReward->setName(isset($specialReward['name']) ? $specialReward['name'] : null);
                    $newReward->setValue(isset($specialReward['value']) ? $specialReward['value'] : null);
                    $newReward->setCode(isset($specialReward['code']) ? $specialReward['code'] : null);
                    unset($oldSpecialRewards[$key]);
                } else {
                    $newReward = new SpecialReward(
                        new SpecialRewardId($specialReward['id']),
                        $level,
                        $specialReward['name'],
                        $specialReward['value'],
                        $specialReward['code']
                    );
                }
                $newReward->setActive($specialReward['active']);
                $newReward->setStartAt(isset($specialReward['startAt']) ? $specialReward['startAt'] : null);
                $newReward->setEndAt(isset($specialReward['endAt']) ? $specialReward['endAt'] : null);

                $this->specialRewardRepository->save($newReward);

                $newSpecialRewards[] = $newReward;
            }
            $level->setSpecialRewards($newSpecialRewards);
            foreach ($oldSpecialRewards as $old) {
                $this->specialRewardRepository->remove($old);
            }
        } else {
            foreach ($level->getSpecialRewards() as $old) {
                $this->specialRewardRepository->remove($old);
            }
            $level->setSpecialRewards([]);
        }

        $this->levelRepository->save($level);
    }

    /**
     * @param SetLevelPhoto $command
     *
     * @throws OptimisticLockException
     */
    public function handleSetLevelPhoto(SetLevelPhoto $command)
    {
        /** @var Level $level */
        $level = $this->levelRepository->byId($command->getLevelId());
        $level->setPhoto($command->getLevelPhoto());

        $this->levelRepository->save($level);
    }

    /**
     * @param RemoveLevelPhoto $command
     *
     * @throws OptimisticLockException
     */
    public function handleRemoveLevelPhoto(RemoveLevelPhoto $command)
    {
        /** @var Level $level */
        $level = $this->levelRepository->byId($command->getLevelId());
        $level->removePhoto();
        $this->levelRepository->save($level);
    }
}
