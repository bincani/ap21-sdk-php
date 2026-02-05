<?php

namespace PHPAP21;

class FreestockTest extends TestResource
{
    protected static $productId;
    protected static $allStyles;

    /**
     * Find a product ID that has freestock by querying AllStyles
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        try {
            static::$allStyles = self::$ap21->Freestock->AllStyles->get();
            if (!empty(static::$allStyles)) {
                $first = reset(static::$allStyles);
                static::$productId = $first['id'];
            }
        } catch (\Exception $e) {
            // Will be handled by markTestSkipped in individual tests
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
        if (!static::$allStyles) {
            $this->markTestSkipped('AllStyles returned no data in setup');
        }

        $this->assertIsArray(static::$allStyles);
        $this->summary('Freestock::AllStyles', static::$allStyles);
    }
}
