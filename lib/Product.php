<?php
declare(strict_types=1);

/**
 * class Product
 *
 * Clrs
 * The list of colours for this product
 *
 * Additional query parameters
 *  CustomData = true
 *      List of Cards of Custom Data on product level
 *
 */

namespace PHPAP21;

use PHPAP21\Exception\ApiException;
use SimpleXMLElement;

class Product extends HTTPXMLResource
{
    public const PAGE_LIMIT = 0;
    public const DEFAULT_PAGE_ROWS = 500;

    /** @var array<string,mixed> */
    protected array $products = [];
    protected int $productLimit = 0;

    protected int $totalProducts = 0;
    protected int $totalPages = 0;
    protected int $currentPage = 1;
    protected int $currentVirtualPage = 1; // virtual page is used when paging is done externally
    protected int $startRow = 1;

    // @TODO protected string $resourceKey = 'Product';
    protected $resourceKey = 'Product';

    /** @var string[] */
    // @TODO protected array $childResource = [
    protected $childResource = array(
        'FuturePrice',
        'CustomDataTemplate'
    );

    /** @var array<string,string> */
    // @TODO protected array $customGetActions = [
    protected $customGetActions = array (
        'product_ids' => 'productIds',
    );

    /**
     * processResponse
     *
     * @param SimpleXMLElement|string $xml
     * @param string|null $dataKey
     * @return array
     */
    public function processResponse($xml, $dataKey = null)
    // @TODO public function processResponse(SimpleXMLElement|string $xml, ?string $dataKey = null): array
    {
        // Allow callers to pass raw XML string or SimpleXMLElement
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml);
            if (!$xml instanceof SimpleXMLElement) {
                throw new \RuntimeException('Failed to parse XML response.');
            }
        }

        // sanity check
        if (strcasecmp((string) $dataKey, $xml->getName()) !== 0) {
            throw new ApiException(sprintf("invalid response %s! expecting %s", $xml->getName(), $dataKey));
        }

        // process collection
        if (strcasecmp($this->pluralizeKey(), $xml->getName()) === 0) {
            $att = $xml->attributes();
            $this->totalProducts = (int) $att['TotalRows'];

            return $this->processCollection($xml);
        }

        return $this->processEntity($xml);
    }

    /**
     * Generate a HTTP GET request and return results as an array
     *
     * @param array $urlParams Check Ap21 API reference of the specific resource for the list of URL parameters
     * @param string|null $url
     * @param string|null $dataKey Keyname to fetch data from response array
     *
     * @uses HttpRequestXml::get() to send the HTTP request
     *
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    // @TODO public function get(array $urlParams = [], ?string $url = null, ?string $dataKey = null): array
    public function get($urlParams = array(), $url = null, $dataKey = null)
    {
        // limit
        if (array_key_exists('limit', $urlParams)) {
            $this->productLimit = (int) $urlParams['limit'];
            unset($urlParams['limit']);
        }

        if (!$url) {
            $url  = $this->generateUrl($urlParams);
        }
        Log::debug(sprintf("%s->url: %s", __METHOD__, $url));
        if (!$dataKey) {
            $dataKey = $this->id ? $this->resourceKey : $this->pluralizeKey();
        }
        Log::debug(sprintf("%s->dataKey: %s", __METHOD__, $dataKey), [$this->id]);

        // implement versions
        if (array_key_exists("CustomData", $urlParams)) {
            $this->httpHeaders['Accept'] = 'version_4.0';
        }
        if (preg_match("/freestock/i", (string) $dataKey)) {
            $this->httpHeaders['Accept'] = 'version_2.0';
        }
        Log::debug(sprintf("%s->httpHeaders", __METHOD__), $this->httpHeaders);

        $response = HttpRequestXml::get($url, $this->httpHeaders);
        Log::debug(sprintf("%s->response.length: %d", __METHOD__, is_string($response) ? strlen($response) : 0), []);

        // implement paging
        if (array_key_exists('startRow', $urlParams)) {
            // set up paging
            $this->startRow = (int) $urlParams['startRow'];
            $urlParams['pageRows'] = array_key_exists('pageRows', $urlParams) ? (int) $urlParams['pageRows'] : self::DEFAULT_PAGE_ROWS;
            // set to limit if greater than limit
            if ($this->productLimit !== 0 && $urlParams['pageRows'] > $this->productLimit) {
                $urlParams['pageRows'] = $this->productLimit;
            }

            $this->xml = $this->processResponse($response, $dataKey);
            // calculate the total number of pages
            $this->totalPages = (int) ceil($this->totalProducts / $urlParams['pageRows']);

            Log::info(sprintf("%s->processNextPage1", __METHOD__), [
                sprintf('page: %d/%d', $this->currentVirtualPage, $this->totalPages),
                'startRow:' . $this->startRow,
                'pageRows:' . $urlParams['pageRows'],
                'total:' . $this->totalProducts,
                'limit:' . $this->productLimit
            ]);
            $this->currentVirtualPage++;

            // check we arent already at our limit
            Log::debug(sprintf("%s->check limit %d >= %d", __METHOD__, is_countable($this->xml) ? count($this->xml) : 0, $this->productLimit), []);
            // check we have reached the end
            Log::debug(sprintf("%s->check end %d >= %d", __METHOD__, ($this->startRow + $urlParams['pageRows']), $this->totalProducts), []);
            if (
                $this->productLimit !== 0
                &&
                is_countable($this->xml) && count($this->xml) >= $this->productLimit
            ) {
                Log::info(sprintf("%s->product limit %d reached!", __METHOD__, $this->productLimit), []);
            } elseif (
                ($this->startRow + $urlParams['pageRows']) >= $this->totalProducts
            ) {
                Log::info(sprintf("%s->product end reached %d >= %d", __METHOD__, ($this->startRow + $urlParams['pageRows']), $this->totalProducts), []);
            } else {
                do {
                    // set startRow to the next amount
                    $urlParams['startRow'] = (int) (($urlParams['pageRows'] * $this->currentPage) + $this->startRow);
                    $url = $this->generateUrl($urlParams);

                    $response = HttpRequestXml::get($url, $this->httpHeaders);
                    Log::info(sprintf("%s->response.length: %d", __METHOD__, is_string($response) ? strlen($response) : 0), []);

                    if (empty($response) || (is_string($response) && strlen($response) === 0)) {
                        Log::debug(sprintf("%s->end reached!", __METHOD__), []);
                        break;
                    }
                    if (self::PAGE_LIMIT !== 0 && $this->currentPage < self::PAGE_LIMIT) {
                        Log::debug(sprintf("%s->page limit %d reached!", __METHOD__, self::PAGE_LIMIT), []);
                        break;
                    }
                    $this->currentPage++;
                    Log::info(sprintf("%s->processNextPage2", __METHOD__), [
                        sprintf('page: %d/%d', $this->currentPage, $this->totalPages),
                        'startRow:' . $urlParams['startRow'],
                        'pageRows:' . $urlParams['pageRows'],
                        'total:' . $this->totalProducts,
                        'limit:' . $this->productLimit,
                        'count:' . (is_countable($this->xml) ? count($this->xml) : 0),
                        sprintf("yes: %d", (is_countable($this->xml) && $this->productLimit !== 0 && count($this->xml) >= $this->productLimit) ? 1 : 0)
                    ]);
                    $products = $this->processResponse($response, $dataKey);

                    if ($products && is_array($products)) {
                        /** @var array $this->xml */
                        $this->xml = array_merge($this->xml, $products);
                    }
                    if ($this->productLimit !== 0 && is_countable($this->xml) && count($this->xml) >= $this->productLimit) {
                        Log::debug(sprintf("%s->product limit %d reached!", __METHOD__, $this->productLimit), []);
                        break;
                    }
                } while ($response);
            }
        } else {
            Log::debug(sprintf("%s->%s->processResponse", __METHOD__, get_class($this)), [is_object($response) ? get_class($response) : gettype($response)]);
            $this->xml = $this->processResponse($response, $dataKey);
        }
        /** @var array $this->xml */
        return $this->xml;
    }

    /**
     * processEntity
     *
     * @param SimpleXMLElement $product
     * @return array
     */
    protected function processEntity(SimpleXMLElement $product): array
    {
        $productId = (int) $product->Id;
        Log::debug(sprintf("%s->%s|%s|%s", __METHOD__, $productId, (string) $product->Code, (string) $product->Name));

        $references = [];
        if (isset($product->References)) {
            foreach ($product->References->children() as $reference) {
                $rTypeId = (int) $reference->ReferenceTypeId;
                $rIdRaw  = trim((string) $reference->Id);
                if ($rIdRaw === '') {
                    // skip empty <Id/> entries
                    continue;
                }
                $references[$rTypeId] = [
                    'type_id' => $rTypeId,
                    'id'      => (int) $rIdRaw,
                ];
            }
        }

        // process parent custom data
        $pCustomData = $this->processCustomData($product->CustomData ?? null);

        $children = [];
        if (isset($product->Clrs)) {
            foreach ($product->Clrs->children() as $colour) {
                $cCode = (string) $colour->Code;
                $cName = (string) $colour->Name;
                $cCustomData = $this->processCustomData($colour->CustomData ?? null);

                foreach ($colour->SKUs->children() as $sku) {
                    if (!isset($children[$cCode])) {
                        $children[$cCode] = [];
                    }
                    $barcode = (string) $sku->Barcode;
                    $children[$cCode][$barcode] = [
                        'sku_id'          => (int) $sku->Id,
                        'barcode'         => $barcode,
                        'product_id'      => $productId,
                        'colour_id'       => (string) $colour->Id,
                        'sequence_sku'    => (int) $sku->Sequence,
                        'sequence_colour' => (int) $colour->Sequence,
                        'colour_desc'     => $cName,
                        'colour_code'     => $cCode,
                        'size_code'       => (string) $sku->SizeCode,
                        // prices
                        'price_org'       => (float) $sku->OriginalPrice,
                        'price_rrp'       => (float) $sku->RetailPrice,
                        'price'           => (float) $sku->Price,
                        'freestock'       => (int) $sku->FreeStock
                    ];
                    if ($cCustomData) {
                        $children[$cCode][$barcode]['customData'] = $cCustomData;
                    }
                }
            }
        }

        return [
            'id'                => $productId,
            'code'              => (string) $product->Code,
            'name'              => (string) $product->Name,
            'size_range'        => (string) $product->SizeRange,
            'update_time_stamp' => strtotime((string) $product->UpdateTimeStamp),
            // references
            'references'        => $references,
            'children'          => $children,
            'customData'        => $pCustomData
        ];
    }

    /**
     * processCollection
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    protected function processCollection(SimpleXMLElement $xml): array
    {
        $this->products = [];
        // loop SimpleXMLElements
        foreach ($xml->children() as $product) {
            $id = (string) $product->Id;
            $this->products[$id] = $this->processEntity($product);
        }
        return $this->products;
    }

    /**
     * Parse all <CustomData> cards into:
     * [
     *   'Card Name' => [
     *       'Field A' => 'text or [values]',
     *       'Field B' => [...],
     *   ],
     *   ...
     * ]
     *
     * @param SimpleXMLElement|null $customData
     * @return array
     */
    protected function processCustomData(?SimpleXMLElement $customData): array
    {
        $out = [];
        if (!$customData || !isset($customData->Cards)) {
            return $out;
        }

        foreach ($customData->Cards->Card as $card) {
            $cardName = trim((string) ($card['Name'] ?? ''));
            if ($cardName === '' || !isset($card->Fields)) {
                continue;
            }
            $fields = [];
            foreach ($card->Fields->Field as $field) {
                $fname = trim((string) ($field['Name'] ?? ''));
                if ($fname === '') {
                    continue;
                }
                // Prefer <ListValues><Value>...</Value></ListValues> if present
                $values = [];
                if (isset($field->ListValues)) {
                    foreach ($field->ListValues->Value as $v) {
                        $val = trim((string) $v);
                        if ($val !== '') {
                            $values[] = $val;
                        }
                    }
                }
                $text = trim((string) $field);
                if (!empty($values)) {
                    $fields[$fname] = $values;
                } elseif ($text !== '') {
                    $fields[$fname] = $text;
                } else {
                    // Empty field; skip
                    continue;
                }
            }
            if (!empty($fields)) {
                // Merge multiple same-name cards if they exist (later ones override per field)
                $out[$cardName] = array_key_exists($cardName, $out)
                    ? array_replace($out[$cardName], $fields)
                    : $fields;
            }
        }
        return $out;
    }

    /**
     * getTotalPages
     *
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * getTotalProducts
     *
     * @return int
     */
    public function getTotalProducts(): int
    {
        return $this->totalProducts;
    }
}
