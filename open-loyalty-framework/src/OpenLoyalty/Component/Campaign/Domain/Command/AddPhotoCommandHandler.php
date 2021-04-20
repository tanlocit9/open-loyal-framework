<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Campaign\Domain\Command;

use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\Event\CampaignPhotoSavedEvent;
use OpenLoyalty\Component\Campaign\Domain\Factory\PhotoEntityFactoryInterface;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignPhotoRepositoryInterface;
use OpenLoyalty\Component\Campaign\Domain\Repository\CampaignRepositoryInterface;
use OpenLoyalty\Component\Campaign\Domain\PhotoId;
use OpenLoyalty\Component\Campaign\Domain\PhotoMimeType;
use OpenLoyalty\Component\Campaign\Domain\PhotoOriginalName;
use OpenLoyalty\Component\Campaign\Domain\PhotoPath;

/**
 * Class AddPhotoCommandHandler.
 */
class AddPhotoCommandHandler extends SimpleCommandHandler
{
    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var CampaignPhotoRepositoryInterface
     */
    private $photoRepository;

    /**
     * @var PhotoEntityFactoryInterface
     */
    private $photoEntityFactory;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * CampaignCommandHandler constructor.
     *
     * @param CampaignRepositoryInterface      $campaignRepository
     * @param EventDispatcher                  $eventDispatcher
     * @param CampaignPhotoRepositoryInterface $photoRepository
     * @param PhotoEntityFactoryInterface      $photoEntityFactory
     * @param UuidGeneratorInterface           $uuidGenerator
     */
    public function __construct(
        CampaignRepositoryInterface $campaignRepository,
        EventDispatcher $eventDispatcher,
        CampaignPhotoRepositoryInterface $photoRepository,
        PhotoEntityFactoryInterface $photoEntityFactory,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->photoRepository = $photoRepository;
        $this->photoEntityFactory = $photoEntityFactory;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @param AddPhotoCommand $command
     *
     * @throws \Assert\AssertionFailedException|\InvalidArgumentException
     */
    public function handleAddPhotoCommand(AddPhotoCommand $command): void
    {
        $campaign = $this->campaignRepository->findOneById(new CampaignId($command->getCampaignId()));
        if (null === $campaign) {
            throw new \InvalidArgumentException('Campaign not found!');
        }

        $photoId = new PhotoId($this->uuidGenerator->generate());

        $fileName = md5(uniqid()).'.'.$command->getFile()['extension'];

        $photoPath = new PhotoPath($fileName);
        $mimeType = new PhotoMimeType($command->getFile()['mime_type']);
        $originalName = new PhotoOriginalName($command->getFile()['original_name']);

        $photo = $this->photoEntityFactory->crete($campaign, $photoId, $photoPath, $originalName, $mimeType);
        $this->photoRepository->save($photo);

        $this->eventDispatcher->dispatch(
            CampaignPhotoSavedEvent::NAME,
            [
                'file_path' => (string) $photoPath,
                'real_path' => $command->getFile()['real_path'],
            ]
        );
    }
}
