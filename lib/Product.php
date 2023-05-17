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
    const PAGE_LIMIT = 0;
    const DEFAULT_PAGE_ROWS = 500;

    protected $products = [];
    protected $productLimit = 0;

    protected $totalProducts = 0;
    protected $totalPages = 0;

    protected $resourceKey = 'Product';

    protected $childResource = array(
        'FuturePrice',
        'CustomDataTemplate'
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
            $this->totalProducts = (int)$att['TotalRows'];
            /*
            return [
                'totalRows' => $this->totalProducts,
                'totalPages' => ceil($this->totalProducts / (int)$att['PageRows']),
                'products' => $this->processCollection($xml)
            ];
            */
            return $this->processCollection($xml);
        }
        else {
            return $this->processEntity($xml);
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
            $this->productLimit = $urlParams['limit'];
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

        // implement versions
        if (array_key_exists("CustomData", $urlParams)) {
            $this->httpHeaders['Accept'] = sprintf("version_4.0");
        }
        if (preg_match("/freestock/i", $dataKey)) {
            $this->httpHeaders['Accept'] = sprintf("version_2.0");
        }
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
            if ($this->productLimit != 0 && $urlParams['pageRows'] > $this->productLimit) {
                $urlParams['pageRows'] = $this->productLimit;
            }

            $this->xml = $this->processResponse($response, $dataKey);
            // calculate the total number of pages
            $this->totalPages = ceil($this->totalProducts / $urlParams['pageRows']);

            Log::info(sprintf("%s->processNextPage", __METHOD__), [
                sprintf('page: %d/%d', $page, $this->totalPages),
                'startRow:' . $urlParams['startRow'],
                'pageRows:' . $urlParams['pageRows'],
                'total:' . $this->totalProducts,
                'limit:' . $this->productLimit
            ]);

            // check we arent already at our limit
            Log::debug(sprintf("%s->check limit %d >= %d", __METHOD__, count($this->xml), $this->productLimit), []);
            if ($this->productLimit != 0 && count($this->xml) >= $this->productLimit) {
                Log::debug(sprintf("%s->product limit %d reached!", __METHOD__, $this->productLimit), []);
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
                        'limit:' . $this->productLimit,
                        'count:' . count($this->xml),
                        sprintf("yes: %d", (count($this->xml) >= $this->productLimit))
                    ]);
                    $products = $this->processResponse($response, $dataKey);

                    if ($products && is_array($products)) {
                        $this->xml = array_merge($this->xml, $products);
                    }
                    if ($this->productLimit != 0 && count($this->xml) >= $this->productLimit) {
                        Log::debug(sprintf("%s->product limit %d reached!", __METHOD__, $this->productLimit), []);
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
     * processEntity
     *
     * @return array
     */
    protected function processEntity($product) {
        $productId = (int)$product->Id;
        Log::debug(sprintf("%s->%s|%s|%s", __METHOD__, $productId, $product->Code, $product->Name));

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
                $barcode = (string)$sku->Barcode;
                $children[$cCode][$barcode] = [
                    'sku_id'            => (int)$sku->Id,
                    'barcode'           => $barcode,
                    'product_id'        => $productId,
                    'colour_id'         => (string)$colour->Id,
                    'sequence_sku'      => (int)$sku->Sequence,
                    'sequence_colour'   => (int)$colour->Sequence,
                    'colour_desc'       => $cName,
                    'colour_code'       => $cCode,
                    'size_code'         => (string)$sku->SizeCode,
                    // prices
                    'price_org'         => (float)$sku->OriginalPrice,
                    'price_rrp'         => (float)$sku->RetailPrice,
                    'price'             => (float)$sku->Price,
                    'freestock'         => (int)$sku->FreeStock
                ];
            }
        }
        $product = [
            'id'            => $productId,
            'code'          => (string)$product->Code,
            'name'          => (string)$product->Name,
            'size_range'    => (string)$product->SizeRange,
            'update_time_stamp' => strtotime((string)$product->UpdateTimeStamp),
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