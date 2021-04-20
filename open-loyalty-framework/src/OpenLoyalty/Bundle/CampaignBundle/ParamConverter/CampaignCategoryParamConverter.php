<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\ParamConverter;

use OpenLoyalty\Component\Campaign\Domain\CampaignCategory;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryId;
use OpenLoyalty\Component\Campaign\Domain\CampaignCategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CampaignCategoryParamConverter.
 */
class CampaignCategoryParamConverter implements ParamConverterInterface
{
    /**
     * @var CampaignCategoryRepository
     */
    protected $repository;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * CampaignCategoryParamConverter constructor.
     *
     * @param CampaignCategoryRepository $repository
     * @param TranslatorInterface        $translator
     */
    public function __construct(CampaignCategoryRepository $repository, TranslatorInterface $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $name = $configuration->getName();

        if (null === $request->attributes->get($name, false)) {
            $configuration->setIsOptional(true);
        }
        $value = $request->attributes->get($name);
        $object = $this->repository->byId(new CampaignCategoryId($value));

        if (null === $object && false === $configuration->isOptional()) {
            throw new NotFoundHttpException($this->translator->trans(
                sprintf('%s object not found.', $configuration->getClass())
            ));
        }
        $request->attributes->set($name, $object);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === CampaignCategory::class;
    }
}
