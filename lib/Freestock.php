<?php
/**
 * class FreeStock
 *
 * Addtional query parameters
 *      None
 *
 */

namespace PHPAP21;

use PHPAP21\Exception\ApiException;

class Freestock extends HTTPXMLResource
{
    protected $styles = [];

    protected $styleCnt = 0;

    protected $resource = 'FreeStock';
    protected $resourceKey = 'Freestock/AllStyle';

    protected $childResource = array(
        'Style' => 'Clr',
        'Clr'   => 'Sku'
    );

    /**
     * processResponse
     *
     * @param SimpleXML $xml
     * @param string $dataKey
     *
     * @return [] $styles
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
            $this->styleCnt = $att['TotalRows'];
            // $att['PageStartRow'];
            // $att['PageRows'];
            Log::debug(sprintf("%s->styleCnt: %d", __METHOD__, $this->styleCnt), []);
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
    protected function processEntity($style) {
        $colours = [];
        $skus = [];
        $children = [];

        //echo $style->asXML();
        $styleFreestock = 0;
        $id = (int)$style['StyleIdx'];
        foreach($style->Clr as $colour) {
            //echo $colour->asXML();
            $colourFreestock = 0;
            $cCode = (string)$colour['ClrIdx'];
            if (!array_key_exists($cCode, $children)) {
                $children[$cCode] = [];
            }
            foreach($colour->children() as $sku) {
                //echo $sku->asXML();
                $skuFreestock = 0;
                $sCode = (string)$sku['SkuIdx'];
                if (!array_key_exists($sCode, $children[$cCode])) {
                    $children[$cCode][$sCode] = [];
                }
                foreach($sku->children() as $store) {
                    //echo $store->asXML();
                    $storeId = (string)$store['StoreId'];
                    $storeFreestock = (int)$store['FreeStock'];
                    //Log::debug(sprintf("%s", __METHOD__), [$cCode, $cCode, $sCode, $storeId, $storeFreestock]);
                    $styleFreestock += $storeFreestock;
                    $colourFreestock += $storeFreestock;
                    $skuFreestock += $storeFreestock;
                    $children[$cCode][$sCode][$storeId] = [
                        'store'         => $store['name'],
                        'freestock'     => $storeFreestock
                    ];
                }
                $skus[$sCode] = [
                    'name'      => (string)$sku['Name'],
                    'freestock' => $skuFreestock
                ];
            }
            $colours[$cCode] = [
                'name'      => (string)$colour['Name'],
                'freestock' => $colourFreestock
            ];
        }
        $style = [
            'id'        => $id,
            'name'      => (string)$style['Name'],
            'freestock' => $styleFreestock,
            'colours'   => $colours,
            'skus'      => $skus,
            'stores'    => $children
        ];
        //echo sprintf(sprintf("%s->%s", __METHOD__, print_r($style, true)));
        return $style;
    }

    /**
     * processCollection
     *
     * @return array
     */
    protected function processCollection($xml) {
        $styles = [];
        // loop SimpleXMLElements
        foreach($xml->children() as $style) {
            $id = $style['StyleIdx'];
            //Log::debug(sprintf("%s->%s|%s", __METHOD__, $id, $style['Name']));
            $styles["$id"] = $this->processEntity($style);
        }
        return $styles;
    }
}