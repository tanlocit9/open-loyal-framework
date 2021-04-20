<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Import\Infrastructure;

/**
 * Class ImportResultItem.
 */
class ImportResultItem
{
    const SUCCESS = 'success';
    const ERROR = 'error';

    /**
     * @var string
     */
    private $status;

    /**
     * @var mixed
     */
    private $object;

    /**
     * @var string
     */
    private $message = null;

    /**
     * @var \Exception
     */
    private $exception = null;

    /**
     * @var ProcessImportResult
     */
    private $processImportResult;

    /**
     * @var string
     */
    private $identifier;

    /**
     * ImportResultItem constructor.
     *
     * @param string                   $status
     * @param string                   $identifier
     * @param null                     $object
     * @param null|string              $message
     * @param \Exception|null          $exception
     * @param ProcessImportResult|null $processImportResult
     */
    public function __construct(
        string $status,
        string $identifier,
        $object = null,
        ?string $message = null,
        \Exception $exception = null,
        ProcessImportResult $processImportResult = null
    ) {
        $this->status = $status;
        $this->identifier = $identifier;
        $this->object = $object;
        $this->message = $message;
        $this->exception = $exception;
        $this->processImportResult = $processImportResult;
    }

    /**
     * @param null|mixed               $object
     * @param string|null              $identify
     * @param ProcessImportResult|null $processResult
     * @param null|string              $message
     *
     * @return ImportResultItem
     */
    public static function success(
        $object = null,
        ?string $identify = null,
        ProcessImportResult $processResult = null,
        ?string $message = null
    ) {
        return new self(self::SUCCESS, $identify, $object, $message, null, $processResult);
    }

    /**
     * @param null|mixed      $object
     * @param string|null     $identify
     * @param null|string     $message
     * @param \Exception|null $exception
     *
     * @return ImportResultItem
     */
    public static function error($object = null, $identify = null, $message = null, \Exception $exception = null)
    {
        return new self(self::ERROR, $identify, $object, $message, $exception);
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        if (!empty($this->message)) {
            return $this->message;
        }

        if (null !== $this->exception) {
            return $this->exception->getMessage();
        }

        return;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return \Exception
     */
    public function getException(): \Exception
    {
        return $this->exception;
    }

    /**
     * @return ProcessImportResult
     */
    public function getProcessImportResult()
    {
        return $this->processImportResult;
    }
}
