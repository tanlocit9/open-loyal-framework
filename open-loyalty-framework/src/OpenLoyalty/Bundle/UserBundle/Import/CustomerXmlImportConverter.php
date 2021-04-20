<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\UserBundle\Import;

use OpenLoyalty\Component\Customer\Domain\Model\Gender;
use OpenLoyalty\Component\Import\Infrastructure\AbstractXMLImportConverter;
use OpenLoyalty\Component\Import\Infrastructure\Validator\XmlNodeValidator;

/**
 * Class CustomerXmlImportConverter.
 */
class CustomerXmlImportConverter extends AbstractXMLImportConverter
{
    /**
     * {@inheritdoc}
     */
    public function convert(\SimpleXMLElement $element)
    {
        $this->checkValidNodes(
            $element,
            [
                'email' => ['required' => true],
                'firstName' => ['required' => true],
                'lastName' => ['required' => true],
                'active' => [
                    'required' => true,
                    'format' => XmlNodeValidator::BOOL_FORMAT,
                ],
                'sendActivationMail' => [
                    'format' => XmlNodeValidator::BOOL_FORMAT,
                ],
                'birthDate' => [
                    'format' => XmlNodeValidator::DATE_FORMAT,
                ],
                'agreement1' => [
                    'required' => true,
                    'format' => XmlNodeValidator::BOOL_FORMAT,
                ],
                'agreement2' => [
                    'format' => XmlNodeValidator::BOOL_FORMAT,
                ],
                'agreement3' => [
                    'format' => XmlNodeValidator::BOOL_FORMAT,
                ],
                'gender' => [
                    'required' => true,
                    'format' => XmlNodeValidator::VALID_CONST_FORMAT,
                    'values' => [Gender::MALE, Gender::FEMALE, Gender::NOT_DISCLOSED],
                ],
                'levelId' => [
                    'format' => XmlNodeValidator::UUID_FORMAT,
                ],
                'posId' => [
                    'format' => XmlNodeValidator::UUID_FORMAT,
                ],
                'sellerId' => [
                    'format' => XmlNodeValidator::UUID_FORMAT,
                ],
            ]
        );

        $customerData = [
            'email' => (string) $element->{'email'},
            'firstName' => (string) $element->{'firstName'},
            'lastName' => (string) $element->{'lastName'},
            'gender' => (string) $element->{'gender'},
            'agreement1' => $this->returnBool($element->{'agreement1'}),
            'agreement2' => $this->returnBool($element->{'agreement2'}),
            'agreement3' => $this->returnBool($element->{'agreement3'}),
            'birthDate' => \DateTime::createFromFormat(XmlNodeValidator::DATE_CONVERT_FORMAT, $element->{'birthDate'}),
            'active' => $this->returnBool($element->{'active'}),
            'sendActivationMail' => $this->returnBool($element->{'sendActivationMail'}),
        ];

        if ($element->{'levelId'}) {
            $customerData['levelId'] = (string) $element->{'levelId'};
        }

        if ($element->{'posId'}) {
            $customerData['posId'] = (string) $element->{'posId'};
        }

        if ($element->{'sellerId'}) {
            $customerData['sellerId'] = (string) $element->{'sellerId'};
        }

        if ($element->{'loyaltyCardNumber'}) {
            $customerData['loyaltyCardNumber'] = (string) $element->{'loyaltyCardNumber'};
        }

        if ($element->{'phone'}) {
            $customerData['phone'] = (string) $element->{'phone'};
        }

        if ($element->{'company'}) {
            $customerData['company'] = [
                'name' => (string) $element->{'company'}->{'name'},
                'nip' => (string) $element->{'company'}->{'nip'},
            ];
        }

        if ($element->{'address'}) {
            $customerData['address'] = [
                'address1' => (string) $element->{'address'}->{'address1'},
                'address2' => (string) $element->{'address'}->{'address2'},
                'city' => (string) $element->{'address'}->{'city'},
                'country' => (string) $element->{'address'}->{'country'},
                'postal' => (string) $element->{'address'}->{'postal'},
                'street' => (string) $element->{'address'}->{'street'},
                'province' => (string) $element->{'address'}->{'province'},
            ];
        }

        if ($element->{'labels'}) {
            $labels = [];
            foreach ($element->{'labels'}->children() as $label) {
                $labels[] = [
                    'key' => (string) $label->{'key'},
                    'value' => (string) $label->{'value'},
                ];
            }
            $customerData['labels'] = $labels;
        }

        return $customerData;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(\SimpleXMLElement $element): string
    {
        return (string) $element->{'email'};
    }
}
