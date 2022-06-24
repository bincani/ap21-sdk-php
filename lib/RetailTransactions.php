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

        // check response
        if (!$response) {
            throw new ApiException($message = "no response", CurlRequest::$lastHttpCode);
        }
        // parse xml
        if (!$this->xml = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOERROR |  LIBXML_ERR_NONE)) {
            throw new \Exception("invalid xml!");
        }

        //Log::debug(__METHOD__, [$dataKey, $this->xml->getName(), $this->pluralizeKey() ]);

        // sanity check
        if (strcasecmp($dataKey, $this->xml->getName()) !== 0) {
            throw new Exception(sprintf("invalid response %s! expecting %s", $this->xml->getName(), $dataKey));
        }

        return [];
    }

}