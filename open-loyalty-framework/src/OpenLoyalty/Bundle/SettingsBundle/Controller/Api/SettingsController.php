<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\SettingsBundle\Controller\Api;

use Broadway\CommandHandling\SimpleCommandBus;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use OpenLoyalty\Bundle\SettingsBundle\Entity\FileSettingEntry;
use OpenLoyalty\Bundle\SettingsBundle\Exception\AlreadyExistException;
use OpenLoyalty\Bundle\SettingsBundle\Form\Type\ConditionsFileType;
use OpenLoyalty\Bundle\SettingsBundle\Form\Type\LogoFormType;
use OpenLoyalty\Bundle\SettingsBundle\Form\Type\SettingsFormType;
use OpenLoyalty\Bundle\SettingsBundle\Form\Type\TranslationsFormType;
use OpenLoyalty\Bundle\SettingsBundle\Service\ConditionsUploader;
use OpenLoyalty\Bundle\SettingsBundle\Service\LogoUploader;
use OpenLoyalty\Bundle\SettingsBundle\Service\SettingsManager;
use OpenLoyalty\Bundle\SettingsBundle\Service\TemplateProvider;
use OpenLoyalty\Bundle\SettingsBundle\Provider\ChoicesProvider;
use OpenLoyalty\Bundle\SettingsBundle\Service\TranslationsProvider;
use OpenLoyalty\Component\Core\Domain\Command\RemovePhoto;
use OpenLoyalty\Component\Core\Domain\Command\UploadPhoto;
use OpenLoyalty\Component\Core\Domain\Exception\InvalidPhotoNameException;
use OpenLoyalty\Component\Customer\Domain\Model\Status;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SettingsController.
 */
