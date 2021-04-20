<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Command;

use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class AddPhotoCommand.
 */
class AddPhotoCommand
{
    /**
     * @var array
     */
    private $file;

    /**
     * @var string
     */
    private $campaignId;

    /**
     * AddPhotoCommand constructor.
     *
     * @param array  $file
     * @param string $campaignId
     */
    public function __construct(array $file, string $campaignId)
    {
        $this->file = $file;
        $this->campaignId = $campaignId;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param CampaignId   $campaignId
     *
     * @return AddPhotoCommand
     */
    public static function withData(UploadedFile $uploadedFile, CampaignId $campaignId): self
    {
        $file = [
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getClientMimeType(),
            'path' => $uploadedFile->getPath(),
            'extension' => $uploadedFile->guessExtension(),
            'real_path' => $uploadedFile->getRealPath(),
        ];

        return new self($file, (string) $campaignId);
    }

    /**
     * @return array
     */
    public function getFile(): array
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getCampaignId(): string
    {
        return $this->campaignId;
    }
}
