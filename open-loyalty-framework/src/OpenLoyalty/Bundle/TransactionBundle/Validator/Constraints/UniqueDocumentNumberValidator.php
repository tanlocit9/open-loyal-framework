<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Validator\Constraints;

use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class UniqueDocumentNumberValidator.
 */
class UniqueDocumentNumberValidator extends ConstraintValidator
{
    /**
     * @var TransactionDetailsRepository
     */
    private $transactionDetailsRepository;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * UniqueDocumentNumberValidator constructor.
     *
     * @param TransactionDetailsRepository $transactionDetailsRepository
     * @param Translator                   $translator
     */
    public function __construct(TransactionDetailsRepository $transactionDetailsRepository, Translator $translator)
    {
        $this->transactionDetailsRepository = $transactionDetailsRepository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        $duplicatedValue = $this->transactionDetailsRepository->findTransactionByDocumentNumber($value);

        if ($duplicatedValue) {
            $this->context->buildViolation($this->translator->trans($constraint->message))
                ->addViolation();
        }
    }
}
