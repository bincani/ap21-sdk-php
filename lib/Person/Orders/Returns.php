<?php
/**
 * class Returns
 *
 */

namespace PHPAP21\Person\Orders;

use PHPAP21\Person\Orders as Orders;
use PHPAP21\Log;

class Returns extends Orders
{
    protected $totalReturns = 0;

    protected $resourceKey = 'Return';

    /**
     * processResponse
     *
     * @param SimpleXML $xml
     * @param string $dataKey
     *
     * @return [] $style
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

        Log::debug(__METHOD__, [$xml->asXML()]);

        // process collection
        if (strcasecmp($dataKey, $xml->getName()) === 0) {
            $att = $xml->attributes();
            $this->totalReturns = (int)$att['TotalRows'];
            Log::debug(sprintf("%s->totalReturns: %d", __METHOD__, $this->totalReturns), []);
            if ($this->totalReturns) {
                return parent::processCollection($xml);
            }
            else {
                return [];
            }
        }
        else {
            return parent::processEntity($xml);
        }
    }

}
