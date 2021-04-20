<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ImportBundle\Importer;

use OpenLoyalty\Component\Import\Infrastructure\FileImporter;
use OpenLoyalty\Component\Import\Infrastructure\ImportConvertException;
use OpenLoyalty\Component\Import\Infrastructure\ImporterProcessor;
use OpenLoyalty\Component\Import\Infrastructure\ImportProcessException;
use OpenLoyalty\Component\Import\Infrastructure\ImportResult;
use OpenLoyalty\Component\Import\Infrastructure\ImportResultItem;
use OpenLoyalty\Component\Import\Infrastructure\ProcessImportResult;
use OpenLoyalty\Component\Import\Infrastructure\XMLFileStreamer;
use OpenLoyalty\Component\Import\Infrastructure\XMLImportConverter;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class XMLImporter.
 */
class XMLImporter implements FileImporter
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /** @var ImporterProcessor */
    protected $processor;

    /** @var XMLImportConverter */
    protected $converter;

    /** @var XMLFileStreamer */
    protected $xmlStreamer;

    /**
     * XMLImporter constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return XMLFileStreamer
     */
    public function getXmlStreamer(): XMLFileStreamer
    {
        return $this->xmlStreamer;
    }

    /**
     * @param XMLFileStreamer $xmlStreamer
     */
    public function setXmlStreamer(XMLFileStreamer $xmlStreamer)
    {
        $this->xmlStreamer = $xmlStreamer;
    }

    /**
     * @return ImporterProcessor
     */
    public function getProcessor(): ImporterProcessor
    {
        return $this->processor;
    }

    /**
     * @param ImporterProcessor $processor
     */
    public function setProcessor(ImporterProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @return XMLImportConverter
     */
    public function getConverter(): XMLImportConverter
    {
        return $this->converter;
    }

    /**
     * @param XMLImportConverter $converter
     */
    public function setConverter(XMLImportConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * @param string $filePath
     *
     * @return ImportResult
     */
    public function import(string $filePath): ImportResult
    {
        if (!$this->processor) {
            throw new \InvalidArgumentException('Processor class is required');
        }

        if (!$this->converter) {
            throw new \InvalidArgumentException('Converter class is required');
        }

        $streamer = $this->xmlStreamer->getStreamer($filePath);

        $results = [];
        $entity = null;

        while ($node = $streamer->getNode()) {
            try {
                $xmlNode = $this->getXmlNode($node);
                $identifier = $this->getIdentifier($xmlNode);
                $entity = $this->convertStage($xmlNode);
                $result = $this->processStage($entity);

                $results[] = ImportResultItem::success($entity, $identifier, $result);
            } catch (ImportConvertException $ex) {
                $results[] = ImportResultItem::error(
                    $node,
                    $identifier ?? '',
                    $this->translator->trans(sprintf('Convert exception: %s', $ex->getMessage())),
                    $ex
                );
                continue;
            } catch (ImportProcessException $ex) {
                $results[] = ImportResultItem::error(
                    $entity,
                    $identifier ?? '',
                    $this->translator->trans(sprintf('Process exception: %s', $ex->getMessage())),
                    $ex
                );
                continue;
            }
        }

        return new ImportResult($results);
    }

    /**
     * @param mixed $entity
     *
     * @return ProcessImportResult
     *
     * @throws ImportProcessException
     */
    protected function processStage($entity)
    {
        try {
            return $this->processor->processItem($entity);
        } catch (\Exception $ex) {
            throw new ImportProcessException($this->translator->trans($ex->getMessage()), $ex->getCode(), $ex);
        }
    }

    /**
     * @param \SimpleXMLElement $xmlNode
     *
     * @return mixed
     *
     * @throws ImportConvertException
     */
    protected function convertStage(\SimpleXMLElement $xmlNode)
    {
        try {
            return $this->converter->convert($xmlNode);
        } catch (\Exception $ex) {
            throw new ImportConvertException($this->translator->trans($ex->getMessage()), $ex->getCode(), $ex);
        }
    }

    /**
     * @param string $node
     *
     * @return \SimpleXMLElement
     *
     * @throws ImportConvertException
     */
    protected function getXmlNode(string $node): \SimpleXMLElement
    {
        $xmlNode = @simplexml_load_string($node);
        if ($xmlNode == false) {
            throw new ImportConvertException(sprintf('XML has invalid structure'));
        }

        return $xmlNode;
    }

    /**
     * @param \SimpleXMLElement $xmlNode
     *
     * @return string
     *
     * @throws ImportConvertException
     */
    protected function getIdentifier(\SimpleXMLElement $xmlNode): string
    {
        try {
            return $this->converter->getIdentifier($xmlNode);
        } catch (\Exception $ex) {
            throw new ImportConvertException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
}
