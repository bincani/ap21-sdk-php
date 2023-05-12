<?php
/**
 * class Stores
 *
 * GET https://retailapi.apparel21.com/RetailAPI/Stores/2701?countryCode=AU
 */

namespace PHPAP21;

class Store extends HTTPXMLResource
{
    protected $stores = [];

    protected $resourceKey = 'Store';

    public $dom;

    public $countEnabled = false;

    public $readOnly = true;

    /**
     * @return array
     */
    public function get($urlParams = array(), $url = null, $dataKey = null)
    {
        // implement version
        $this->httpHeaders['Accept'] = sprintf("version_4.0");
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

        // Convert SimpleXML to DOMDocument
        $this->dom = new \DOMDocument;
        $this->dom->loadXML($xml->asXML());

        Log::debug(__METHOD__, [get_class($this->dom)]);

        $stores = $this->dom->getElementsByTagName('Store');
        //Log::debug(__METHOD__, ["stores.count: " . count($stores)]);

        // no colour
        if ($stores->length == 0) {
            $this->processEntity($this->dom);
        }
        else {
            foreach ($stores as $store) {
                $this->processEntity($store);
            }
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
    protected function processEntity($store) {
        //Log::debug(__METHOD__, [$store->nodeValue]);
        $storeId = $this->innerHTML($store->getElementsByTagName('StoreId')[0]);
        $code = $this->innerHTML($store->getElementsByTagName('Code')[0]);
        $storeNbr = $this->innerHTML($store->getElementsByTagName('StoreNo')[0]);
        $name = $this->innerHTML($store->getElementsByTagName('Name')[0]);
        $address1 = $this->innerHTML($store->getElementsByTagName('Address1')[0]);
        $address2 = $this->innerHTML($store->getElementsByTagName('Address2')[0]);
        $city = $this->innerHTML($store->getElementsByTagName('City')[0]);
        $state = $this->innerHTML($store->getElementsByTagName('State')[0]);
        $postcode = $this->innerHTML($store->getElementsByTagName('Postcode')[0]);
        $country = $this->innerHTML($store->getElementsByTagName('Country')[0]);
        $email = "";
        if ($store->getElementsByTagName('Email')[0]) {
            $email = $this->innerHTML($store->getElementsByTagName('Email')[0]);
        }
        $this->stores[$code] = [
            'store_id'  => $storeId,
            'code'      => $code,
            'store_nbr' => $storeNbr,
            'name'      => $name,
            'address1'  => $address1,
            'address2'  => $address2,
            'city'      => $city,
            'state'     => $state,
            'postcode'  => $postcode,
            'country'   => $country,
            'email'     => $email
        ];
    }
}