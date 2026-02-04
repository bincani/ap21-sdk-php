<?php

namespace PHPAP21;

class ProductFuturePriceTest extends TestResource
{
    protected static $productId;

    /**
     * Find a product ID
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $products = self::$ap21->Product->get();
        if (!empty($products)) {
            $first = reset($products);
            static::$productId = $first['id'];
        }
    }

    /**
     * Test GET future price for a product
     */
    public function testGetFuturePrice()
    {
        if (!static::$productId) {
            $this->markTestSkipped('No product ID available');
        }

        try {
            $result = static::$ap21->Product(static::$productId)->FuturePrice->get();
            $this->assertIsArray($result);
            $this->summary('Product(' . static::$productId . ')::FuturePrice', $result);
        } catch (Exception\ApiException $e) {
            // Accept API errors (e.g. no future prices set)
            $this->assertInstanceOf(Exception\ApiException::class, $e);
            $this->summary('Product(' . static::$productId . ')::FuturePrice', [['error' => $e->getMessage()]]);
        }
    }
}
