<?php
/**
 * class RetailTransactions
 */

namespace PHPAP21;

use PHPAP21\Exception\ApiException;

class RetailTransactions extends HTTPXMLResource
{
    protected $resourceKey = 'retailtransactions';

    /**
     * processResponse
     *
     * @param [type] $responseArray
     * @param [type] $dataKey
     * @return [] $people
     */
    public function processResponse($response, $dataKey = null) {

        // Convert SimpleXML to DOMDocument
        $this->dom = new \DOMDocument;
        $this->dom->loadXML($xml->asXML());

        //Log::debug(__METHOD__, [get_class($this->dom)]);
        //Log::debug(__METHOD__, [$dataKey, $this->xml->getName(), $this->pluralizeKey() ]);

        // sanity check
        if (strcasecmp($dataKey, $this->xml->getName()) !== 0) {
            throw new Exception(sprintf("invalid response %s! expecting %s", $this->xml->getName(), $dataKey));
        }

        return [];
    }

}