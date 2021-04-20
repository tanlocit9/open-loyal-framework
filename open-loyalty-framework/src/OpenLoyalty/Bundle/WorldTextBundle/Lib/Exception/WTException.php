<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\WorldTextBundle\Lib\Exception;

/**
 * Class WTException.
 */
class WTException extends \Exception
{
    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $desc;

    /**
     * @var string
     */
    protected $error;

    /**
     * WTException constructor.
     *
     * @param string $message
     * @param string $json
     * @param int    $code
     */
    public function __construct($message, $json, $code = 0)
    {
        $this->status = '1';
        try {
            $data = json_decode($json, true);
            $this->desc = $data['desc'];
            $this->error = $data['error'];
        } catch (\Exception $ex) {
        }
        parent::__construct($message, $code);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
