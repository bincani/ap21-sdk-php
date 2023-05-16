<?php
/**
 * class Style
 *
 */

namespace PHPAP21\Freestock;

use PHPAP21\Freestock as Freestock;
use PHPAP21\Log;

class Sku extends Freestock
{
    protected $resourceKey = 'Sku';

    /**
     * processResponse
     *
     * @param SimpleXML $xml
     * @param string $dataKey
     *
     * @return [] $sku
     */
    public function processResponse($xml, $dataKey = null) {
        //$dataKey = $this->resource;
        Log::debug(__METHOD__, ["dataKey:" . $dataKey, "xml->getName:" . $xml->getName()]);
        // sanity check
        if (strcasecmp($dataKey, $xml->getName()) !== 0) {
            throw new \Exception(
                sprintf("invalid response %s! expecting %s", $xml->getName(), $dataKey)
            );
        }
        return parent::processSku($xml);
    }

}
