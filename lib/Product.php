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
     * @param SimpleXML $xml
     * @param string $dataKey
     *
     * @return [] $products
     */
    public function processResponse($xml, $dataKey = null) {

        //Log::debug(__METHOD__, [$dataKey, $this->xml->getName(), $this->pluralizeKey() ]);

        // sanity check
        if (strcasecmp($dataKey, $xml->getName()) !== 0) {
            throw new Exception(sprintf("invalid response %s! expecting %s", $xml->getName(), $dataKey));
        }

        // process collection
        if (strcasecmp($this->pluralizeKey(), $xml->getName()) === 0) {
            $att = $xml->attributes();
            $this->productCnt = $att['TotalRows'];
            return $this->processCollection($xml);
        }
        else {
            return $this->processEntity($xml);
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

        $references = [];
        foreach($product->References->children() as $reference) {
            $rTypeId = (string)$reference->ReferenceTypeId;
            $rId = (string)$reference->Id;
            //Log::debug(sprintf("%s->rId->%s|%s", __METHOD__, $rTypeId, $rId));
            $references[$rTypeId] = [
                'id'    => $rTypeId,
                'key'   => $rId
            ];
        }

        $customData = [];
        if ($product->CustomData) {
            foreach($product->CustomData->children() as $cards) {
                $cardName = (string)$cards->Card['Name'];
                //Log::debug(sprintf("%s->%s|%s", __METHOD__, $cards->getName(), $cardName ));
                $customData[$cardName] = [];
                foreach($cards->Card->Fields->children() as $field) {
                    $key = (string)$field['Name'];
                    $val = (string)$field;
                    $customData[$cardName][$key] = $val;
                }
            }
        }

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
                    // prices
                    'originalPrice' => (float)$sku->OriginalPrice,
                    'retailPrice'   => (float)$sku->RetailPrice,
                    'price'         => (float)$sku->Price
                ];
            }
        }
        $product = [
            'id'       => $id,
            'code'     => (string)$product->Code,
            'name'     => (string)$product->Name,
            'range'    => (string)$product->SizeRange,
            // references
            'references'    => $references,
            'children'      => $children,
            'customData'    => $customData
        ];
        return $product;
    }

    /**
     * processCollection
     *
     * @return array
     */
    protected function processCollection($xml) {
        // loop SimpleXMLElements
        foreach($xml->children() as $product) {
            $id = $product->Id;
            $products["$id"] = $this->processEntity($product);
        }
        return $products;
    }
}