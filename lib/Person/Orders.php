<?php
/**
 * class Orders
 *
 */

namespace PHPAP21\Person;

use PHPAP21\Person as Person;
use PHPAP21\Log;

class Orders extends Person
{
    protected $totalOrders = 0;

    protected $resourceKey = 'Order';

    /**
     * processResponse
     *
     * @param SimpleXML $xml
     * @param string $dataKey
     *
     * @return [] $style
     */
    public function processResponse($xml, $dataKey = null) {

        if (empty($xml)) {
            if (in_array($this->getMethod(), ['GET'])) {
                $message = sprintf("%s->no response for method %s", __METHOD__, $this->getMethod());
                throw new ApiException($message, CurlRequest::$lastHttpCode);
            }
            else {
                return '';
            }
        }

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
            $this->totalOrders = (int)$att['TotalRows'];
            Log::debug(sprintf("%s->totalOrders: %d", __METHOD__, $this->totalOrders), []);
            if ($this->totalOrders) {
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
