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
        //Log::debug(__METHOD__, [$style->asXML()]);
        $styleFreestock = 0;
        $id = (int)$style['StyleIdx'];
        foreach($style->Clr as $colour) {
            //Log::debug(__METHOD__, [$colour->asXML()]);
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
        //Log::debug(__METHOD__, $style);
        return $style;
    }

    /**
     * processClr
     *
     * @param \SimpleXMLElement $colour
     * @return array
     */
    protected function processClr($colour)
    {
        $skus           = [];
        $stores         = []; // for the "no SKU" case
        $colourFreestock = 0;
        // Case 2: <Clr> has <Sku> children
        if ($colour->Sku->count()) {
            foreach ($colour->Sku as $sku) {
                $sCode = (string) $sku['SkuIdx'];
                $skus[$sCode] = $this->processSku($sku);

                if (isset($skus[$sCode]['freestock'])) {
                    $colourFreestock += (int) $skus[$sCode]['freestock'];
                }
            }
        }
        // Case 1: <Clr> has <Store> children directly (no <Sku>)
        elseif ($colour->Store->count()) {
            foreach ($colour->Store as $store) {
                $storeId   = (string) $store['StoreId'];
                $storeData = $this->processStore($store);

                $colourFreestock += $storeData['freestock'];
                $stores[$storeId] = $storeData;
            }
        }
        return [
            'name'      => (string) $colour['Name'],
            'freestock' => $colourFreestock,
            'skus'      => $skus,
            'stores'    => $stores, // populated for case 1; empty for case 2
        ];
    }

    /**
     * processSku
     *
     * @param \SimpleXMLElement $sku
     * @return array
     */
    protected function processSku($sku)
    {
        $stores = [];
        $skuFreestock = 0;
        // Only iterate over <Store> children
        foreach ($sku->Store as $store) {
            $storeId   = (string) $store['StoreId'];
            $storeData = $this->processStore($store);
            $skuFreestock += $storeData['freestock'];
            $stores[$storeId] = $storeData;
        }
        return [
            'name'      => (string) $sku['Name'],
            'freestock' => $skuFreestock,
            'stores'    => $stores,
        ];
    }

    /**
     * processStore
     *
     * @param \SimpleXMLElement $store
     * @return array
     */
    protected function processStore($store)
    {
        return [
            'store'     => (string) $store['Name'],
            'freestock' => (int) $store['FreeStock'],
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