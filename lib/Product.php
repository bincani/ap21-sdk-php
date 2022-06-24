<?php
/**
 * class Product
 *
 * Clrs
 * The list of colours for this product
 *
 * Addtional query parameters
 *  CustomData = true
 *      List of Cards of Custom Data on product level
 *
 */

namespace PHPAP21;

use PHPAP21\Exception\ApiException;

class Product extends HTTPXMLResource
{
    protected $xml;
    protected $products = [];
    protected $productCnt = 0;

    protected $resourceKey = 'product';

    protected $childResource = array(
        'ProductImage'      => 'Image',
        'ProductVariant'    => 'Variant',
        'Metafield',
        'Event'
    );

    protected $customGetActions = array (
        'product_ids' => 'productIds',
    );

    /**
     * processResponse
     *
     * @param [type] $responseArray
     * @param [type] $dataKey
     * @return [] $products
     */
    public function processResponse($response, $dataKey = null) {

        // check response
        if (!$response) {
            throw new ApiException($message = "no response", CurlRequest::$lastHttpCode);
        }
        // parse xml
        if (!$this->xml = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOERROR |  LIBXML_ERR_NONE)) {
            throw new \Exception("invalid xml!");
        }

        //Log::debug(__METHOD__, [$dataKey, $this->xml->getName(), $this->pluralizeKey() ]);

        // sanity check
        if (strcasecmp($dataKey, $this->xml->getName()) !== 0) {
            throw new Exception(sprintf("invalid response %s! expecting %s", $this->xml->getName(), $dataKey));
        }

        // process collection
        if (strcasecmp($this->pluralizeKey(), $this->xml->getName()) === 0) {
            $att = $this->xml->attributes();
            $this->productCnt = $att['TotalRows'];
            return $this->processCollection();
        }
        else {
            return $this->processEntity($this->xml);
        }
    }

    /**
     * processEntity
     *
     * @return array
     */
    protected function processEntity($product) {
        $id = $product->Id;
        Log::debug(sprintf("%s->%s|%s|%s", __METHOD__, $id, $product->Code, $product->Name));
        $children = [];
        foreach($product->Clrs->children() as $colour) {
            $cCode = (string)$colour->Code;
            $cName = (string)$colour->Name;
            //Log::debug(sprintf("col->%s|%s", $cCode, cName));
            foreach($colour->SKUs->children() as $sku) {
                if (!array_key_exists($cCode, $children)) {
                    $children[$cCode] = [];
                }
                $children[$cCode][(string)$sku->Barcode] = [
                    'colour' => (string)$colour->Name,
                    'size'   => (string)$sku->SizeCode,
                    'price'  => (float)$sku->Price
                ];
            }
        }
        $product = [
            'code'     => (string)$product->Code,
            'name'     => (string)$product->Name,
            'children' => $children
        ];
        return $product;
    }

    /**
     * processCollection
     *
     * @return array
     */
    protected function processCollection() {
        // loop SimpleXMLElements
        foreach($this->xml->children() as $product) {
            $id = $product->Id;
            $products["$id"] = $this->processEntity($product);
        }
        return $products;
    }
}