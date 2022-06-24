<?php
/**
 * class HTTPXMLResource
 */

namespace PHPAP21;

use PHPAP21\Exception\ApiException;
use PHPAP21\Exception\SdkException;
use PHPAP21\Exception\CurlException;
use Psr\Http\Message\ResponseInterface;

abstract class HTTPXMLResource extends HTTPResource
{
    protected $count = 0;

    public function __construct($id = null, $parentResourceUrl = '') {
        Log::debug(sprintf("%s->id: %s", __METHOD__, $id), [func_get_args()] );
        parent::__construct(...func_get_args());
    }

    /**
     * Generate a HTTP GET request and return results as an array
     *
     * @param array $urlParams Check Ap21 API reference of the specific resource for the list of URL parameters
     * @param string $url
     * @param string $dataKey Keyname to fetch data from response array
     *
     * @uses HttpRequestXml::get() to send the HTTP request
     *
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    public function get($urlParams = array(), $url = null, $dataKey = null)
    {
        if (!$url) {
            $url  = $this->generateUrl($urlParams);
        }
        Log::debug(sprintf("%s->url: %s", __METHOD__, $url) );
        if (!$dataKey) {
            $dataKey = $this->id ? $this->resourceKey : $this->pluralizeKey();
        }
        Log::debug(sprintf("%s->dataKey: %s", __METHOD__, $dataKey), [$this->id]);

        if (
            array_key_exists('useCache', $this->config)
            &&
            $this->config['useCache']
        ) {
            Log::debug(sprintf("%s->readCache: %s", __METHOD__, $dataKey) );
            $cacheFile = sprintf("%s/../data/get/%s.xml", __DIR__, strtolower($dataKey));
            if (!file_exists($cacheFile)) {
                throw new \Exception(sprintf("cannot open cache file '%s'!", $cacheFile));
            }
            Log::debug(sprintf("%s->read[%d]: %s", __METHOD__, filesize($cacheFile), $cacheFile));
            $response = file_get_contents(
                $cacheFile,
                $use_include_path = false,
                $context = null,
                $offset = 0,
                $length = 10000
            );
        }
        else {
            $response = HttpRequestXml::get($url, $this->httpHeaders);
            //Log::debug(sprintf("%s->response", __METHOD__), [$response]);
        }
        return $this->processResponse($response, $dataKey);
    }

    /**
     * Get count for the number of resources available
     *
     * @param array $urlParams Check Ap21 API reference of the specific resource for the list of URL parameters
     *
     * @throws SdkException
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return integer
     */
    public function count($urlParams = array())
    {
        if (!$this->countEnabled) {
            throw new SdkException("Count is not available for " . $this->getResourceName());
        }
        $url = $this->generateUrl($urlParams, 'count');
        return $this->get(array(), $url, 'count');
    }

    /**
     * Search within the resouce
     *
     * @param mixed $query
     *
     * @throws SdkException if search is not enabled for the resouce
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    public function search($query)
    {
        if (!$this->searchEnabled) {
            throw new SdkException("Search is not available for " . $this->getResourceName());
        }
        if (!is_array($query)) {
            $query = array('query' => $query);
        }
        $url = $this->generateUrl($query, 'search');
        return $this->get(array(), $url);
    }

    /**
     * Call POST method to create a new resource
     *
     * @param array $dataArray Check Ap21 API reference of the specific resource for the list of required and optional data elements to be provided
     * @param string $url
     * @param bool $wrapData wraps the date in an array ['resource' => $data]
     *
     * @uses HttpRequestXml::post() to send the HTTP request
     *
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    public function post($dataArray, $url = null, $wrapData = false)
    {
        if (!$url) {
            $url = $this->generateUrl();
        }
        if ($wrapData && !empty($dataArray)) {
            $dataArray = $this->wrapData($dataArray);
        }
        $response = HttpRequestXml::post($url, $dataArray, $this->httpHeaders);
        return $this->processResponse($response, $this->resourceKey);
    }

    /**
     * Call PUT method to update an existing resource
     *
     * @param array $dataArray Check Ap21 API reference of the specific resource for the list of required and optional data elements to be provided
     * @param string $url
     * @param bool $wrapData wraps the date in an array ['resource' => $data]
     *
     * @uses HttpRequestXml::put() to send the HTTP request
     *
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    public function put($dataArray, $url = null, $wrapData = false)
    {
        if (!$url) {
            $url = $this->generateUrl();
        }
        if ($wrapData && !empty($dataArray)) {
            $dataArray = $this->wrapData($dataArray);
        }
        $response = HttpRequestXml::put($url, $dataArray, $this->httpHeaders);
        return $this->processResponse($response, $this->resourceKey);
    }

    /**
     * Call DELETE method to delete an existing resource
     *
     * @param array $urlParams Check Ap21 API reference of the specific resource for the list of URL parameters
     * @param string $url
     *
     * @uses HttpRequestXml::delete() to send the HTTP request
     *
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array an empty array will be returned if the request is successfully completed
     */
    public function delete($urlParams = array(), $url = null)
    {
        if (!$url) {
            $url = $this->generateUrl($urlParams);
        }
        $response = HttpRequestXml::delete($url, $this->httpHeaders);
        return $this->processResponse($response);
    }

