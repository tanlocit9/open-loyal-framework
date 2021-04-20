<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\ImportBundle\Importer;

use OpenLoyalty\Component\Import\Infrastructure\XMLFileStreamer;
use Prewk\XmlStringStreamer;

/**
 * Class XMLUniqueNodeStringStreamer.
 */
class XMLUniqueNodeStringFileStreamer implements XMLFileStreamer
{
    /**
     * Chunk size.
     */
    const CHUNK_SIZE = 65536; // 1024 * 64;

    /**
     * @var string
     */
    protected $nodeName;

    /**
     * XMLUniqueNodeStringStreamer constructor.
     *
     * @param string $nodeName
     */
    public function __construct(string $nodeName)
    {
        $this->nodeName = $nodeName;
    }

    /**
     * @return string
     */
    public function getNodeName(): string
    {
        return $this->nodeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getStreamer(string $filePath): XmlStringStreamer
    {
        $stream = new XmlStringStreamer\Stream\File($filePath, self::CHUNK_SIZE);
        $parser = new XmlStringStreamer\Parser\UniqueNode(['uniqueNode' => $this->getNodeName()]);

        return new XmlStringStreamer($parser, $stream);
    }
}
