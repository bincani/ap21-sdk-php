<?php

namespace PHPAP21;

class OrderTest extends TestResource
{
    protected static $personId;
    protected static $skuId;
    protected static $skuPrice;
    protected static $uniqueEmail;

    /**
     * Create a test person and find a product SKU for the order
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Create a person for the order
        $uniqueId = uniqid();
        static::$uniqueEmail = sprintf('order.test.%s@example.com', $uniqueId);

        $personXml = sprintf('<Person>
  <Firstname>Order</Firstname>
  <Surname>Tester</Surname>
  <Addresses>
    <Billing>
      <AddressLine1>456 Order Street</AddressLine1>
      <City>Melbourne</City>
      <State>VIC</State>
      <Postcode>3000</Postcode>
      <Country>Australia</Country>
    </Billing>
  </Addresses>
  <Contacts>
    <Email>%s</Email>
  </Contacts>
</Person>', static::$uniqueEmail);

        try {
            self::$ap21->Person->post($personXml);
            if (CurlRequest::$lastHttpCode === 201) {
                $headers = CurlRequest::$lastHttpResponseHeaders;
                if (preg_match('/persons\/(\d+)/i', $headers['location'], $matches)) {
                    static::$personId = (int) $matches[1];
                }
            }
        } catch (\Exception $e) {
            // handled by markTestSkipped
        }

        // Get a product with a SKU
        try {
            $products = self::$ap21->Product->get(['limit' => 1]);
            if (!empty($products)) {
                $first = reset($products);
                foreach ($first['children'] as $colour) {
                    foreach ($colour as $sku) {
                        static::$skuId = $sku['sku_id'];
                        static::$skuPrice = $sku['price'] > 0 ? $sku['price'] : $sku['price_rrp'];
                        if (static::$skuPrice <= 0) {
                            static::$skuPrice = $sku['price_org'];
                        }
                        break 2;
                    }
                }
            }
        } catch (\Exception $e) {
            // handled by markTestSkipped
        }
    }

    /**
     * Test POST creates an order and returns the order ID
     *
     * @return int The new order ID
     */
    public function testPostOrder()
    {
        if (!static::$personId) {
            $this->markTestSkipped('No person ID available');
        }
        if (!static::$skuId) {
            $this->markTestSkipped('No SKU available');
        }

        $price = static::$skuPrice;
        $value = $price; // quantity 1

        $orderXml = sprintf('<Order>
  <PersonId>%d</PersonId>
  <Addresses>
    <Billing>
      <AddressLine1>456 Order Street</AddressLine1>
      <City>Melbourne</City>
      <State>VIC</State>
      <Postcode>3000</Postcode>
      <Country>Australia</Country>
    </Billing>
    <Delivery>
      <ContactName>Order Tester</ContactName>
      <AddressLine1>456 Order Street</AddressLine1>
      <City>Melbourne</City>
      <State>VIC</State>
      <Postcode>3000</Postcode>
      <Country>Australia</Country>
    </Delivery>
  </Addresses>
  <Contacts>
    <Email>%s</Email>
  </Contacts>
  <DeliveryContacts>
    <Email>%s</Email>
  </DeliveryContacts>
  <OrderDetails>
    <OrderDetail>
      <SkuId>%d</SkuId>
      <Quantity>1</Quantity>
      <Price>%.2f</Price>
      <Value>%.2f</Value>
    </OrderDetail>
  </OrderDetails>
  <Payments>
    <PaymentDetail>
      <Origin>CreditCard</Origin>
      <Amount>%.2f</Amount>
    </PaymentDetail>
  </Payments>
</Order>',
            static::$personId,
            static::$uniqueEmail,
            static::$uniqueEmail,
            static::$skuId,
            $price,
            $value,
            $value
        );

        $orderId = static::$ap21->Person(static::$personId)->Orders->post($orderXml);

        $this->assertEquals(201, CurlRequest::$lastHttpCode);
        $this->assertNotEmpty($orderId, 'Order ID not returned from POST');
        $this->assertGreaterThan(0, (int) $orderId);

        $orderId = (int) $orderId;

        $this->summary('Order::POST (201 Created)', [[
            'orderId'  => $orderId,
            'personId' => static::$personId,
            'skuId'    => static::$skuId,
            'price'    => $price,
        ]]);

        return $orderId;
    }

    /**
     * Test GET fetches the order created by testPostOrder
     *
     * @depends testPostOrder
     */
    public function testGetOrder(int $orderId)
    {
        if (!$orderId) {
            $this->markTestSkipped('No order ID available (POST failed)');
        }

        $order = static::$ap21->Person(static::$personId)->Orders($orderId)->get();

        $this->assertIsArray($order);
        $this->assertNotEmpty($order);
        $this->assertEquals($orderId, $order['id']);
        $this->assertEquals(static::$personId, $order['personId']);

        $this->summary(sprintf('Order::GET(%d)', $orderId), [$order]);
    }

    /**
     * Test GET order with invalid ID triggers ApiException
     */
    public function testGetOrderError()
    {
        if (!static::$personId) {
            $this->markTestSkipped('No person ID available');
        }

        $this->expectException('PHPAP21\\Exception\\ApiException');
        static::$ap21->Person(static::$personId)->Orders(999999)->get();
    }
}
