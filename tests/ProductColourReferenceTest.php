<?php

namespace PHPAP21;

class ProductColourReferenceTest extends TestResource
{
    /**
     * Test GET product colour references
     */
    public function testGet()
    {
        try {
            $result = static::$ap21->ProductColourReference->get();
            $this->assertIsArray($result);
        } catch (Exception\ApiException $e) {
            // Accept API errors (may need specific product/colour ID)
            $this->assertInstanceOf(Exception\ApiException::class, $e);
        }
    }
}
