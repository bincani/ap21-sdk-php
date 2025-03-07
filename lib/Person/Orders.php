<?php
/**
 * class Orders
 */
namespace PHPAP21\Person;

use PHPAP21\CurlRequest;
use PHPAP21\HttpRequestXml;
use PHPAP21\Person as Person;
use PHPAP21\Log;

class Orders extends Person
{
    protected $orderId = null;
    protected $totalOrders = 0;
    protected $orders = [];

    protected $resourceKey = 'Order';

    public function __construct($orderId = null, $parentResourceUrl = '') {
        $this->orderId = $orderId;
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

        // limit
        if (array_key_exists('limit', $urlParams)) {
            $this->personLimit = $urlParams['limit'];
            unset($urlParams['limit']);
        }
        if (!$url) {
            $url  = $this->generateUrl($urlParams);
        }
        if (!$dataKey) {
            $dataKey = $this->orderId ? $this->resourceKey : $this->pluralizeKey();
        }
        $response = HttpRequestXml::get($url, $this->httpHeaders);
        $this->xml = $this->processResponse($response, $dataKey);
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
            // parse POST headers
            else if (in_array($this->getMethod(), ['POST'])) {
                /**
                 * HTTP status code 201 Created means that the request has been successfully processed
                 * Location Header: Specifies the URL of the newly created resource
                 */
                if (CurlRequest::$lastHttpCode === 201) {
                    foreach(CurlRequest::$lastHttpResponseHeaders as $header => $value) {
                        if ($header === 'location') {
                            preg_match("/Orders\/(\d+)/", $value, $matches);
                            $this->orderId = $matches[1] ?? null;
                        }
                    }
                }
                return $this->orderId;
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
            $att = $xml->attributes();
            $this->totalOrders = (int)$att['TotalRows'];
            //Log::debug(sprintf("%s->totalOrders: %d", __METHOD__, $this->totalOrders), []);
            if ($this->totalOrders) {
                return $this->processCollection($xml);
            }
            else {
                return [];
            }
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
    protected function processEntity($order) {
        $orderId = $order->Id;
        //Log::debug(__METHOD__, ["id:" . $orderId, "xml:" . print_r($order, true)]);
        // addresses
        $addresses = [];
        foreach($order->Addresses->children() as $address) {
            $type = strtolower((string)$address->getName());
            //Log::debug(__METHOD__, [$type]);
            $addresses[$type] = [
                'line1' => (string)$address->AddressLine1,
                'line2' => (string)$address->AddressLine2,
                'city'  => (string)$address->City,
                'state' => (string)$address->State,
                'postcode'  => (string)$address->Postcode,
                'country'   => (string)$address->Country
            ];
        }

        // contacts
        $contacts = [];
        $c1 = $this->processContacts($order->Contacts->children());
        $contacts = array_merge($contacts, $c1);
        $c2 = $this->processContacts($order->DeliveryContacts->children());
        $contacts = array_merge($contacts, $c2);

        // order items
        $orderItems = [];
        foreach($order->OrderDetails->children() as $lineItem) {
            $orderItemId = (int)$lineItem->Id;
            //echo sprintf("lineItem[%s]=%s\n", $orderItemId, print_r($lineItem, true));
            $orderItems[$orderItemId] = [
                'sequence'      => (int)$lineItem->Sequence,
                'productId'     => (int)$lineItem->ProductId,
                'colourId'      => (int)$lineItem->ColourId,
                'skuId'         => (int)$lineItem->SkuId,
                'productCode'   => (string)$lineItem->ProductCode,
                'productName'   => (string)$lineItem->ProductName,
                'colourCode'    => (int)$lineItem->ColourCode,
                'colourName'    => (string)$lineItem->ColourName,
                'sizeCode'      => (int)$lineItem->SizeCode,
                'barCode'       => (string)$lineItem->BarCode,
                'quantity'      => (int)$lineItem->Quantity,
                'status'        => (string)$lineItem->Status
            ];
        }

        // payments
        $payments = [];
        foreach($order->Payments->children() as $payment) {
            $paymentId = (int)$payment->Id;
            $payments[$paymentId] = [
                'sequence'      => (int)$payment->Sequence,
                'origin'        => (string)$payment->Origin,
                'cardType'      => (string)$payment->CardType,
                'stan'          => (string)$payment->Stan,
                'reference'     => (string)$payment->Reference,
                'amount'        => (float)$payment->Amount,
                'merchantId'    => (int)$payment->MerchantId
            ];
        }

        /**
         * PointsPartner (currently empty)
         */
        $order = [
            'id'            => (int)$order->Id,
            'number'        => (string)$order->OrderNumber,
            'personId'      => (int)$order->PersonId,
            'createdAt'     => (string)$order->OrderDateTime,
            'sourceId'      => (string)$order->PartnerOrderId, // shopify order id
            'itemsOrdered'  => (int)$order->TotalQuantity,
            'totalTax'      => (float)$order->TotalTax,
            'taxIncluded'   => (boolean)$order->PricesIncludeTax,
            'totlaDiscount' => (float)$order->TotalDiscount,
            'totalDue'      => (float)$order->TotalDue,
            'totalPayment'  => (float)$order->TotalPayment,
            'pickupStoreId' => (int)$order->PickupStoreId,
            'carrier'       => (string)$order->SelectedFreightOption->Name,
            'deliveryInstructions' => (string)$order->DeliveryInstructions,
            'addresses'     => $addresses,
            'contacts'      => $contacts,
            'orderItems'    => $orderItems,
            'payments'      => $payments,
        ];
        return $order;
    }

    /**
     * processCollection
     *
     * @return array
     */
    protected function processCollection($xml) {
        // loop SimpleXMLElements
        foreach($xml->children() as $order) {
            $orderId = $order->Id;
            $this->orders["$orderId"] = $this->processEntity($order);
        }
        return $this->orders;
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
     * getTotalOrders
     *
     * @return int
     */
    public function getTotalOrders():int {
        return $this->totalOrders;
    }

    /**
     * getOrderId
     *
     * @return int
     */
    public function getOrderId():int {
        return $this->orderId;
    }
}
