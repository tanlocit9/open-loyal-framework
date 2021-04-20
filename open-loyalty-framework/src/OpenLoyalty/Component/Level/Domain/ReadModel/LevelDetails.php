<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Level\Domain\ReadModel;

use Broadway\ReadModel\SerializableReadModel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use OpenLoyalty\Component\Level\Domain\LevelId;
use OpenLoyalty\Component\Level\Domain\LevelTranslation;

/**
 * Class LevelDetails.
 */
class LevelDetails implements SerializableReadModel
{
    /**
     * @var LevelId
     */
    protected $levelId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $translations = [];

    /**
     * LevelDetails constructor.
     *
     * @param LevelId $id
     */
    public function __construct(LevelId $id)
    {
        $this->levelId = $id;
    }

    /**
     * @param array $data
     *
     * @return LevelDetails The object instance
     */
    public static function deserialize(array $data)
    {
        $level = new self(new LevelId($data['id']));
        if (!empty($data['name'])) {
            $level->setName($data['name']);
        }
        if (!empty($data['translations'])) {
            $level->translations = $data['translations'];
        }

        return $level;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'id' => (string) $this->getLevelId(),
            'name' => $this->getName(),
            'translations' => $this->getTranslations() ?? [],
        ];
    }

    /**
     * @return LevelId
     */
    public function getLevelId(): LevelId
    {
        return $this->levelId;
    }

    /**
     * @param LevelId $levelId
     */
    public function setLevelId(LevelId $levelId)
    {
        $this->levelId = $levelId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * @param array|ArrayCollection $translations
     */
    public function setTranslations(iterable $translations): void
    {
        if ($translations instanceof Collection) {
            $translations = $translations->toArray();
        }

        $this->translations = array_map(
            function (LevelTranslation $level): array {
                return [
                    'name' => $level->getName(),
                ];
            },
            $translations
        );
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->getLevelId();
    }
}
