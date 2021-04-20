<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Import;

use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Component\Import\Infrastructure\AbstractXMLImportConverter;
use OpenLoyalty\Component\Import\Infrastructure\Validator\XmlNodeValidator;
use OpenLoyalty\Component\Pos\Domain\Pos;
use OpenLoyalty\Component\Pos\Domain\PosId as DomainPosId;
use OpenLoyalty\Component\Pos\Domain\PosRepository;
use OpenLoyalty\Component\Transaction\Domain\Command\RegisterTransaction;
use OpenLoyalty\Component\Transaction\Domain\PosId;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetailsRepository;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TransactionXmlImportConverter.
 */
class TransactionXmlImportConverter extends AbstractXMLImportConverter
{
    /**
     * @var UuidGeneratorInterface
     */
    protected $uuidGenerator;

    /**
     * @var PosRepository
     */
    protected $posRepository;

    /**
     * @var TransactionDetailsRepository
     */
    private $transactionDetailsRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * TransactionXmlImportConverter constructor.
     *
     * @param UuidGeneratorInterface       $uuidGenerator
     * @param PosRepository                $posRepository
     * @param TransactionDetailsRepository $transactionDetailsRepository
     * @param TranslatorInterface          $translator
     */
    public function __construct(UuidGeneratorInterface $uuidGenerator, PosRepository $posRepository, TransactionDetailsRepository $transactionDetailsRepository, TranslatorInterface $translator)
    {
        $this->uuidGenerator = $uuidGenerator;
        $this->posRepository = $posRepository;
        $this->transactionDetailsRepository = $transactionDetailsRepository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(\SimpleXMLElement $element)
    {
        $this->checkValidNodes(
            $element,
            [
                'documentNumber' => ['required' => true],
                'purchaseDate' => ['format' => XmlNodeValidator::DATE_TIME_FORMAT, 'required' => true],
                'customer' => ['required' => true],
                'documentType' => ['required' => true],
                'items' => ['required' => true],
                'customer/name' => ['required' => true],
            ]
        );

        $transactionData = [
            'documentNumber' => (string) $element->{'documentNumber'},
            'purchasePlace' => (string) $element->{'purchasePlace'},
            'purchaseDate' => \DateTime::createFromFormat(DATE_ATOM, (string) $element->{'purchaseDate'}),
            'documentType' => (string) $element->{'documentType'},
        ];

        $customerData = [
            'name' => (string) $element->{'customer'}->{'name'},
            'email' => (string) $element->{'customer'}->{'email'},
            'nip' => (string) $element->{'customer'}->{'nip'},
            'phone' => (string) $element->{'customer'}->{'phone'},
            'loyaltyCardNumber' => (string) $element->{'customer'}->{'loyaltyCardNumber'},
            'address' => [
                'street' => (string) $element->{'customer'}->{'address'}->{'street'},
                'address1' => (string) $element->{'customer'}->{'address'}->{'address1'},
                'city' => (string) $element->{'customer'}->{'address'}->{'city'},
                'country' => (string) $element->{'customer'}->{'address'}->{'country'},
                'province' => (string) $element->{'customer'}->{'address'}->{'province'},
                'postal' => (string) $element->{'customer'}->{'address'}->{'postal'},
            ],
        ];

        $items = [];
        foreach ($element->{'items'}->children() as $item) {
            $labels = [];
            foreach ($item->{'labels'}->children() as $label) {
                $labels[] = [
                    'key' => (string) $label->{'key'},
                    'value' => (string) $label->{'value'},
                ];
            }

            $items[] = [
                'sku' => ['code' => (string) $item->{'sku'}->{'code'}],
                'name' => (string) $item->{'name'},
                'quantity' => (string) $item->{'quantity'},
                'grossValue' => (string) $item->{'grossValue'},
                'category' => (string) $item->{'category'},
                'maker' => (string) $item->{'maker'},
                'labels' => $labels,
            ];
        }

        /** @var DomainPosId $posId */
        $posId = null;
        if (isset($element->{'posId'}) || isset($element->{'posIdentifier'})) {
            $posId = $this->getPos($element);
            if (!$posId) {
                throw new \InvalidArgumentException(sprintf(
                    $this->translator->trans('transaction.import.pos_not_found'),
                    (string) $element->{'posIdentifier'}
                ));
            }
        }

        if ($this->transactionDetailsRepository->findTransactionByDocumentNumber((string) $element->{'documentNumber'})) {
            throw new \InvalidArgumentException($this->translator->trans('transaction.document_number_should_be_unique'));
        }

        return new RegisterTransaction(
            new TransactionId($this->uuidGenerator->generate()),
            $transactionData,
            $customerData,
            $items,
            $posId ? new PosId((string) $posId) : null,
            null,
            null,
            null,
            (string) $element->{'revisedDocument'}
        );
    }

    /**
     * @param \SimpleXMLElement $element
     *
     * @return null|DomainPosId
     */
    protected function getPos(\SimpleXMLElement $element): ?DomainPosId
    {
        /** @var null|Pos $pos */
        $pos = null;
        if (isset($element->{'posId'})) {
            $posId = new DomainPosId((string) $element->{'posId'});
            $pos = $this->posRepository->byId($posId);
            if ($pos) {
                return $pos->getPosId();
            }
        }

        if (isset($element->{'posIdentifier'})) {
            $pos = $this->posRepository->oneByIdentifier((string) $element->{'posIdentifier'});
            if ($pos) {
                return $pos->getPosId();
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(\SimpleXMLElement $element): string
    {
        return (string) $element->{'documentNumber'};
    }
}
