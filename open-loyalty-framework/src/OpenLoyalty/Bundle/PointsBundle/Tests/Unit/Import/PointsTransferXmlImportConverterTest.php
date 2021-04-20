<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\PointsBundle\Tests\Unit\Import;

use Broadway\UuidGenerator\UuidGeneratorInterface;
use OpenLoyalty\Bundle\PointsBundle\Import\AccountProviderInterface;
use OpenLoyalty\Bundle\PointsBundle\Import\PointsTransferXmlImportConverter;
use OpenLoyalty\Bundle\PointsBundle\Service\PointsTransfersManager;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\Command\AddPoints;
use OpenLoyalty\Component\Account\Domain\Command\SpendPoints;
use OpenLoyalty\Component\Account\Domain\CustomerId;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\ReadModel\AccountDetails;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PointsTransferXmlImportConverterTest.
 */
class PointsTransferXmlImportConverterTest extends TestCase
{
    /**
     * @var PointsTransferXmlImportConverter
     */
    private $converter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $accountProviderMock = $this->getMockBuilder(AccountProviderInterface::class)->getMock();
        $accountProviderMock->method('provideOne')->willReturn(new AccountDetails(
            new AccountId('00000000-0000-aaaa-0000-000000000000'),
            new CustomerId('00000000-0000-bbbb-0000-000000000000')
        ));

        $uuidGeneratorMock = $this->getMockBuilder(UuidGeneratorInterface::class)->getMock();
        $uuidGeneratorMock->method('generate')->willReturn('00000000-cccc-0000-0000-000000000000');

        $translatorMock = $this->getMockBuilder(TranslatorInterface::class)->getMock();

        $pointsTransfersManagerMock = $this->getMockBuilder(PointsTransfersManager::class)->disableOriginalConstructor()->getMock();
        $pointsTransfersManagerMock->method('createAddPointsTransferInstance')->willReturn(
            new AddPointsTransfer(new PointsTransferId('00000000-dddd-0000-0000-000000000000'), 2)
        );

        $this->converter = new PointsTransferXmlImportConverter(
            $uuidGeneratorMock,
            $pointsTransfersManagerMock,
            $accountProviderMock,
            $translatorMock
        );
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Component\Import\Infrastructure\ImportConvertException
     */
    public function it_checks_that_validity_duration_node_is_required(): void
    {
        $data = new \SimpleXMLElement('
            <pointsTransfer>
                <customerId>00000000-0000-474c-b092-b0dd880c07e2</customerId>
                <points>12</points>
                <type>spending</type>
            </pointsTransfer>'
        );
        $this->converter->convert($data);
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Component\Import\Infrastructure\ImportConvertException
     */
    public function it_checks_that_points_node_is_required(): void
    {
        $data = new \SimpleXMLElement('
            <pointsTransfer>
                <customerId>00000000-0000-474c-b092-b0dd880c07e2</customerId>
                <type>spending</type>
                <validityDuration>5</validityDuration>
            </pointsTransfer>'
        );
        $this->converter->convert($data);
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Component\Import\Infrastructure\ImportConvertException
     */
    public function it_checks_that_type_node_is_required(): void
    {
        $data = new \SimpleXMLElement('
            <pointsTransfer>
                <customerId>00000000-0000-474c-b092-b0dd880c07e2</customerId>
                <points>12</points>
                <validityDuration>5</validityDuration>
            </pointsTransfer>'
        );
        $this->converter->convert($data);
    }

    /**
     * @test
     * @expectedException \OpenLoyalty\Component\Import\Infrastructure\ImportConvertException
     */
    public function it_checks_that_at_least_one_customer_field_is_required(): void
    {
        $data = new \SimpleXMLElement('
            <pointsTransfer>
                <points>12</points>
                <type>spending</type>
                <validityDuration>5</validityDuration>
            </pointsTransfer>'
        );
        $this->converter->convert($data);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_checks_that_type_at_least_one_customer_field_is_possible_to_pass(): void
    {
        $fields = [
            'customerId' => '00000000-0000-474c-b092-b0dd880c07e2',
            'customerEmail' => 'user@oloy.com',
            'customerPhoneNumber' => '+48123123123',
            'customerLoyaltyCardNumber' => 'ABC81245',
        ];

        foreach ($fields as $field => $value) {
            $data = new \SimpleXMLElement("
            <pointsTransfer>
                <$field>$value</$field>
                <points>12</points>
                <type>spending</type>
                <validityDuration>5</validityDuration>
            </pointsTransfer>
            ");
            $this->converter->convert($data);
        }
    }

    /**
     * @test
     */
    public function it_creates_spend_points_command(): void
    {
        $data = new \SimpleXMLElement('
            <pointsTransfer>
                <customerId>00000000-0000-474c-b092-b0dd880c07e2</customerId>
                <points>12</points>
                <type>spending</type>
                <validityDuration>5</validityDuration>
            </pointsTransfer>'
        );
        $this->assertInstanceOf(SpendPoints::class, $this->converter->convert($data));
    }

    /**
     * @test
     */
    public function it_creates_add_points_command(): void
    {
        $data = new \SimpleXMLElement('
            <pointsTransfer>
                <customerId>00000000-0000-474c-b092-b0dd880c07e2</customerId>
                <points>12</points>
                <type>adding</type>
                <validityDuration>5</validityDuration>
            </pointsTransfer>'
        );
        $this->assertInstanceOf(AddPoints::class, $this->converter->convert($data));
    }
}
