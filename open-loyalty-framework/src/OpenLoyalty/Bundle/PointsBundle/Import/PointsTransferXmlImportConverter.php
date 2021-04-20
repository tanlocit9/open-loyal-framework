<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Import;

use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Bundle\PointsBundle\Service\PointsTransfersManager;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Command\AddPoints;
use OpenLoyalty\Component\Account\Domain\Command\SpendPoints;
use OpenLoyalty\Component\Account\Domain\Model\PointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\SpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use OpenLoyalty\Component\Account\Domain\ReadModel\PointsTransferDetails;
use OpenLoyalty\Component\Import\Infrastructure\AbstractXMLImportConverter;
use OpenLoyalty\Component\Import\Infrastructure\ImportConvertException;
use OpenLoyalty\Component\Import\Infrastructure\Validator\XmlNodeValidator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PointsTransferXmlImportConverter.
 */
class PointsTransferXmlImportConverter extends AbstractXMLImportConverter
{
    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var PointsTransfersManager
     */
    protected $pointsTransfersManager;

    /**
     * @var AccountProviderInterface
     */
    protected $accountProvider;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * PointsTransferXmlImportConverter constructor.
     *
     * @param UuidGeneratorInterface   $uuidGenerator
     * @param PointsTransfersManager   $pointsTransfersManager
     * @param AccountProviderInterface $accountProvider
     * @param TranslatorInterface      $translator
     */
    public function __construct(UuidGeneratorInterface $uuidGenerator, PointsTransfersManager $pointsTransfersManager,
                                AccountProviderInterface $accountProvider, TranslatorInterface $translator)
    {
        $this->uuidGenerator = $uuidGenerator;
        $this->pointsTransfersManager = $pointsTransfersManager;
        $this->accountProvider = $accountProvider;
        $this->translator = $translator;
    }

    /**
     * @param string $customerId
     * @param string $email
     * @param string $phone
     * @param string $loyaltyNumber
     *
     * @return AccountDetails
     *
     * @throws \Exception
     */
    protected function getAccount(string $customerId, string $email, string $phone, string $loyaltyNumber): AccountDetails
    {
        $account = $this->accountProvider->provideOne($customerId, $email, $phone, $loyaltyNumber);

        if (!$account) {
            throw new \Exception('Account does not exist for given data');
        }

        return $account;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(\SimpleXMLElement $element)
    {
        $this->checkValidNodes(
            $element,
            [
                'points' => ['format' => XmlNodeValidator::INTEGER_FORMAT, 'required' => true],
                'type' => ['required' => true],
                'validityDuration' => ['required' => true],
            ]
        );

        if (!$element->{'customerId'} && !$element->{'customerEmail'} && !$element->{'customerPhoneNumber'} && !$element->{'customerLoyaltyCardNumber'}) {
            throw new ImportConvertException($this->translator->trans('At least one node should be defined '
                .'(customerId, customerEmail, customerLoyaltyCardNumber, customerPhoneNumber)'));
        }

        $transferType = (string) $element->{'type'};
        $account = $this->getAccount(
            (string) $element->{'customerId'},
            (string) $element->{'customerEmail'},
            (string) $element->{'customerPhoneNumber'},
            (string) $element->{'customerLoyaltyCardNumber'}
        );
        $pointsTransferId = new PointsTransferId($this->uuidGenerator->generate());

        switch ($transferType) {
            case PointsTransferDetails::TYPE_ADDING:
                return new AddPoints(
                    new AccountId($account->getId()),
                    $this->pointsTransfersManager->createAddPointsTransferInstance(
                        $pointsTransferId,
                        (string) $element->{'points'},
                        null,
                        false,
                        null,
                        (string) $element->{'comment'},
                        PointsTransfer::ISSUER_ADMIN
                    )
                );
            case PointsTransferDetails::TYPE_SPENDING:
                return new SpendPoints(
                    $account->getAccountId(),
                    new SpendPointsTransfer(
                        $pointsTransferId,
                        (string) $element->{'points'},
                        null,
                        false,
                        (string) $element->{'comment'},
                        PointsTransfer::ISSUER_ADMIN
                    )
                );
                break;
        }

        throw new \InvalidArgumentException(sprintf('type = %s', $transferType));
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(\SimpleXMLElement $element): string
    {
        return sprintf(
            '%s/(%s %s)',
            (string) ($element->{'customerId'} ?? $element->{'customerEmail'} ?? $element->{'customerPhoneNumber'} ?? $element->{'customerLoyaltyCardNumber'}),
            (string) $element->{'type'},
            (string) $element->{'points'}
        );
    }
}
