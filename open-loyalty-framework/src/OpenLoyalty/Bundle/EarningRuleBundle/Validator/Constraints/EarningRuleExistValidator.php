<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace OpenLoyalty\Bundle\EarningRuleBundle\Validator\Constraints;

use OpenLoyalty\Component\EarningRule\Domain\EarningRuleGeoRepository;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleId;
use Exception;

/**
 * Class EarningRuleExistValidator.
 */
class EarningRuleExistValidator extends ConstraintValidator
{
    /**
     * @var EarningRuleGeoRepository
     */
    private $earningRuleGeoRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * EarningRuleExistValidator constructor.
     *
     * @param EarningRuleGeoRepository $earningRuleGeoRepository
     * @param TranslatorInterface      $translator
     */
    public function __construct(EarningRuleGeoRepository $earningRuleGeoRepository, TranslatorInterface $translator)
    {
        $this->earningRuleGeoRepository = $earningRuleGeoRepository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }
        try {
            $entity = $this->earningRuleGeoRepository->byId(new EarningRuleId($value));
            if ($entity) {
                return;
            }
            $this->context->buildViolation($this->translator->trans('earningRule_geo_entity_not_exist'))->addViolation();
        } catch (Exception $ex) {
            $this->context->buildViolation($ex->getMessage())->addViolation();
        }
    }
}
