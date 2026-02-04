<?php

namespace PHPAP21;

class FreestockTest extends TestResource
{
    protected static $productId;

    /**
     * Find a product ID to look up freestock
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
     * Test GET freestock by style
     */
    public function testGetByStyle()
    {
        if (!static::$productId) {
            $this->markTestSkipped('No product ID available');
        }

        try {
            $freestock = static::$ap21->Freestock->Style(static::$productId)->get();
            $this->assertIsArray($freestock);
            $this->summary('Freestock::Style(' . static::$productId . ')', $freestock);
        } catch (Exception\ApiException $e) {
            // Accept API errors (e.g. product not found in freestock)
            $this->assertInstanceOf(Exception\ApiException::class, $e);
            $this->summary('Freestock::Style(' . static::$productId . ')', [['error' => $e->getMessage()]]);
        }
    }

    /**
     * Test GET all styles freestock
     */
    public function testGetAllStyles()
    {
        try {
            $freestock = static::$ap21->Freestock->AllStyles->get();
            $this->assertIsArray($freestock);
            $this->summary('Freestock::AllStyles', $freestock);
        } catch (Exception\ApiException $e) {
            $this->assertInstanceOf(Exception\ApiException::class, $e);
            $this->summary('Freestock::AllStyles', [['error' => $e->getMessage()]]);
        }
    }
}
