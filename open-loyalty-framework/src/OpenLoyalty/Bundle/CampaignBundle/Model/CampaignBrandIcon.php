<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use OpenLoyalty\Component\Campaign\Domain\Model\CampaignBrandIcon as DomainCampaignBrandIcon;

/**
 * Class CampaignBrandIcon.
 */
class CampaignBrandIcon extends DomainCampaignBrandIcon
{
    /**
     * @var UploadedFile|null
     * @Assert\NotBlank()
     * @Assert\Image(
     *     mimeTypes={"image/png", "image/gif", "image/jpeg"},
     *     maxSize="2M"
     * )
     */
    protected $file;

    /**
     * @return UploadedFile|null
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;
    }
}
