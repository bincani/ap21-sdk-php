<?php
/**
 * class AllStyles
 *
 * Addtional query parameters
 *      None
 *
 */

namespace PHPAP21\Freestock;

use PHPAP21\HttpRequestXml as HttpRequestXml;
use PHPAP21\Freestock as Freestock;
use PHPAP21\Log;

class AllStyles extends Freestock
{
    const PAGE_LIMIT = 0;
    const DEFAULT_PAGE_ROWS = 500;

    protected $freestocks = [];
    protected $freestockLimit = 0;

    protected $totalProducts = 0;
    protected $totalPages = 0;

    protected $resource = 'FreeStock';
    protected $resourceKey = 'AllStyles';

    /**
     * processResponse
     *
     * @param SimpleXML $xml
     * @param string $dataKey
     *
     * @return [] $allStyles
     */
    public function processResponse($xml, $dataKey = null) {
        $dataKey = $this->resource;
        Log::debug(__METHOD__, [$dataKey, $xml->getName()]);
        // sanity check
        if (strcasecmp($dataKey, $xml->getName()) !== 0) {
            throw new \Exception(sprintf("invalid response %s! expecting %s", $xml->getName(), $dataKey));
        }
        // process collection
        if (strcasecmp($dataKey, $xml->getName()) === 0) {
            $att = $xml->attributes();
            $this->totalProducts = (int)$att['TotalRows'];
            Log::debug(sprintf("%s->totalProducts: %d", __METHOD__, $this->totalProducts), []);
            return parent::processCollection($xml);
        }
        else {
            return parent::processEntity($xml);
        }
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
        // limit
        if (array_key_exists('limit', $urlParams)) {
            $this->freestockLimit = $urlParams['limit'];
            unset($urlParams['limit']);
        }

        if (!$url) {
            $url  = $this->generateUrl($urlParams);
        }
        Log::debug(sprintf("%s->url: %s", __METHOD__, $url) );
        if (!$dataKey) {
            $dataKey = $this->id ? $this->resourceKey : $this->pluralizeKey();
        }
        Log::debug(sprintf("%s->dataKey: %s", __METHOD__, $dataKey), [$this->id]);

        $this->httpHeaders['Accept'] = sprintf("version_4.0");
        Log::debug(sprintf("%s->httpHeaders", __METHOD__), $this->httpHeaders);

        $response = HttpRequestXml::get($url, $this->httpHeaders);
        Log::debug(sprintf("%s->response.length: %d", __METHOD__, strlen($response)), []);

        // implement paging
        if (array_key_exists('startRow', $urlParams)) {
            // set up paging
            $page = 1;
            $startRow = $urlParams['startRow'];
            $urlParams['pageRows'] = array_key_exists('pageRows', $urlParams) ? $urlParams['pageRows'] : self::DEFAULT_PAGE_ROWS;
            // set to limit if greater than limit
            if ($this->freestockLimit != 0 && $urlParams['pageRows'] > $this->freestockLimit) {
                $urlParams['pageRows'] = $this->freestockLimit;
            }

            $this->xml = $this->processResponse($response, $dataKey);
            // calculate the total number of pages
            $this->totalPages = ceil($this->totalProducts / $urlParams['pageRows']);

            Log::info(sprintf("%s->processNextPage", __METHOD__), [
                sprintf('page: %d/%d', $page, $this->totalPages),
                'startRow:' . $urlParams['startRow'],
                'pageRows:' . $urlParams['pageRows'],
                'total:' . $this->totalProducts,
                'limit:' . $this->freestockLimit
            ]);

            // check we arent already at our limit
            Log::debug(sprintf("%s->check limit %d >= %d", __METHOD__, count($this->xml), $this->freestockLimit), []);
            if ($this->freestockLimit != 0 && count($this->xml) >= $this->freestockLimit) {
                Log::debug(sprintf("%s->product limit %d reached!", __METHOD__, $this->freestockLimit), []);
            }
            else {
                do {
                    // set startRow to the next amount
                    $urlParams['startRow'] = ($urlParams['pageRows'] * $page) + 1;
                    $url = $this->generateUrl($urlParams);

                    $response = HttpRequestXml::get($url, $this->httpHeaders);
                    Log::info(sprintf("%s->response.length: %d", __METHOD__, strlen($response)), []);

                    if (empty($response) || strlen($response) == 0) {
                        Log::debug(sprintf("%s->end reached!", __METHOD__), []);
                        break;
                    }
                    if (self::PAGE_LIMIT != 0 && $page < self::PAGE_LIMIT) {
                        Log::debug(sprintf("%s->page limit %d reached!", __METHOD__, self::PAGE_LIMIT), []);
                        break;
                    }
                    $page++;
                    Log::info(sprintf("%s->processNextPage", __METHOD__), [
                        sprintf('page: %d/%d', $page, $this->totalPages),
                        'startRow:' . $urlParams['startRow'],
                        'pageRows:' . $urlParams['pageRows'],
                        'total:' . $this->totalProducts,
                        'limit:' . $this->freestockLimit,
                        'count:' . count($this->xml),
                        sprintf("yes: %d", (count($this->xml) >= $this->freestockLimit))
                    ]);
                    $freestocks = $this->processResponse($response, $dataKey);

                    if ($freestocks && is_array($freestocks)) {
                        $this->xml = array_merge($this->xml, $freestocks);
                    }
                    if ($this->freestockLimit != 0 && count($this->xml) >= $this->freestockLimit) {
                        Log::debug(sprintf("%s->product limit %d reached!", __METHOD__, $this->freestockLimit), []);
                        break;
                    }
                }
                while($response);
            }
        }
        else {
            Log::debug(sprintf("%s->%s->processResponse", __METHOD__, get_class($this)), [get_class($response)]);
            $this->xml = $this->processResponse($response, $dataKey);
        }
        return $this->xml;
    }

    /**
     * getTotalPages
     *
     * @return int
     */
    public function getTotalPages() {
        return $this->totalPages;
    }

    /**
     * getTotalProducts
     *
     * @return int
     */
    public function getTotalProducts() {
        return $this->totalProducts;
    }
}