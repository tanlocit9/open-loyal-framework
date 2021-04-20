<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\WorldTextBundle\Lib;

use OpenLoyalty\Bundle\WorldTextBundle\Lib\Exception\WTException;

/**
 * Class WorldTextSms.
 */
class WorldTextSms extends WorldText
{
    /**
     * WorldTextSms constructor.
     *
     * @param string $id
     * @param string $apiKey
     */
    public function __construct($id, $apiKey)
    {
        parent::__construct($id, $apiKey);
    }

    /**
     * @param string $id
     * @param string $apiKey
     *
     * @return WorldTextSms
     */
    public static function CreateSmsInstance($id, $apiKey)
    {
        return new self($id, $apiKey);
    }

    /**
     * @param string      $dst
     * @param string      $txt
     * @param null|string $src
     * @param bool|null   $multipart
     * @param bool        $simulate
     *
     * @return mixed
     *
     * @throws WTException
     */
    public function send($dst, $txt, $src = null, $multipart = null, $simulate = false)
    {
        $data = array(
            'dstaddr' => $dst,
            'txt' => $txt,
        );

        if ($simulate === true) {
            $data['sim'] = 1;
        }

        if (WorldText::isUTF8($txt)) {
            $data = array_merge($data, array('enc' => 'UnicodeBigUnmarked'));
        }

        if ($src !== null) {
            $data = array_merge($data, array('srcaddr' => $src));
        }

        if ($multipart) {
            $data = array_merge($data, array('multipart' => $multipart));
        }

        try {
            $returned = $this->callResource(self::PUT, '/sms/send', $data);
        } catch (WTException $ex) {
            throw $ex;
        }

        return  $returned['data']['message'];
    }

    /**
     * @param string $msgID
     *
     * @return array
     *
     * @throws WTException
     */
    public function query($msgID)
    {
        $data = array(
            'msgid' => $msgID,
        );

        return $this->callResource(self::GET, '/sms/query', $data);
    }

    /**
     * @param string $dst
     *
     * @return array
     *
     * @throws WTException
     */
    public function cost($dst)
    {
        $data = array(
            'dstaddr' => $dst,
        );

        return $this->callResource(self::GET, '/sms/cost', $data);
    }
}
