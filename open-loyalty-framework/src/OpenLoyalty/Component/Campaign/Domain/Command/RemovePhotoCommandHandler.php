<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Command;

use Assert\AssertionFailedException;
use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\EventDispatcher\EventDispatcher;
use OpenLoyalty\Component\Campaign\Domain\Event\CampaignPhotoRemovedEvent;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignPhotoRepositoryInterface;

/**
 * Class RemovePhotoCommandHandler.
 */
class RemovePhotoCommandHandler extends SimpleCommandHandler
{
    /**
     * @var CampaignPhotoRepositoryInterface
     */
    private $photoRepository;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * RemovePhotoCommandHandler constructor.
     *
     * @param CampaignPhotoRepositoryInterface $photoRepository
     * @param EventDispatcher                  $eventDispatcher
     */
    public function __construct(
        CampaignPhotoRepositoryInterface $photoRepository,
        EventDispatcher $eventDispatcher
    ) {
        $this->photoRepository = $photoRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param RemovePhotoCommand $command
     *
     * @throws \InvalidArgumentException|AssertionFailedException
     */
    public function handleRemovePhotoCommand(RemovePhotoCommand $command): void
    {
        $photo = $this->photoRepository->findOneByIdCampaignId($command->getPhotoId(), $command->getCampaignId());
        if (null === $photo) {
            throw new \InvalidArgumentException('Campaign photo not found!');
        }

        $this->eventDispatcher->dispatch(
            CampaignPhotoRemovedEvent::NAME,
            [
                'file_path' => (string) $photo->getPath(),
            ]
        );
        $this->photoRepository->remove($photo);
    }
}
