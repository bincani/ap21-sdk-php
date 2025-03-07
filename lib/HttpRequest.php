<?php
namespace PHPAP21;

use PHPAP21\CurlRequest;

/**
 * Class HttpRequest
 *
 * Prepare the data / headers for http requests, make the call and decode the response
 * Accepts data in array format returns data in array format
 *
 * @uses CurlRequest
 *
 * @package PHPAP21
 */
class HttpRequest
{
    /**
     * HTTP request headers
     *
     * @var array
     */
    protected static $httpHeaders;

    /**
     * Prepared string to be posted with request
     *
     * @var string
     */
    public static $postData;

    /**
     * Prepare the data and request headers before making the call
     *
     * @param array $httpHeaders
     * @param array $postData
     *
     * @return void
     */
    protected static function prepareRequest($httpHeaders = [], $postData = null)
    {
        self::$postData = $postData;
        self::$httpHeaders = $httpHeaders;
        self::$httpHeaders['Content-type'] = 'application/html';
        if (!empty($postData)) {
            Log::debug(__METHOD__, [$postData]);
            self::$httpHeaders['Content-Length'] = strlen(self::$postData);
        }
    }

    /**
     * Implement a GET request
     *
     * @param string $url
     * @param array $httpHeaders
     *
     * @return array
     */
    public static function get($url, $httpHeaders = array())
    {
        self::prepareRequest($httpHeaders);
        return self::processRequest('GET', $url);
    }

    /**
     * Implement a POST request
     *
     * @param string $url
     * @param array $postData
     * @param array $httpHeaders
     *
     * @return array
     */
    public static function post($url, $postData, $httpHeaders = array())
    {
        self::prepareRequest($httpHeaders, $postData);
        return self::processRequest('POST', $url);
    }

    /**
     * Implement a PUT request
     *
     * @param string $url
     * @param array $postData
     * @param array $httpHeaders
     *
     * @return array
     */
    public static function put($url, $postData, $httpHeaders = array())
    {
        self::prepareRequest($httpHeaders, $postData);
        return self::processRequest('PUT', $url);
    }

    /**
     * Implement a DELETE request
     *
     * @param string $url
     * @param array $httpHeaders
     *
     * @return array
     */
    public static function delete($url, $httpHeaders = array())
    {
        self::prepareRequest($httpHeaders);
        return self::processRequest('DELETE', $url);
    }

    /**
     * Process a curl request and return response
     *
     * @param string $method Request http method ('GET', 'POST', 'PUT' or 'DELETE')
     * @param string $url Request URL
     *
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    public static function processRequest($method, $url) {
        $retry = 0;
        $raw = null;
        while(true) {
            try {
                switch($method) {
                    case 'GET':
                        Log::debug(sprintf("%s->url: %s", __METHOD__, $url));
                        $raw = CurlRequest::get($url, self::$httpHeaders);
                        //Log::debug(sprintf("%s->raw: %s", __METHOD__, $raw));
                        break;
                    case 'POST':
                        $raw = CurlRequest::post($url, self::$postData, self::$httpHeaders);
                        break;
                    case 'PUT':
                        $raw = CurlRequest::put($url, self::$postData, self::$httpHeaders);
                        break;
                    case 'DELETE':
                        $raw = CurlRequest::delete($url, self::$httpHeaders);
                        break;
                    default:
                        throw new \Exception("unexpected request method '$method'");
                }
                return self::processResponse($raw);
            }
            catch(\Exception $e) {
                if (!self::shouldRetry($raw, $e, $retry++)) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Evaluate if send again a request
     *
     * @param string $response Raw request response
     * @param exception $error the request error occured
     * @param integer $retry the current number of retry
     *
     * @return bool
     */
    public static function shouldRetry($response, $error, $retry) {
        $config = Ap21SDK::$config;

        if (isset($config['RequestRetryCallback'])) {
           return $config['RequestRetryCallback']($response, $error, $retry);
        }

        return false;
    }

    /**
     * Decode response
     *
     * @param string $response
     *
     * @return array
     */
    protected static function processResponse($response)
    {
        //Log::debug(__METHOD__, [$response]);
        //Log::debug(__METHOD__, [CurlRequest::$lastHttpResponseHeaders]);

        // checks if the content we're receiving isn't empty, to avoid the warning
        if (empty($response)) {
            return false;
        }

        $responseArray = self::loadHtml($response);

        if ($responseArray === null) {
            // Something went wrong, Checking HTTP Codes
            $httpOK         = 200; // Request Successful, OK.
            $httpCreated    = 201; // Create Successful.
            $httpDeleted    = 204; // Delete Successful
            $httpOther      = 303; // See other (headers)

            $lastHttpResponseHeaders = CurlRequest::$lastHttpResponseHeaders;

            //should be null if any other library used for http calls
            $httpCode = CurlRequest::$lastHttpCode;

            if ($httpCode == $httpOther && array_key_exists('location', $lastHttpResponseHeaders)) {
                return ['location' => $lastHttpResponseHeaders['location']];
            }

            if ($httpCode != null && $httpCode != $httpOK && $httpCode != $httpCreated && $httpCode != $httpDeleted) {
                throw new Exception\CurlException("Request failed with HTTP Code $httpCode.", $httpCode);
            }
        }

        return $responseArray;
    }

    /**
     * loadHtml
     *
     * Load HTML from a string
     *
     * @return DOMDocument
     */
    protected static function loadHtml($response) {
        // converts all special characters to utf-8
        //$response = mb_convert_encoding($response, 'HTML-ENTITIES', 'UTF-8');
        $response = mb_encode_numericentity(
            htmlspecialchars_decode(
                htmlentities($response, ENT_NOQUOTES, 'UTF-8', false), ENT_NOQUOTES
            ), [0x80, 0x10FFFF, 0, ~0],
            'UTF-8'
        );

        $dom = new \DOMDocument('1.0', 'utf-8');
        // turning off some errors
        libxml_use_internal_errors(true);
        // it loads the content without adding enclosing html/body tags and also the doctype declaration
        //$dom->LoadHTML($response, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $dom->LoadHTML($response);
        return $dom;
    }

    /**
     * parseToArray
     *
     * @param [type] $xpath
     * @param [type] $class
     * @return void
     */
    private static function parseToArray($xpath, $tag, $class = '')
    {
        $resultarray = [];
        $xpathquery = sprintf("//%s%s", $tag, $class ? sprintf("[@class='%s']", $class) : '');
        $elements = $xpath->query($xpathquery);
        if (!is_null($elements)) {
            foreach ($elements as $element) {
                $nodes = $element->childNodes;
                foreach ($nodes as $node) {
                  $resultarray[] = $node->nodeValue;
                }
            }
        }
        return $resultarray;
    }
}
