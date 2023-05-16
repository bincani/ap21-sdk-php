<?php
/**
 * class AllStyles
 *
 * Addtional query parameters
 *      None
 *
 */

namespace PHPAP21\Freestock;

use PHPAP21\Freestock as Freestock;
use PHPAP21\Log;

class AllStyles extends Freestock
{
    protected $resourceKey = 'AllStyles';

    /**
     * processResponse
     *
     * @param SimpleXML $xml
     * @param string $dataKey
     *
     * @return [] $allStyles
     */
    public function processResponse($xml, $dataKey = null) {
        //$dataKey = $this->resource;
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
            return parent::processCollection($xml);
        }
        else {
            return parent::processEntity($xml);
        }
    }
}