    /**
     * Wrap data array with resource key
     *
     * @param array $dataArray
     * @param string $dataKey
     *
     * @return array
     */
    public function wrapData($dataArray, $dataKey = null)
    {
        if (!$dataKey) {
            $dataKey = $this->getResourcePostKey();
        }
        return array($dataKey => $dataArray);
    }

    /**
     * Convert an array to string
     *
     * @param array $array
     *
     * @internal
     *
     * @return string
     */
    public function castString($array)
    {
        if ( ! is_array($array)) return (string) $array;

        $string = '';
        $i = 0;
        foreach ($array as $key => $val) {
            //Add values separated by comma
            //prepend the key string, if it's an associative key
            //Check if the value itself is another array to be converted to string
            $string .= ($i === $key ? '' : "$key - ") . $this->castString($val) . ', ';
            $i++;
        }

        //Remove trailing comma and space
        $string = rtrim($string, ', ');

        return $string;
    }

    public function getLinks($responseHeaders){
        $this->nextLink = $this->getLink($responseHeaders, 'next');
        $this->prevLink = $this->getLink($responseHeaders, 'previous');
    }

    public function getLink($responseHeaders, $type='next'){

        if(array_key_exists('x-ap21-api-version', $responseHeaders)
            && $responseHeaders['x-ap21-api-version'] < '2019-07'){
            return null;
        }

        if(!empty($responseHeaders['link'])) {
            if (stristr($responseHeaders['link'], '; rel="'.$type.'"') > -1) {
                $headerLinks = explode(',', $responseHeaders['link']);
                foreach ($headerLinks as $headerLink) {
                    if (stristr($headerLink, '; rel="'.$type.'"') === -1) {
                        continue;
                    }

                    $pattern = '#<(.*?)>; rel="'.$type.'"#m';
                    preg_match($pattern, $headerLink, $linkResponseHeaders);
                    if ($linkResponseHeaders) {
                        return $linkResponseHeaders[1];
                    }
                }
            }
        }

        return null;
    }

    public function getPrevLink(){
        return $this->prevLink;
    }

    public function getNextLink(){
        return $this->nextLink;
    }

    public function getUrlParams($url) {
        if ($url) {
            $parts = parse_url($url);
            return $parts['query'];
        }
        return '';
    }

    public function getNextPageParams(){
        $nextPageParams = [];
        parse_str($this->getUrlParams($this->getNextLink()), $nextPageParams);
        return $nextPageParams;
    }

    public function getPrevPageParams(){
        $nextPageParams = [];
        parse_str($this->getUrlParams($this->getPrevLink()), $nextPageParams);
        return $nextPageParams;
    }

    /**
     * validateResponse
     *
     * @param [type] $response
     * @return void
     */
    public function validateResponse($response) {
        // check response
        if (!$response) {
            throw new ApiException($message = "no response", CurlRequest::$lastHttpCode);
        }

        // see if there are errors inside a HTML document
        if (
            get_class($response) == "DOMDocument"
            &&
            preg_match("/DOCTYPE html/", $response->saveHTML())
        ) {
            $errorNodes = $response->getElementsByTagName('errorcode');
            if ($errorNodes->length > 0) {
                Log::debug(__METHOD__, ["html", $response->saveHTML()]);
                $errCode = $this->innerHTML($errorNodes[0]);
                $errDesc = $this->innerHTML($response->getElementsByTagName('description')[0]);
                throw new ApiException(sprintf("%d - %s", $errCode, $errDesc));
            }
        }

        // parse xml
        if (
            get_class($response) != "DOMDocument"
            &&
            !$this->xml = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOERROR |  LIBXML_ERR_NONE)
        ) {
            throw new \Exception("invalid xml!");
        }

        // see if there are errors in the XML document
        if (preg_match("/Ap21Error/i", $this->xml->getName()) ) {
            $errCode = (string)$this->xml->ErrorCode;
            $errDesc = (string)$this->xml->Description;
            throw new ApiException(sprintf("%d - %s", $errCode, $errDesc));
        }

    }
}
