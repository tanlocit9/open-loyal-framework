<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\EarningRuleBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use OpenLoyalty\Bundle\EarningRuleBundle\Validator\Constraints as CampaignAssert;
use OpenLoyalty\Component\EarningRule\Domain\Model\EarningRulePhoto as DomainEarningRulePhoto;

/**
 * Class EarningRulePhoto.
 */
class EarningRulePhoto extends DomainEarningRulePhoto
{
    /**
     * @var UploadedFile
     * @Assert\NotBlank()
     * @CampaignAssert\Image(
     *     mimeTypes={"image/png", "image/gif", "image/jpeg"},
     *     maxSize="2M"
     * )
     */
    protected $file;

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     *
     * @return EarningRulePhoto
     */
    public function setFile(UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }
}
