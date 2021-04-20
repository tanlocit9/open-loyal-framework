<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\WorldTextBundle\Lib;

use OpenLoyalty\Bundle\WorldTextBundle\Lib\Exception\WTException;

/**
 * Class WorldText.
 */
class WorldText
{
    const USER_AGENT = 'world-text-php/2.0.0';
    const GET = 1;
    const PUT = 2;
    const DELETE = 3;

    /**
     * @var string
     */
    private $apiVersion = '2.0.0';

    /**
     * @var string
     */
    private $apiState = 'dev';

    /**
     * @var string
     */
    protected $apiKey = '';

    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var string
     */
    private $simulate = '';

    /**
     * @var string
     */
    private $protocol = '';

    /**
     * @var string
     */
    private $host = 'sms.world-text.com';

    /**
     * @var string
     */
    private $baseUrl = '/v2.0';

    /**
     * WorldText constructor.
     *
     * @param string $id
     * @param string $apiKey
     */
    public function __construct($id, $apiKey)
    {
        $this->id = $id;
        $this->apiKey = $apiKey;

        // Check we have cURL...
        if (!extension_loaded('curl')) {
            trigger_error("cURL is required to process the HTTPS/HTTP requests\n".
                "to use the World Text REST API.\n", E_USER_ERROR);
        }

        // Now check that it supports HTTPS...
        $this->setSecure(true);
    }

    /**
     * @param bool $value
     */
    public function setSecure($value)
    {
        if ($value == true) {
            $version = curl_version();
            $ssl_supported = ($version['features'] & CURL_VERSION_SSL);

            if ($ssl_supported) {
                $this->protocol = 'https://';
            } else {
                $this->protocol = 'http://';
            }
        } else {
            $this->protocol = 'http://';
        }
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @param bool $value
     */
    public function setSimulated($value)
    {
        if ($value == true) {
            $this->simulate = '&sim';
        } else {
            $this->simulate = '';
        }
    }

    /**
     * @param string $method
     * @param string $call
     * @param array  $data
     *
     * @return array
     *
     * @throws WTException
     */
    protected function callResource($method, $call, $data = array())
    {
        // Add the api key and ID to the parameter data...

        $data = array_merge($data, array('id' => $this->id, 'key' => $this->apiKey));

        $params = '';
        foreach ($data as $k => $v) {
            if ($v === reset($data)) {
                $params .= "?$k=".urlencode($v);
            } else {
                $params .= "&$k=".urlencode($v);
            }
        }

        $url = $this->protocol.$this->host.$this->baseUrl.$call.$params.$this->simulate;

        // Parameters assembled, send it...
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));

        switch ($method) {
            case self::GET:
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                break;
            case self::PUT:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case self::DELETE:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl, CURLOPT_POST, true);
                break;
            default:
                // Oops...
                throw new WTException('Unknown HTTP Method', $call);
                break;
        }

        if (!($response = curl_exec($curl))) {
            // Failed...
            throw new \Exception(curl_error($curl));
        }

        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($responseCode > 307) {
            throw new WTException("request failed: $call", $response, $responseCode);
        }

        if ($responseCode == 307) {
            // redirect received - which we are going to ignore for now
        }

        if (!$processedResponse = $this->processResponse($response)) {
            // Failed...
            throw new \Exception(curl_error($curl));
        } else {
            return array(
                'data' => $processedResponse,
                'http_code' => (int) $responseCode,
            );
        }
    }

    /**
     * @param string $response
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function processResponse($response)
    {
        // Decode into array...
        $decoded = json_decode($response, true);

        if ($decoded === null) {
            throw new \Exception('Could not decode JSON from response - comms error or 500?');
        } else {
            // Host change can come back from anything...
            // (not just a 307)
            if (isset($decoded['hostname'])) {
                // Server gave us a host...
                // Ignore for now.
                //$this->host = $decoded['hostname'];	 // Update stored host
            }

            return $decoded;
        }
    }

    /**
     * @param string $string
     *
     * @return false|int
     */
    public static function isUTF8($string)
    {
        return preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $string);
    }
}
