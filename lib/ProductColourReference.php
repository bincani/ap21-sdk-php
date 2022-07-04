<?php
/**
 * class ProductColourReference
 */

namespace PHPAP21;

class ProductColourReference extends HTTPXMLResource
{
    protected $productColourReferences = [];

    protected $resourceKey = 'ProductColourReference';

    public $dom;

    public $countEnabled = false;

    public $readOnly = true;

    /**
     * processResponse
     *
     * @param SimpleXML $xml
     * @param string $dataKey
     *
     * @return [] $productColourReferences
     */
    public function processResponse($xml, $dataKey = null) {

        // Convert SimpleXML to DOMDocument
        $this->dom = new \DOMDocument;
        $this->dom->loadXML($xml->asXML());

        //Log::debug(__METHOD__, [get_class($this->dom)]);

        $productColourReferences = $this->dom->getElementsByTagName('ProductColourReference');
        Log::debug(__METHOD__, [count($productColourReferences)]);

        // no ProductColourReference
        if ($productColourReferences->length == 0) {
            $this->processEntity($this->dom);
        }
        else {
            foreach ($productColourReferences as $productColourReference) {
                $this->processEntity($productColourReference);
            }
        }
        //Log::debug(__METHOD__, [$this->productColourReferences]);
        if (count($this->productColourReferences) == 1) {
            $this->productColourReferences = array_pop($this->productColourReferences);
        }
        return $this->productColourReferences;
    }

    /**
     * processEntity
     *
     * @return array
     */
    protected function processEntity($productColourReference) {
        $id = $this->innerHTML($productColourReference->getElementsByTagName('ClrId')[0]);
        Log::debug(__METHOD__, [$id, $productColourReference->nodeValue]);
        $this->productColourReferences[$id] = [
            'id' => $id
        ];
    }

}