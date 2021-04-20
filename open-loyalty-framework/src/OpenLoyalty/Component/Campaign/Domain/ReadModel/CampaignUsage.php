<?php
/*
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\ReadModel;

use Broadway\ReadModel\SerializableReadModel;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Core\Domain\ReadModel\Versionable;
use OpenLoyalty\Component\Core\Domain\ReadModel\VersionableReadModel;

/**
 * Class CampaignUsage.
 */
class CampaignUsage implements SerializableReadModel, VersionableReadModel
{
    use Versionable;

    /**
     * @var CampaignId
     */
    protected $campaignId;

    /**
     * @var int
     */
    protected $campaignUsage = 0;

    /**
     * CampaignUsage constructor.
     *
     * @param CampaignId $campaignId
     */
    public function __construct(CampaignId $campaignId)
    {
        $this->campaignId = $campaignId;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->campaignId;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        $tmp = new self(new CampaignId($data['campaignId']));
        if (isset($data['usage'])) {
            $tmp->setCampaignUsage($data['usage']);
        }

        return $tmp;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return [
            'campaignId' => (string) $this->campaignId,
            'usage' => $this->campaignUsage,
        ];
    }

    /**
     * @return CampaignId
     */
    public function getCampaignId(): CampaignId
    {
        return $this->campaignId;
    }

    /**
     * @return int
     */
    public function getCampaignUsage(): int
    {
        return $this->campaignUsage;
    }

    /**
     * @param int $campaignUsage
     */
    public function setCampaignUsage(int $campaignUsage): void
    {
        $this->campaignUsage = $campaignUsage;
    }
}
