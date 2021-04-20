<?php

namespace OpenLoyalty\Bundle\ImportBundle\Tests\Unit\Importer;

use OpenLoyalty\Bundle\ImportBundle\Importer\XMLImporter;
use OpenLoyalty\Component\Import\Infrastructure\ImporterProcessor;
use OpenLoyalty\Component\Import\Infrastructure\ImportResultItem;
use OpenLoyalty\Component\Import\Infrastructure\ProcessImportResult;
use OpenLoyalty\Component\Import\Infrastructure\XMLFileStreamer;
use OpenLoyalty\Component\Import\Infrastructure\XMLImportConverter;
use PHPUnit\Framework\TestCase;
use Prewk\XmlStringStreamer;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class XmlImporterTest.
 */
class XmlImporterTest extends TestCase
{
    /**
     * @param int   $correctNodes
     * @param array ...$nodes
     *
     * @return XMLImporter
     */
    protected function prepareXmlImporter(int $correctNodes, ...$nodes)
    {
        $translatorMock = $this->getMockForAbstractClass(TranslatorInterface::class);
        $xmlImporter = new XMLImporter($translatorMock);

        $someStreamer = $this->getMockBuilder(XmlStringStreamer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNode'])
            ->getMock();
        $someStreamer->method('getNode')
            ->willReturnOnConsecutiveCalls(...$nodes);

        $stringStreamer = $this->getMockBuilder(XMLFileStreamer::class)
            ->setMethods(['getStreamer'])
            ->getMock();
        $stringStreamer->method('getStreamer')->willReturn($someStreamer);

        $xmlImportConverter = $this->createMock(XMLImportConverter::class);

        $xmlImportConverter->method('convert')->willReturnCallback(function (\SimpleXMLElement $element) {
            $value = (string) $element->{'name'};

            if ($value == 'invalid_convert_name_2') {
                throw new \Exception('Test exception (conversion)');
            }

            return 'converted_'.$value;
        });

        $importerProcessor = $this->createMock(ImporterProcessor::class);
        $importerProcessor->expects($this->exactly($correctNodes))
            ->method('processItem')->willReturnCallback(function ($entity) {
                if ($entity == 'converted_invalid_process_name_2') {
                    throw new \Exception('Test exception (processing)');
                }

                return new ProcessImportResult(new \stdClass());
            });

        $xmlImporter->setXmlStreamer($stringStreamer);
        $xmlImporter->setProcessor($importerProcessor);
        $xmlImporter->setConverter($xmlImportConverter);

        return $xmlImporter;
    }

    /**
     * @test
     */
    public function import_all_elements_from_nodes_with_success()
    {
        $xmlImporter = $this->prepareXmlImporter(
            2,
            '<item><name>name_1</name></item>',
            '<item><name>name_2</name></item>'
        );
        $result = $xmlImporter->import('test.xml');

        $convertedObjects = array_map(function (ImportResultItem $x) {
            return $x->getObject();
        }, $result->getItems());

        $this->assertTrue(
            $convertedObjects == ['converted_name_1', 'converted_name_2']
        );

        $statuses = array_map(function (ImportResultItem $x) {
            return $x->getStatus();
        }, $result->getItems());

        $this->assertTrue(
            $statuses == [ImportResultItem::SUCCESS, ImportResultItem::SUCCESS]
        );
    }

    /**
     * @test
     */
    public function import_all_elements_from_nodes_with_convert_error()
    {
        $xmlImporter = $this->prepareXmlImporter(
            1,
            '<item><name>name_1</name></item>',
            '<item><name>invalid_convert_name_2</name></item>'
        );
        $result = $xmlImporter->import('test.xml');

        $convertedObjects = array_map(function (ImportResultItem $x) {
            return $x->getObject();
        }, $result->getItems());

        $this->assertTrue(
            $convertedObjects == ['converted_name_1', '<item><name>invalid_convert_name_2</name></item>']
        );

        $statuses = array_map(function (ImportResultItem $x) {
            return $x->getStatus();
        }, $result->getItems());

        $this->assertTrue(
            $statuses == [ImportResultItem::SUCCESS, ImportResultItem::ERROR]
        );
    }

    /**
     * @test
     */
    public function import_all_elements_from_nodes_with_process_error()
    {
        $xmlImporter = $this->prepareXmlImporter(
            2,
            '<item><name>name_1</name></item>',
            '<item><name>invalid_process_name_2</name></item>'
        );
        $result = $xmlImporter->import('test.xml');

        $convertedObjects = array_map(function (ImportResultItem $x) {
            return $x->getObject();
        }, $result->getItems());

        $this->assertTrue(
            $convertedObjects == ['converted_name_1', 'converted_invalid_process_name_2']
        );

        $statuses = array_map(function (ImportResultItem $x) {
            return $x->getStatus();
        }, $result->getItems());

        $this->assertTrue(
            $statuses == [ImportResultItem::SUCCESS, ImportResultItem::ERROR]
        );
    }
}
