<?php
/**
 * class ProductColourReference
 */

namespace PHPAP21;

class ProductColourReference extends HTTPXMLResource
{
    protected $productColourReferences = [];

    protected $resourceKey = 'ProductColourReference';

    public $countEnabled = false;

    public $readOnly = true;

    /**
     * processResponse
     *
     * @param [type] $response
     * @param [type] $dataKey
     *
     * @return [] $productColourReferences
     */
    public function processResponse($response, $dataKey = null) {

        if (!$response) {
            $message = "no response";
            throw new ApiException($message, CurlRequest::$lastHttpCode);
        }

        // parse xml
        if (!$this->xml = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOERROR |  LIBXML_ERR_NONE)) {
            throw new \Exception("invalid xml!");
        }

        $this->xml = new \DOMDocument;
        $this->xml->loadXML($response);

        // SimpleXMLElement
        Log::debug(__METHOD__, [get_class($this->xml)]);
        $productColourReferences = $this->xml->getElementsByTagName('ProductColourReference');
        foreach ($productColourReferences as $productColourReference) {
            $id = $this->innerHTML($productColourReference->getElementsByTagName('ClrId')[0]);
            //Log::debug(__METHOD__, [$id, $productColourReference->nodeValue]);
            $this->productColourReferences[$id] = [
                'id' => $id
            ];
        }
        Log::debug(__METHOD__, [$this->productColourReferences]);
        if (count($this->productColourReferences) == 1) {
            $this->productColourReferences = array_pop($this->productColourReferences);
        }
        return $this->productColourReferences;
    }
}