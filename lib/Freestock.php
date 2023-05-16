<?php
/**
 * class FreeStock
 *
 * Addtional query parameters
 *      None
 *
 */

namespace PHPAP21;

class Freestock extends HTTPXMLResource
{
    protected $styles = [];

    protected $styleCnt = 0;

    protected $resourceKey = 'Freestock';

    protected $childResource = array(
        'AllStyles',
        'Style',
        'Clr',
        'Sku'
    );

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
     * @return [] $styles
     */
    public function processResponse($xml, $dataKey = null) {
        //$dataKey = $this->resource;
        Log::debug(__METHOD__, [$dataKey, $xml->getName()]);
        // sanity check
        if (strcasecmp($dataKey, $xml->getName()) !== 0) {
            throw new \Exception(sprintf("invalid response %s! expecting %s", $xml->getName(), $dataKey));
        }
        // no nothing
        return ["please select a resource", $childResource];
    }

    /**
     * processEntity
     *
     * @return array
     */
    protected function processEntity($style) {
        $colours = [];
        $skus = [];

        //echo $style->asXML();
        $styleFreestock = 0;
        $id = (int)$style['StyleIdx'];
        foreach($style->Clr as $colour) {
            $cCode = (string)$colour['ClrIdx'];
            $colours[$cCode] = $this->processClr($colour);
            $styleFreestock += $colours[$cCode]['freestock'];
        }
        $style = [
            'id'        => $id,
            'name'      => (string)$style['Name'],
            'freestock' => $styleFreestock,
            'colours'   => $colours,
        ];
        // flatten skus
        /*
        $skus = [];
        foreach($colours as $colour) {
            $skus = array_merge($skus, $colour['skus']);
        }
        $style = [
            'id'        => $id,
            'name'      => (string)$style['Name'],
            'freestock' => $styleFreestock,
            'skus'      => $skus
        ];
        */

        //echo sprintf(sprintf("%s->%s", __METHOD__, print_r($style, true)));
        return $style;
    }

    /**
     * processClr
     *
     * @return array
     */
    protected function processClr($colour) {
        //echo $colour->asXML();
        $skus = [];
        foreach($colour->children() as $sku) {
            $sCode = (string)$sku['SkuIdx'];
            $skus[$sCode] = $this->processSku($sku);
            $colourFreestock += $skus[$sCode]['freestock'];
        }
        return [
            'name'      => (string)$colour['Name'],
            'freestock' => $colourFreestock,
            'skus'      => $skus
        ];
    }

    /**
     * processSku
     *
     * @return array
     */
    protected function processSku($sku) {
        //echo $sku->asXML();
        $stores = [];
        $skuFreestock = 0;
        foreach($sku->children() as $store) {
            //echo $store->asXML();
            $storeId = (string)$store['StoreId'];
            $storeFreestock = (int)$store['FreeStock'];
            //Log::debug(sprintf("%s", __METHOD__), [$cCode, $cCode, $sCode, $storeId, $storeFreestock]);
            $skuFreestock += $storeFreestock;
            $stores[$storeId] = [
                'store'     => (string)$store['Name'],
                'freestock' => $storeFreestock
            ];
        }
        return [
            'name'      => (string)$sku['Name'],
            'freestock' => $skuFreestock,
            'stores'    => $stores
        ];
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

    public function pluralizeKey()
    {
        // no pluralize
        return $this->resourceKey;
    }
}