class SettingsController extends FOSRestController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var SettingsManager
     */
    private $settingsManager;

    /**
     * @var SimpleCommandBus
     */
    private $commandBus;

    /**
     * @var LogoUploader
     */
    private $uploader;

    /**
     * @var TranslationsProvider
     */
    private $translationsProvider;

    /**
     * @var TemplateProvider
     */
    private $templateProvider;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * SettingsController constructor.
     *
     * @param TranslatorInterface  $translator
     * @param SettingsManager      $settingsManager
     * @param TranslationsProvider $translationsProvider
     * @param LogoUploader         $uploader
     * @param SimpleCommandBus     $commandBus
     * @param TemplateProvider     $templateProvider
     * @param FormFactory          $formFactory
     */
    public function __construct(
        TranslatorInterface $translator,
        SettingsManager $settingsManager,
        TranslationsProvider $translationsProvider,
        LogoUploader $uploader,
        SimpleCommandBus $commandBus,
        TemplateProvider $templateProvider,
        FormFactory $formFactory
    ) {
        $this->translator = $translator;
        $this->settingsManager = $settingsManager;
        $this->translationsProvider = $translationsProvider;
        $this->uploader = $uploader;
        $this->commandBus = $commandBus;
        $this->templateProvider = $templateProvider;
        $this->formFactory = $formFactory;
    }

    /**
     * Add photo.
     *
     * @Route(name="oloy.settings.add_photo", path="/settings/photo/{name}")
     * @Method("POST")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Add named photo",
     *     section="Settings",
     *     input={"class" = "OpenLoyalty\Bundle\SettingsBundle\Form\Type\LogoFormType", "name" = "photo"},
     *     requirements={{"name"="name", "description"="allowed names: logo, small-logo, hero-image, admin-cockpit-logo, client-cockpit-logo-big, client-cockpit-logo-small, client-cockpit-hero-image", "dataType"="string", "required"=true}}
     * )
     *
     * @param Request $request
     * @param string  $name
     *
     * @return View
     *
     * @throws \Exception
     */
    public function addPhotoAction(Request $request, string $name): View
    {
        $form = $this->get('form.factory')->createNamed('photo', LogoFormType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $uploadPhotoCommand = new UploadPhoto($name, $form->getData()->getFile());
                $this->commandBus->dispatch($uploadPhotoCommand);
            } catch (InvalidPhotoNameException $e) {
                throw $this->createNotFoundException($e->getMessage(), $e);
            }

            return $this->view([], Response::HTTP_OK);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove named photo.
     *
     * @Route(name="oloy.settings.remove_photo", path="/settings/photo/{name}")
     * @Method("DELETE")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Delete named photo",
     *     section="Settings",
     *     requirements={{"name"="name", "description"="allowed names: logo, small-logo, hero-image, admin-cockpit-logo, client-cockpit-logo-big, client-cockpit-logo-small, client-cockpit-hero-image", "dataType"="string", "required"=true}}
     * )
     *
     * @param string $name
     *
     * @return View
     *
     * @throws \Exception
     */
    public function removePhotoAction(string $name): View
    {
        try {
            $removePhotoCommand = new RemovePhoto($name);
            $this->commandBus->dispatch($removePhotoCommand);
        } catch (InvalidPhotoNameException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        return $this->view([], Response::HTTP_OK);
    }

    /**
     * Get named photo.
     *
     * @Method("GET")
     * @Route(name="oloy.settings.get_photo", path="/settings/photo/{name}")
     * @ApiDoc(
     *     name="Get named photo",
     *     section="Settings",
     *     requirements={{"name"="name", "description"="allowed names: logo, small-logo, hero-image, admin-cockpit-logo, client-cockpit-logo-big, client-cockpit-logo-small, client-cockpit-hero-image", "dataType"="string"}},
     * )
     * @Route(name="oloy.settings.get_photo_size", path="/settings/photo/{name}/{size}", defaults={"size"=null}, requirements={"size"="\d{1,}x\d{1,}"})
     * @ApiDoc(
     *     name="Get named photo",
     *     section="Settings",
     *     requirements={
     *      {"name"="name", "description"="allowed names: logo, small-logo, hero-image, admin-cockpit-logo, client-cockpit-logo-big, client-cockpit-logo-small, client-cockpit-hero-image", "dataType"="string"},
     *      {"name"="size", "description"="allowed sizes: 192x192, 512x512 available only for names: small-logo", "dataType"="string"}
     *     }
     * )
     *
     * @param string      $name
     * @param null|string $size
     *
     * @return Response
     */
    public function getPhotoAction(string $name, ?string $size = null): Response
    {
        $settings = $this->settingsManager->getSettings();
        $logoEntry = $settings->getEntry($name);
        $logo = null;

        if (null !== $logoEntry) {
            $logo = $logoEntry->getValue();
        }
        if (null === $logo) {
            throw $this->createNotFoundException();
        }

        $content = $this->uploader->get($logo, $size);
        if (null === $content) {
            throw $this->createNotFoundException();
        }

        $response = new Response($content);
        $response->headers->set('Content-Disposition', 'inline');
        $response->headers->set('Content-Type', $logo->getMime());

        return $response;
    }

    /**
     * Add logo.
     *
     * @Route(name="oloy.settings.add_logo", path="/settings/logo")
     * @Method("POST")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Add logo to loyalty program",
     *     section="Settings",
     *     input={"class" = "OpenLoyalty\Bundle\SettingsBundle\Form\Type\LogoFormType", "name" = "photo"}
     * )
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws InvalidPhotoNameException
     * @throws \Exception
     */
    public function addLogoAction(Request $request): View
    {
        return $this->addPhotoAction($request, LogoUploader::LOGO);
    }

    /**
     * Add small logo.
     *
     * @Route(name="oloy.settings.add_small_logo", path="/settings/small-logo")
     * @Method("POST")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Add small logo to loyalty program",
     *     section="Settings",
     *     input={"class" = "OpenLoyalty\Bundle\SettingsBundle\Form\Type\LogoFormType", "name" = "photo"}
     * )
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws InvalidPhotoNameException
     * @throws \Exception
     */
    public function addSmallLogoAction(Request $request): View
    {
        return $this->addPhotoAction($request, LogoUploader::SMALL_LOGO);
    }

    /**
     * Add hero image.
     *
     * @Route(name="oloy.settings.add_hero_image", path="/settings/hero-image")
     * @Method("POST")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Add hero image to loyalty program",
     *     section="Settings",
     *     input={"class" = "OpenLoyalty\Bundle\SettingsBundle\Form\Type\LogoFormType", "name" = "photo"}
     * )
     *
     * @param Request $request
     *
     * @return View
     *
     * @throws InvalidPhotoNameException
     * @throws \Exception
     */
    public function addHeroImageAction(Request $request): View
    {
        return $this->addPhotoAction($request, LogoUploader::HERO_IMAGE);
    }

    /**
     * Add conditions file.
     *
     * @Route(name="oloy.settings.add_conditions_file", path="/settings/conditions-file")
     * @Method("POST")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Add conditions file to loyalty program",
     *     section="Settings",
     *     input={"class" = "OpenLoyalty\Bundle\SettingsBundle\Form\Type\ConditionsFileType", "name" = "conditions"}
     * )
     *
     * @param Request            $request
     * @param ConditionsUploader $conditionsUploader
     *
     * @return View
     */
    public function addConditionsFileAction(Request $request, ConditionsUploader $conditionsUploader)
    {
        $form = $this->get('form.factory')->createNamed('conditions', ConditionsFileType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->getData()->getFile();

            $settingsManager = $this->get('ol.settings.manager');
            $settings = $settingsManager->getSettings();
            $conditions = $settings->getEntry(ConditionsUploader::CONDITIONS);
            if ($conditions) {
                $conditionsUploader->remove($conditions->getValue());
                $settingsManager->removeSettingByKey(ConditionsUploader::CONDITIONS);
            }

            $conditions = $conditionsUploader->upload($file);

            $settings->addEntry(new FileSettingEntry(ConditionsUploader::CONDITIONS, $conditions));
            $settingsManager->save($settings);

            return $this->view([], Response::HTTP_OK);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove logo.
     *
     * @Route(name="oloy.settings.remove_logo", path="/settings/logo")
     * @Method("DELETE")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Delete logo",
     *     section="Settings"
     * )
     *
     * @return View
     *
     * @throws InvalidPhotoNameException
     * @throws \Exception
     */
    public function removeLogoAction(): View
    {
        return $this->removePhotoAction(LogoUploader::LOGO);
    }

    /**
     * Remove small logo.
     *
     * @Route(name="oloy.settings.remove_small_logo", path="/settings/small-logo")
     * @Method("DELETE")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Delete small logo",
     *     section="Settings"
     * )
     *
     * @return View
     *
     * @throws InvalidPhotoNameException
     * @throws \Exception
     */
    public function removeSmallLogoAction(): View
    {
        return $this->removePhotoAction(LogoUploader::SMALL_LOGO);
    }

    /**
     * Remove hero imag.
     *
     * @Route(name="oloy.settings.remove_hero_image", path="/settings/hero-image")
     * @Method("DELETE")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Delete hero image",
     *     section="Settings"
     * )
     *
     * @return View
     *
     * @throws InvalidPhotoNameException
     * @throws \Exception
     */
    public function removeHeroImageAction(): View
    {
        return $this->removePhotoAction(LogoUploader::HERO_IMAGE);
    }

    /**
     * Remove conditions file.
     *
     * @Route(name="oloy.settings.remove_conditions_file", path="/settings/conditions-file")
     * @Method("DELETE")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Delete conditions file",
     *     section="Settings"
     * )
     *
     * @param ConditionsUploader $conditionsUploader
     *
     * @return View
     */
    public function removeConditionsFileAction(ConditionsUploader $conditionsUploader)
    {
        $settingsManager = $this->get('ol.settings.manager');
        $settings = $settingsManager->getSettings();
        $conditions = $settings->getEntry(ConditionsUploader::CONDITIONS);
        if ($conditions) {
            $conditions = $conditions->getValue();
            $conditionsUploader->remove($conditions);
            $settingsManager->removeSettingByKey(ConditionsUploader::CONDITIONS);
        }

        return $this->view([], Response::HTTP_OK);
    }

    /**
     * Get logo.
     *
     * @Route(name="oloy.settings.get_logo", path="/settings/logo")
     * @Route(name="oloy.settings.get_logo_size", path="/settings/logo/{size}", defaults={"size"=null}, requirements={"size"="\d{1,}x\d{1,}"})
     * @Method("GET")
     * @ApiDoc(
     *     name="Get logo",
     *     section="Settings",
     *     parameters={{"name"="active", "dataType"="boolean", "required"=true}}
     * )
     *
     * @param null|string $size
     *
     * @return Response
     */
    public function getLogoAction(?string $size = null): Response
    {
        return $this->getPhotoAction(LogoUploader::LOGO, $size);
    }

    /**
     * Get small logo.
     *
     * @Route(name="oloy.settings.get_small_logo", path="/settings/small-logo")
     * @Route(name="oloy.settings.get_small_logo_size", path="/settings/small-logo/{size}", defaults={"size"=null}, requirements={"size"="\d{1,}x\d{1,}"})
     * @Method("GET")
     * @ApiDoc(
     *     name="Get small logo",
     *     section="Settings"
     * )
     *
     * @param string $size
     *
     * @return Response
     */
    public function getSmallLogoAction(?string $size = null): Response
    {
        return $this->getPhotoAction(LogoUploader::SMALL_LOGO, $size);
    }

    /**
     * Get hero image.
     *
     * @Route(name="oloy.settings.get_hero_image_size", path="/settings/hero-image")
     * @Route(name="oloy.settings.get_hero_image_size", path="/settings/hero-image/{size}", defaults={"size"=null}, requirements={"size"="\d{1,}x\d{1,}"})
     * @Method("GET")
     * @ApiDoc(
     *     name="Get hero image",
     *     section="Settings"
     * )
     *
     * @param null|string $size
     *
     * @return Response
     */
    public function getHeroImageAction(?string $size = null): Response
    {
        return $this->getPhotoAction(LogoUploader::HERO_IMAGE, $size);
    }

    /**
     * Get manifest file.
     *
     * @Route(name="oloy.settings.get_manifest", path="/settings/manifest")
     * @Method("GET")
     * @ApiDoc(
     *     name="Get PWA manifest",
     *     section="Settings"
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getManifestAction(Request $request): Response
    {
        $settings = $this->settingsManager->getSettings();
        $program = $settings->getEntry('programName');

        if (null == $program) {
            $error = [
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => $this->translator->trans('settings.get_manifest.not_found'),
                ],
            ];

            return JsonResponse::create($error, Response::HTTP_BAD_REQUEST, [
                'Content-type', 'application/json',
            ]);
        }

        $data = [
            'name' => $program->getValue(),
            'short_name' => $program->getValue(),
            'icons' => [
                [
                    'src' => $request->getHost().$this->generateUrl('oloy.settings.get_small_logo'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => $request->getHost().$this->generateUrl('oloy.settings.get_logo'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
            'start_url' => '/',
            'display' => 'standalone',
            'scope' => '/',
            'background_color' => '#FFFFFF',
            'theme_color' => '#FFFFFF',
        ];

        return JsonResponse::create($data, Response::HTTP_OK, [
            'Content-type', 'application/json',
        ]);
    }

    /**
     * Get conditions files.
     *
     * @Route(name="oloy.settings.get_conditions_file", path="/settings/conditions-file")
     * @Method("GET")
     *
     * @param ConditionsUploader $conditionsUploader
     *
     * @return Response
     */
    public function getConditionsFileAction(ConditionsUploader $conditionsUploader)
    {
        $settingsManager = $this->get('ol.settings.manager');
        $settings = $settingsManager->getSettings();
        $conditionsEntry = $settings->getEntry(ConditionsUploader::CONDITIONS);
        $conditions = null;

        if ($conditionsEntry) {
            $conditions = $conditionsEntry->getValue();
        }
        if (!$conditions) {
            throw $this->createNotFoundException();
        }

        $content = $conditionsUploader->get($conditions);
        if (!$content) {
            throw $this->createNotFoundException();
        }

        $response = new Response($content);
        $response->headers->set('Content-Disposition', 'attachment; filename=terms_conditions.pdf');
        $response->headers->set('Content-Type', $conditions->getMime());

        return $response;
    }

    /**
     * Get conditions url.
     *
     * @Route(name="oloy.settings.get_conditions_url", path="/settings/conditions-url")
     * @Method("GET")
     *
     * @param ConditionsUploader $conditionsUploader
     *
     * @return Response
     */
    public function getConditionsUrlAction(ConditionsUploader $conditionsUploader)
    {
        return new JsonResponse(['url' => $conditionsUploader->getUrl()]);
    }

    /**
     * Method allow to update system settings.
     *
     * @Route(name="oloy.settings.edit", path="/settings")
     * @Method("POST")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Edit system settings",
     *     section="Settings",
     *     input={"class" = "OpenLoyalty\Bundle\SettingsBundle\Form\Type\SettingsFormType", "name" = "settings"},
     *     statusCodes={
     *          200="Returned when successful",
     *          400="Returned when form contains errors",
     *     }
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request): Response
    {
        $form = $this->formFactory->createNamed('settings', SettingsFormType::class, $this->settingsManager->getSettings());
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $data = $form->getData();
                $this->settingsManager->removeAll();
                $this->settingsManager->save($data);

                return $this->handleView(
                    View::create(
                        [
                            'settings' => $data->toArray(),
                        ],
                        Response::HTTP_OK
                    )
                );
            } catch (AlreadyExistException $exception) {
                $error = [
                    'error' => [
                        'code' => Response::HTTP_BAD_REQUEST,
                        'message' => $exception->getMessage(),
                    ],
                ];

                return JsonResponse::create($error, Response::HTTP_BAD_REQUEST, [
                    'Content-type', 'application/json',
                ]);
            }
        }

        return $this->handleView(View::create($form->getErrors(), Response::HTTP_BAD_REQUEST));
    }

    /**
     * Method will return all system settings.
     *
     * @Route(name="oloy.settings.get", path="/settings")
     * @Method("GET")
     * @Security("is_granted('VIEW_SETTINGS')")
     * @ApiDoc(
     *     name="Get system settings",
     *     section="Settings"
     * )
     *
     * @return View
     */
    public function getAction()
    {
        $settingsManager = $this->get('ol.settings.manager');

        return $this->view([
            'settings' => $settingsManager->getSettings()->toArray(),
        ], Response::HTTP_OK);
    }

    /**
     * Method will return current translations.
     *
     * @Route(name="oloy.settings.translations", path="/translations")
     * @Method("GET")
     * @ApiDoc(
     *     name="Get translations",
     *     section="Settings"
     * )
     *
     * @return Response
     */
    public function translationsAction(): Response
    {
        return new Response($this->translationsProvider->getCurrentTranslations()->getContent(), Response::HTTP_OK, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Method will return list of available translations.
     *
     * @Route(name="oloy.settings.translations_list", path="/admin/translations")
     * @Method("GET")
     * @Security("is_granted('VIEW_SETTINGS')")
     * @ApiDoc(
     *     name="Get translations list",
     *     section="Settings"
     * )
     *
     * @return View
     */
    public function listTranslationsAction(): View
    {
        $translations = $this->translationsProvider->getAvailableTranslationsList();

        return $this->view(
            [
                'translations' => $translations,
                'total' => count($translations),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Method will return list of available customer statuses.
     *
     * @Route(name="oloy.settings.customer_statuses_list", path="/admin/customer-statuses")
     * @Method("GET")
     * @Security("is_granted('VIEW_SETTINGS')")
     * @ApiDoc(
     *     name="Get customer statuses list",
     *     section="Settings"
     * )
     *
     * @return View
     */
    public function listCustomerStatusesAction()
    {
        $statuses = Status::getAvailableStatuses();

        return $this->view(
            [
                'statuses' => $statuses,
                'total' => count($statuses),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Method will return translations<br/> You must provide translation key, available keys can be obtained by /admin/translations endpoint.
     *
     * @Route(name="oloy.settings.translations_get", path="/admin/translations/{code}")
     * @Method("GET")
     * @Security("is_granted('VIEW_SETTINGS')")
     * @ApiDoc(
     *     name="Get single translation by code",
     *     section="Settings"
     * )
     *
     * @param string $code
     *
     * @return View
     *
     * @internal param $code
     */
    public function getTranslationByCodeAction(string $code): View
    {
        try {
            $translationsEntry = $this->translationsProvider->getTranslationsByKey($code);
        } catch (\Exception $e) {
            throw $this->createNotFoundException($this->translator->trans($e->getMessage()), $e);
        }

        return $this->view($translationsEntry, Response::HTTP_OK);
    }

    /**
     * Method will remove translations<br/> You must provide translation key, available keys can be obtained by /admin/translations endpoint.
     *
     * @Route(name="oloy.settings.translations_remove", path="/admin/translations/{code}")
     * @Method("DELETE")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Remove single translation by code",
     *     section="Settings"
     * )
     *
     * @param string $code
     *
     * @return View
     */
    public function removeTranslationByCodeAction(string $code): View
    {
        if (!$this->translationsProvider->hasTranslation($code)) {
            throw $this->createNotFoundException();
        }

        $this->translationsProvider->remove($code);

        return $this->view([], Response::HTTP_OK);
    }

    /**
     * Method allows to update specific translations.
     *
     * @Route(name="oloy.settings.translations_update", path="/admin/translations/{code}")
     * @Method("PUT")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Update single translation by code",
     *     section="Settings"
     * )
     *
     * @param Request $request
     * @param string  $code
     *
     * @return View
     */
    public function updateTranslationByCodeAction(Request $request, string $code): View
    {
        if (!$this->translationsProvider->hasTranslation($code)) {
            throw $this->createNotFoundException();
        }
        $entry = $this->translationsProvider->getTranslationsByKey($code);
        $wasDefault = $entry->isDefault();

        $form = $this->get('form.factory')->createNamed('translation', TranslationsFormType::class, $entry, [
            'method' => 'PUT',
            'validation_groups' => ['edit', 'Default'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($wasDefault && !$entry->isDefault()) {
                return $this->view(['error' => $this->translator->trans('Some default language is required')], Response::HTTP_BAD_REQUEST);
            }

            $this->translationsProvider->update($entry);

            return $this->view($entry, Response::HTTP_OK);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method allows to create new translations.
     *
     * @Route(name="oloy.settings.translations_create", path="/admin/translations")
     * @Method("POST")
     * @Security("is_granted('EDIT_SETTINGS')")
     * @ApiDoc(
     *     name="Create single translation",
     *     section="Settings",
     *     input={"class"="OpenLoyalty\Bundle\SettingsBundle\Form\Type\TranslationsFormType", "name"="translation"},
     *     statusCodes={
     *       200="Returned when successful",
     *       400="Returned when form contains errors",
     *     }
     * )
     *
     * @param Request $request
     *
     * @return View
     */
    public function createTranslationAction(Request $request): View
    {
        $form = $this->get('form.factory')->createNamed('translation', TranslationsFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry = $form->getData();
            $this->translationsProvider->create($entry);

            return $this->view($entry, Response::HTTP_OK);
        }

        return $this->view($form->getErrors(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * Method will return activation method (email|sms).
     *
     * @Route(name="oloy.settings.get_activation_method", path="/settings/activation-method")
     * @Method("GET")
     * @ApiDoc(
     *     name="Get activation method",
     *     section="Settings"
     * )
     *
     * @return View
     */
    public function getActivationMethodAction()
    {
        return $this->view(['method' => $this->get('oloy.action_token_manager')->getCurrentMethod()]);
    }

    /**
     * Method will return some data needed for specific select fields.
     *
     * @Route(name="oloy.settings.get_form_choices", path="/settings/choices/{type}")
     * @Method("GET")
     * @Security("is_granted('VIEW_SETTINGS_CHOICES')")
     * @ApiDoc(
     *     name="Get choices",
     *     section="Settings",
     *     requirements={{"name"="type", "description"="allowed types: timezone, language, country, availableFrontendTranslations, earningRuleLimitPeriod, availableCustomerStatuses, availableAccountActivationMethods, availablePointExpireAfter, deliveryStatus", "dataType"="string", "required"=true}}
     * )
     *
     * @param ChoicesProvider $choicesProvider
     * @param string          $type
     *
     * @return View
     */
    public function getChoicesAction(ChoicesProvider $choicesProvider, string $type)
    {
        $result = $choicesProvider->getChoices($type);

        if (empty($result)) {
            throw $this->createNotFoundException();
        }

        return $this->view($result);
    }

    /**
     * Method will return customized CSS.
     *
     * @Route(name="oloy.settings.css", path="/settings/css")
     * @Method("GET")
     * @ApiDoc(
     *     name="Get choices",
     *     section="Settings",
     *     requirements={{"name"="json", "Returns content as json", "dataType"="boolean", "required"=false}}
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cssAction(Request $request): Response
    {
        if ($request->get('json', false)) {
            return new JsonResponse($this->templateProvider->getJsonContent(), Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response($this->templateProvider->getCssContent(), Response::HTTP_OK, ['Content-Type' => 'text/css; charset=utf-8']);
    }

    /**
     * Method will return all public system settings.
     *
     * @Route(name="oloy.settings.public", path="/settings/public")
     * @Method("GET")
     * @ApiDoc(
     *     name="Get public system settings",
     *     section="Settings"
     * )
     *
     * @return View
     */
    public function publicAction(): View
    {
        $key = SettingsFormType::ALLOW_CUSTOMERS_PROFILE_EDITS_SETTINGS_KEY;
        $settingsEntry = $this->settingsManager->getSettingByKey($key);

        return $this->view(
            [
                'settings' => [
                    $key => $settingsEntry->getValue(),
                ],
            ],
            Response::HTTP_OK
        );
    }
}
