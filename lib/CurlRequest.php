<?php
/**
 * class CurlRequest
 *
 * handles get, post, put, delete HTTP requests
 */

namespace PHPAP21;

use PHPAP21\Exception\CurlException;
use PHPAP21\Exception\ResourceRateLimitException;
use PHPAP21\Http\Status;

class CurlRequest
{
    /**
     * HTTP Code of the last executed request
     *
     * @var integer
     */
    public static $lastHttpCode;

    public static $lastHttpResponse;

    /**
     * HTTP response headers of last executed request
     *
     * @var array
     */
    public static $lastHttpResponseHeaders = array();

    /**
     * Curl additional configuration
     *
     * @var array
     */
    protected static $config = array();

    /**
     * Initialize the curl resource
     *
     * @param string $url
     * @param array $httpHeaders
     *
     * @return resource
     */
    protected static function init($url, $httpHeaders = array())
    {
        // Create Curl resource
        $ch = curl_init();

        // Set URL
        curl_setopt($ch, CURLOPT_URL, $url);

        // SSL verification - enabled by default for security
        // Can be disabled via config for development: [CURLOPT_SSL_VERIFYPEER => false]
        $sslVerify = !isset(self::$config[CURLOPT_SSL_VERIFYPEER]) || self::$config[CURLOPT_SSL_VERIFYPEER];
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $sslVerify ? 2 : 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslVerify);

        //Return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHPAP21/ap21-sdk-php');

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        foreach (self::$config as $option => $value) {
            curl_setopt($ch, $option, $value);
        }

        $headers = array();
        foreach ($httpHeaders as $key => $value) {
            $headers[] = "$key: $value";
        }
        //Set HTTP Headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        return $ch;
    }

    /**
     * Implement a GET request and return output
     *
     * @param string $url
     * @param array $httpHeaders
     *
     * @return string
     */
    public static function get($url, $httpHeaders = array())
    {
        Log::debug(sprintf("%s->url", __METHOD__), [$url]);
        //Initialize the Curl resource
        $ch = self::init($url, $httpHeaders);
        return self::processRequest($ch);
    }

    /**
     * Implement a POST request and return output
     *
     * @param string $url
     * @param array $data
     * @param array $httpHeaders
     *
     * @return string
     */
    public static function post($url, $data, $httpHeaders = array())
    {
        $ch = self::init($url, $httpHeaders);
        //Set the request type
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        return self::processRequest($ch);
    }

    /**
     * Implement a PUT request and return output
     *
     * @param string $url
     * @param array $data
     * @param array $httpHeaders
     *
     * @return string
     */
    public static function put($url, $data, $httpHeaders = array())
    {
        $ch = self::init($url, $httpHeaders);
        //set the request type
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        return self::processRequest($ch);
    }

    /**
     * Implement a DELETE request and return output
     *
     * @param string $url
     * @param array $httpHeaders
     *
     * @return string
     */
    public static function delete($url, $httpHeaders = array())
    {
        $ch = self::init($url, $httpHeaders);
        //set the request type
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        return self::processRequest($ch);
    }

    /**
     * Set curl additional configuration
     *
     * @param array $config
     */
    public static function config($config = array())
    {
        self::$config = $config;
    }

    /**
     * Execute a request, release the resource and return output
     *
     * @param resource $ch
     *
     * @throws CurlException if curl request is failed with error
     *
     * @return string
     */
    protected static function processRequest($ch)
    {
        // Rate limit retry settings
        $maxRetries = isset(self::$config['MaxRetries']) ? (int)self::$config['MaxRetries'] : 5;
        $retryCount = 0;

        // Handle 429 rate limit with retry and backoff
        while ($retryCount <= $maxRetries) {
            // Enforce minimum gap between API calls
            Ap21SDK::checkApiCallLimit();

            $output   = curl_exec($ch);
            $response = new CurlResponse($output);
            $response->getHeaders();

            self::$lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (self::$lastHttpCode != 429) {
                if (Status::httpResponse(self::$lastHttpCode)) {
                    self::$lastHttpResponse = Status::httpResponse(self::$lastHttpCode);
                }
                elseif (http_response_code(CurlRequest::$lastHttpCode)) {
                    self::$lastHttpResponse = http_response_code(CurlRequest::$lastHttpCode);
                }
                break;
            }

            // Max retries exceeded
            if ($retryCount >= $maxRetries) {
                throw new ResourceRateLimitException(
                    sprintf("Rate limit exceeded after %d retries", $maxRetries),
                    429
                );
            }

            // Use Retry-After header if available, otherwise exponential backoff
            $retryAfter = $response->getHeader('Retry-After');
            if ($retryAfter !== null) {
                $sleepTime = (int)$retryAfter;
            } else {
                // Exponential backoff: 1s, 2s, 4s, 8s, 16s
                $sleepTime = pow(2, $retryCount);
            }

            Log::debug(sprintf("%s: Rate limited (429), retry %d/%d after %ds", __METHOD__, $retryCount + 1, $maxRetries, $sleepTime));
            sleep($sleepTime);
            $retryCount++;
        }

        if (curl_errno($ch)) {
            throw new Exception\CurlException(curl_errno($ch) . ' : ' . curl_error($ch));
        }

        // close curl resource to free up system resources
        curl_close($ch);

        self::$lastHttpResponseHeaders = $response->getHeaders();

        return $response->getBody();
    }

}
