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
    protected $orders = [];

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

        //Log::debug(__METHOD__, [$xml->asXML()]);

        // process collection
        if (strcasecmp($dataKey, $xml->getName()) === 0) {
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
                'quantity'      => (int)$lineItem->Quantity
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
            'number'        => (string)$order->OrderNumber,
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
}
