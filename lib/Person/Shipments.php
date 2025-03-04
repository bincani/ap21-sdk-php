<?php
/**
 * class Shipments
 */
namespace PHPAP21\Person;

use PHPAP21\HttpRequestXml;
use PHPAP21\Person as Person;
use PHPAP21\Log;

class Shipments extends Person
{
    protected $shipmentId = null;
    protected $totalShipments = 0;
    protected $shipments = [];

    protected $resourceKey = 'Shipment';

    public function __construct($shipmentId = null, $parentResourceUrl = '') {
        $this->orderId = $shipmentId;
        parent::__construct(...func_get_args());
    }

    /**
     * Generate a HTTP GET request and return result as an array
     *
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    public function get($urlParams = array(), $url = null, $dataKey = null)
    {
        // implement versions
        if ($urlParams) {
            $lowerUrlParams = array_change_key_case($urlParams, CASE_LOWER);
            if (array_key_exists("updatedafter", $lowerUrlParams)) {
                $this->httpHeaders['Accept'] = sprintf("version_2.0");
            }
        }
        if (!$url) {
            $url  = $this->generateUrl($urlParams);
        }
        $response = HttpRequestXml::get($url, $this->httpHeaders);
        $this->xml = $this->processResponse($response, $this->pluralizeKey());
        //Log::debug(__METHOD__, [sprintf("xml: %s", print_r($this->xml, true))]);
        return $this->xml;
    }

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

        // sanity check
        if (strcasecmp($dataKey, $xml->getName()) !== 0) {
            throw new \Exception(
                sprintf("invalid response %s! expecting %s", $xml->getName(), $dataKey)
            );
        }

        // process collection
        //Log::debug(__METHOD__, [sprintf("strcasecmp(%s, %s)=%d", $this->pluralizeKey(), $xml->getName(), strcasecmp($this->pluralizeKey(), $xml->getName()))]);
        if (strcasecmp($this->pluralizeKey(), $xml->getName()) === 0) {
            //$att = $xml->attributes();
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
    protected function processEntity($shipment) {
        //Log::debug(__METHOD__, ["xml:" . print_r($shipment, true)]);
        // contents
        $contents = [];
        foreach($shipment->Contents->children() as $content) {
            $contents[] = [
                'productCode'  => (string)$content->ProductCode,
                'colourCode'    => (string)$content->ColourCode,
                'sizeCode'      => (string)$content->SizeCode,
                'skuId'         => (string)$content->SkuId,
                'quantity'      => (string)$content->Quantity
            ];
        }
        // packed carton contents
        $packedCartonContents = [];
        foreach($shipment->PackedCartonContents->children() as $packedCartonContent) {
            $packedCartonContents[] = [
                'trackingNumber'    => (string)$packedCartonContent->TrackingNumber,
                'cartonSSCC'        => (string)$packedCartonContent->CartonSSCC,
                'productCode'       => (string)$packedCartonContent->ProductCode,
                'colourCode'        => (string)$packedCartonContent->colourCode,
                'sizeCode'          => (string)$packedCartonContent->SizeCode,
                'skuId'             => (string)$packedCartonContent->SkuId,
                'quantity'          => (string)$packedCartonContent->Quantity
            ];
        }
        // shipment
        $shipment = [
            'carrierName'           => (string)$shipment->CarrierName,
            'carrierUrl'            => (string)$shipment->CarrierUrl,
            'conNote'               => (string)$shipment->ConNote,
            'despatchDate'          => (string)$shipment->DespatchDate,
            'contents'              => $contents,
            'packedCartonContents'  => $packedCartonContents
        ];
        return $shipment;
    }

    /**
     * processCollection
     *
     * @return array
     */
    protected function processCollection($xml) {
        // loop SimpleXMLElements
        foreach($xml->children() as $shipment) {
            $shipmentId = $shipment->Id;
            $this->shipments["$shipmentId"] = $this->processEntity($shipment);
        }
        return $this->shipments;
    }

    /**
     * processContacts
     *
     * @param SimpleXMLElement $xmlContacts
     *
     * @return array
     */
    private function processContacts($xmlContacts):array {
        $contacts = [];
        foreach($xmlContacts as $contact) {
            $type = (string)$contact->getName();
            //Log::debug(__METHOD__, [$type, get_class($contact)]);
            if (preg_match("/phones/i", $type)) {
                foreach($contact->children() as $phone) {
                    $type = (string)$phone->getName();
                    $contacts[$type] = (string)$phone;
                }
            }
            else {
                $contacts[$type] = (string)$contact;
            }
        }
        return $contacts;
    }

    /**
     * getTotalShipments
     *
     * @return int
     */
    public function getTotalShipments():int {
        return $this->totalShipments;
    }
}
