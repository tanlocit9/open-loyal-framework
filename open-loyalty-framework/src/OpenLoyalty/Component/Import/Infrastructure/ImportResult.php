<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Import\Infrastructure;

/**
 * Class ImportResult.
 */
class ImportResult
{
    /** @var ImportResultItem[] */
    private $items;

    /**
     * @var int
     */
    private $totalProcessed = 0;

    /**
     * @var int
     */
    private $totalSuccess = 0;

    /**
     * @var int
     */
    private $totalFailed = 0;

    /**
     * ImportResult constructor.
     *
     * @param ImportResultItem[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
        $this->buildResult();
    }

    /**
     * Rebuild results structure.
     */
    protected function buildResult()
    {
        foreach ($this->items as $item) {
            ++$this->totalProcessed;
            if ($item->getStatus() == ImportResultItem::SUCCESS) {
                ++$this->totalSuccess;
            } else {
                ++$this->totalFailed;
            }
        }
    }

    /**
     * @return ImportResultItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getTotalProcessed(): int
    {
        return $this->totalProcessed;
    }

    /**
     * @return int
     */
    public function getTotalSuccess(): int
    {
        return $this->totalSuccess;
    }

    /**
     * @return int
     */
    public function getTotalFailed(): int
    {
        return $this->totalFailed;
    }
}
