<?php
/**
 * class StockChanged
 *
 * Addtional query parameters
 *      None
 *
 */

namespace PHPAP21;

class StockChanged extends HTTPXMLResource
{
    protected $stores = [];

    protected $storeCnt = 0;

    protected $resourceKey = 'StockChanged';

    protected $childResource = array(
        'stockByStore'
    );

    /**
     * @return array
     */
    public function get($urlParams = array(), $url = null, $dataKey = null)
    {
        // implement version
        $this->httpHeaders['Accept'] = sprintf("version_1.0");
        return parent::get($urlParams, $url, $dataKey);
    }

    /**
     * processResponse
     *
     * @param SimpleXML $xml
     * @param string $dataKey
     *
     * @return [] $stores
     */
    public function processResponse($xml, $dataKey = null) {

        $lastResponseHeaders = CurlRequest::$lastHttpResponseHeaders;
        //Log::debug(__METHOD__, $lastResponseHeaders);
        if (!$xml || CurlRequest::$lastHttpCode != 200) {
            if (!CurlRequest::$lastHttpResponse) {
                CurlRequest::$lastHttpResponse = "no response";
            }
            throw new ApiException(sprintf("%s - %s", CurlRequest::$lastHttpCode, CurlRequest::$lastHttpResponse));
        }

        // Convert SimpleXML to DOMDocument
        $this->dom = new \DOMDocument;
        $this->dom->loadXML($xml->asXML());

        Log::debug(__METHOD__, [get_class($this->dom)]);

        $stores = $this->dom->getElementsByTagName('store');
        Log::debug(__METHOD__, ["stores.count: " . count($stores)]);

        foreach ($stores as $store) {
            $this->processEntity($store);
        }
        //Log::debug(__METHOD__, [$this->stores]);
        if (count($this->stores) == 1) {
            $this->stores = array_pop($this->stores);
        }
        return $this->stores;
    }

    /**
     * processEntity
     *
     * @return array
     */
    protected function processEntity(\DOMElement $store) {

        $skus = [];

        //echo $store->asXML();
        $storeFreestock = 0;
        $storeid = (int)$store->getAttribute('storeid');
        $lastChanged = (string)$store->getAttribute('lastChanged');
        //Log::debug(__METHOD__, [$storeid, $lastChanged, $store->childNodes->length]);
        foreach($store->childNodes as $sku) {
            // @var DOMElement $sku
            if ($sku->nodeName != 'sku') {
                continue;
            }
            $skuid = (string)$sku->getAttribute('skuid');
            $skus[$skuid] = (int)$sku->getAttribute('freeStock');
        }
        $this->stores[$storeid] = [
            'id'            => $storeid,
            'lastChanged'   => $lastChanged,
            'skus'          => $skus,
        ];
        //echo sprintf(sprintf("%s->%s", __METHOD__, print_r($store, true)));
        return $store;
    }

    /**
     * processCollection
     *
     * @return array
     */
    protected function processCollection($xml) {
        $stores = [];
        // loop SimpleXMLElements
        foreach($xml->children() as $store) {
            $id = $store['storeid'];
            //Log::debug(sprintf("%s->%s", __METHOD__, $id));
            $stores["$id"] = $this->processEntity($store);
        }
        return $stores;
    }

    public function pluralizeKey()
    {
        // no pluralize
        return $this->resourceKey;
    }
}