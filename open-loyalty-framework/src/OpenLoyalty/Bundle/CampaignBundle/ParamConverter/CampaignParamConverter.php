<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CampaignBundle\ParamConverter;

use Assert\InvalidArgumentException;
use OpenLoyalty\Component\Campaign\Domain\Campaign;
use OpenLoyalty\Component\Campaign\Domain\CampaignId;
use OpenLoyalty\Component\Campaign\Domain\CampaignRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CampaignParamConverter.
 */
class CampaignParamConverter implements ParamConverterInterface
{
    /**
     * @var CampaignRepository
     */
    private $repository;

    /**
     * CampaignParamConverter constructor.
     *
     * @param CampaignRepository $repository
     */
    public function __construct(CampaignRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request        $request       The request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     *
     * @throws \Assert\AssertionFailedException
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $name = $configuration->getName();

        $value = $request->attributes->get($name, false);
        if (null === $value) {
            $configuration->setIsOptional(true);
        }

        try {
            $object = $this->repository->byId(new CampaignId($value));
        } catch (InvalidArgumentException $exception) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $configuration->getClass()));
        }

        if (null === $object && false === $configuration->isOptional()) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $configuration->getClass()));
        }
        $request->attributes->set($name, $object);

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration Should be an instance of ParamConverter
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === Campaign::class;
    }
}
