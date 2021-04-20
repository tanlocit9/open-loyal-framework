<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Core\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\EventDispatcher\EventDispatcher;
use OpenLoyalty\Bundle\SettingsBundle\Entity\FileSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Entity\SettingsEntry;
use OpenLoyalty\Bundle\SettingsBundle\Model\Logo;
use OpenLoyalty\Bundle\SettingsBundle\Service\LogoUploader;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Component\Core\Domain\Exception\InvalidPhotoNameException;
use OpenLoyalty\Component\Core\Domain\SystemEvent\PhotoSystemEvents;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PhotoCommandHandler.
 */
class PhotoCommandHandler extends SimpleCommandHandler
{
    /**
     * @var EventDispatcher|null
     */
    private $eventDispatcher;

    /**
     * @var PhotoSystemEvents
     */
    private $photoSystemEvents;

    /**
     * @var LogoUploader
     */
    private $uploader;

    /**
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * PhotoCommandHandler constructor.
     *
     * @param LogoUploader         $uploader
     * @param SettingsManager      $settingsManager
     * @param PhotoSystemEvents    $events
     * @param TranslatorInterface  $translator
     * @param EventDispatcher|null $eventDispatcher
     */
    public function __construct(
        LogoUploader $uploader,
        SettingsManager $settingsManager,
        PhotoSystemEvents $events,
        TranslatorInterface $translator,
        EventDispatcher $eventDispatcher = null
    ) {
        $this->uploader = $uploader;
        $this->settingsManager = $settingsManager;
        $this->photoSystemEvents = $events;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param UploadPhoto $command
     *
     * @throws \Exception
     * @throws InvalidPhotoNameException
     */
    public function handleUploadPhoto(UploadPhoto $command)
    {
        $name = $command->getName();

        $this->throwExceptionIfInvalidPhotoName($name);

        $file = $command->getFile();

        $settings = $this->settingsManager->getSettings();
        $entry = $settings->getEntry($name);
        if ($entry instanceof SettingsEntry) {
            $this->uploader->remove($entry->getValue());
            $this->settingsManager->removeSettingByKey($name);
        }

        $photo = $this->uploader->upload($file);

        $settings->addEntry(new FileSettingEntry($name, $photo));
        $this->settingsManager->save($settings);

        $this->uploader->onSuccessfulUpload($photo, $name);

        if (null !== $this->eventDispatcher) {
            $this->eventDispatcher->dispatch(
                PhotoSystemEvents::PHOTO_WAS_UPLOADED,
                [
                    $this->photoSystemEvents->createPhotoUploadedEventInstance(
                        $photo,
                        $name
                    ),
                ]
            );
        }
    }

    /**
     * @param RemovePhoto $command
     *
     * @throws InvalidPhotoNameException
     */
    public function handleRemovePhoto(RemovePhoto $command)
    {
        $name = $command->getName();

        $this->throwExceptionIfInvalidPhotoName($name);

        $settings = $this->settingsManager->getSettings();
        $entry = $settings->getEntry($name);
        if ($entry instanceof SettingsEntry) {
            $photo = $entry->getValue();
            if ($photo instanceof Logo) {
                $this->uploader->remove($photo);
            }
            $this->settingsManager->removeSettingByKey($name);
        }

        if (null !== $this->eventDispatcher) {
            $this->eventDispatcher->dispatch(
                PhotoSystemEvents::PHOTO_WAS_REMOVED,
                [
                    $this->photoSystemEvents->createPhotoRemovedEventInstance(
                        $name
                    ),
                ]
            );
        }
    }

    /**
     * @param string $name
     *
     * @throws InvalidPhotoNameException
     */
    private function throwExceptionIfInvalidPhotoName(string $name): void
    {
        if (false === $this->uploader->isValidName($name)) {
            throw new InvalidPhotoNameException(
                sprintf($this->translator->trans('Invalid photo "%s" name'), $name)
            );
        }
    }
}
