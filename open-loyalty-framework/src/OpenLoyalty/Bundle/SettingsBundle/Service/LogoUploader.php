<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Service;

use Broadway\CommandHandling\SimpleCommandBus;
use Gaufrette\Filesystem;
use OpenLoyalty\Bundle\SettingsBundle\Model\FileInterface;
use OpenLoyalty\Bundle\SettingsBundle\Model\Logo;
use OpenLoyalty\Component\Core\Domain\Command\ResizeLogo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class LogoUploader.
 */
class LogoUploader
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var SimpleCommandBus
     */
    protected $commandBus;

    const LOGO = 'logo';
    const SMALL_LOGO = 'small-logo';
    const HERO_IMAGE = 'hero-image';
    const ADMIN_COCKPIT_LOGO = 'admin-cockpit-logo';
    const CLIENT_COCKPIT_LOGO_BIG = 'client-cockpit-logo-big';
    const CLIENT_COCKPIT_LOGO_SMALL = 'client-cockpit-logo-small';
    const CLIENT_COCKPIT_HERO_IMAGE = 'client-cockpit-hero-image';

    /**
     * LogoUploader constructor.
     *
     * @param Filesystem       $filesystem
     * @param SimpleCommandBus $commandBus
     */
    public function __construct(Filesystem $filesystem, SimpleCommandBus $commandBus)
    {
        $this->filesystem = $filesystem;
        $this->commandBus = $commandBus;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isValidName(string $name): bool
    {
        return in_array(
            $name,
            [
                self::LOGO,
                self::SMALL_LOGO,
                self::HERO_IMAGE,
                self::ADMIN_COCKPIT_LOGO,
                self::CLIENT_COCKPIT_LOGO_BIG,
                self::CLIENT_COCKPIT_LOGO_SMALL,
                self::CLIENT_COCKPIT_HERO_IMAGE,
            ]
        );
    }

    /**
     * @param Logo        $photo
     * @param null|string $size
     *
     * @return string|null
     */
    public function get(Logo $photo, ?string $size = null)
    {
        if (null === $photo || null === $photo->getPath()) {
            return;
        }

        if (null !== $size) {
            if (!in_array($size, $photo->getSizes())) {
                return;
            }

            return $this->filesystem->get($photo->getResizedPath($size))->getContent();
        }

        return $this->filesystem->get($photo->getPath())->getContent();
    }

    /**
     * @param UploadedFile $src
     *
     * @return Logo
     */
    public function upload(UploadedFile $src)
    {
        $file = new Logo();
        $fileName = md5(uniqid()).'.'.$src->guessExtension();
        $file->setPath('logo'.DIRECTORY_SEPARATOR.$fileName);
        $file->setMime($src->getClientMimeType());
        $file->setOriginalName($src->getClientOriginalName());

        $this->filesystem->write($file->getPath(), file_get_contents($src->getRealPath()));
        unlink($src->getRealPath());

        return $file;
    }

    /**
     * @param Logo|null $file
     */
    public function remove(Logo $file = null)
    {
        if (null === $file || null === $file->getPath()) {
            return;
        }

        $path = $file->getPath();
        if ($this->filesystem->has($path)) {
            $this->filesystem->delete($path);
        }
    }

    /**
     * @param FileInterface $file
     * @param string        $imageType
     *
     * @throws \Exception
     */
    public function onSuccessfulUpload(FileInterface $file, string $imageType)
    {
        // resize logo
        $resizeLogoCommand = new ResizeLogo($file, $imageType);
        $this->commandBus->dispatch($resizeLogoCommand);
    }
}
