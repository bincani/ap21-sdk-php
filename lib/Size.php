<?php
/**
 * class Sizes
 *
 * GET https://retailapi.apparel21.com/RetailAPI/Sizes?countryCode=AU&query=1,1506
 */

namespace PHPAP21;

class Size extends HTTPXMLResource
{
    protected $sizes = [];

    protected $resourceKey = 'Size';

    public $dom;

    public $countEnabled = false;

    public $readOnly = true;

    /**
     * processResponse
     *
     * @param SimpleXML $xml
     * @param string $dataKey
     *
     * @return [] $sizes
     */
    public function processResponse($xml, $dataKey = null) {

        // Convert SimpleXML to DOMDocument
        $this->dom = new \DOMDocument;
        $this->dom->loadXML($xml->asXML());

        Log::debug(__METHOD__, [get_class($this->dom)]);

        $sizes = $this->dom->getElementsByTagName('Size');
        //Log::debug(__METHOD__, ["sizes.count: " . count($sizes)]);

        // no size
        if ($sizes->length == 0) {
            $this->processEntity($this->dom);
        }
        else {
            foreach ($sizes as $size) {
                $this->processEntity($size);
            }
        }
        //Log::debug(__METHOD__, [$this->sizes]);
        if (count($this->sizes) == 1) {
            $this->sizes = array_pop($this->sizes);
        }
        return $this->sizes;
    }

    /**
     * processEntity
     *
     * @return array
     */
    protected function processEntity($size) {
        $code = $this->innerHTML($size->getElementsByTagName('Code')[0]);
        Log::debug(__METHOD__, [$code, $size->nodeValue]);
        $this->sizes[$code] = [
            'code' => $code
        ];
    }

}