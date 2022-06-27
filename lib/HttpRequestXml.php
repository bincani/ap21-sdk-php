<?php
namespace PHPAP21;

/**
 * Class HttpRequestXml
 *
 * Prepare the data / headers for XML requests, make the call and decode the response
 * Accepts data in array format returns data in array format
 *
 * @uses CurlRequest
 *
 * @package PHPAP21
 */
class HttpRequestXml extends HttpRequest
{
    /**
     * Prepare the data and request headers before making the call
     *
     * @param array $httpHeaders
     * @param array $dataArray
     *
     * @return void
     */
    protected static function prepareRequest($httpHeaders = [], $postData = null)
    {
        self::$postData = $postData;
        self::$httpHeaders = $httpHeaders;
        /**
         * causes
         * 5010 - Person has been updated by another user - Update Time Stamp has changed from  to 24/06/2022 5:38:28 PM
         * 5045 - The requested major API version is not supported
         * 5151 - Person ID in request is different from Person ID in the payload
         */
        //self::$httpHeaders['Accept'] = 'version_4.0';
        if (!empty($postData)) {
            self::$httpHeaders['Content-type'] = 'text/xml';
            self::$httpHeaders['Content-Length'] = strlen(self::$postData);
        }
        Log::debug(sprintf("%s->httpHeaders", __METHOD__), self::$httpHeaders);
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
     * Process a curl request and return decoded response
     *
     * @param string $method Request http method ('GET', 'POST', 'PUT' or 'DELETE')
     * @param string $url Request URL
     *
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    public static function processRequest($method, $url, $useCache = false) {
        $retry = 0;
        $raw = null;
        while(true) {
            try {
                switch($method) {
                    case 'GET':
                        Log::debug(sprintf("%s->url: %s", __METHOD__, $url));
                        $raw = CurlRequest::get($url, self::$httpHeaders);
                        Log::debug(sprintf("%s->raw: %s", __METHOD__, $raw));
                        die();
                        break;
                    case 'POST':
                        Log::debug(sprintf("%s->url: %s", __METHOD__, $url));
                        $raw = CurlRequest::post($url, self::$postData, self::$httpHeaders);
                        break;
                    case 'PUT':
                        Log::debug(sprintf("%s->url: %s", __METHOD__, $url));
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
}
