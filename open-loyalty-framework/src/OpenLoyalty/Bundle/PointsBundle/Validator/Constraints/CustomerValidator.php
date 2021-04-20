<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Broadway\ReadModel\Repository;
use OpenLoyalty\Component\Customer\Domain\ReadModel\CustomerDetails;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CustomerValidator.
 */
class CustomerValidator extends ConstraintValidator
{
    /**
     * @var Repository
     */
    protected $customerDetailsRepository;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * CustomerValidator constructor.
     *
     * @param Repository          $customerDetailsRepository
     * @param TranslatorInterface $translator
     */
    public function __construct(Repository $customerDetailsRepository, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->customerDetailsRepository = $customerDetailsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }
        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $entity = $this->customerDetailsRepository->find($value);

        if ($entity instanceof CustomerDetails) {
            return;
        }

        $this->context->buildViolation($this->translator->trans('account.points_transfer.customer.not_exists'))
            ->setParameter('{{ id }}', $value)
            ->addViolation();
    }
}
