<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Domain\SystemEvent;

use OpenLoyalty\Component\Core\Infrastructure\FileInterface;

/**
 * Class PhotoSystemEvents.
 */
class PhotoSystemEvents
{
    const PHOTO_WAS_UPLOADED = 'oloy.photo.uploaded';
    const PHOTO_WAS_REMOVED = 'oloy.photo.removed';

    /**
     * @param FileInterface $file
     * @param string        $name
     *
     * @return PhotoUploadedSystemEvent
     */
    public function createPhotoUploadedEventInstance(FileInterface $file, string $name): PhotoUploadedSystemEvent
    {
        return new PhotoUploadedSystemEvent($file, $name);
    }

    /**
     * @param string $name
     *
     * @return PhotoRemovedSystemEvent
     */
    public function createPhotoRemovedEventInstance(string $name): PhotoRemovedSystemEvent
    {
        return new PhotoRemovedSystemEvent($name);
    }
}
