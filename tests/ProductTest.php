<?php
/**
 * class ProductTest
 */

namespace PHPAP21;

class ProductTest extends TestSimpleResource
{
    /**
     * @inheritDoc
     */
    public $postArray = [];

    /**
     * posting invalid data to trigger an error
     */
    public $errorPostArray = ["description" => "blah"];

    /**
     * data for testing put
     */
    public $putArray = array("Name" => "Test Product Update");

    /**
     * Test GET single product by ID and verify style code is returned
     */
    public function testGetById()
    {
        $products = static::$ap21->Product->get(['limit' => 1]);
        $this->assertNotEmpty($products, 'No products available');

        $first = reset($products);
        $id = $first['id'];
        $expectedCode = $first['code'];

        $product = static::$ap21->Product($id)->get();
        $this->assertIsArray($product);
        $this->assertNotEmpty($product);
        $this->assertEquals($id, $product['id']);
        $this->assertEquals($expectedCode, $product['code']);
        $this->assertArrayHasKey('name', $product);
        $this->assertArrayHasKey('children', $product);
        $this->summary(sprintf('Product::GET(%d) code=%s', $id, $expectedCode), [$product]);
    }

    /**
     * Test put resource using an existing product ID
     *
     * @depends testGet
     */
    public function testPut($id = null)
    {
        if (!$this->putArray) {
            $this->markTestSkipped('No put data defined for ' . $this->resourceName);
        }

        // Fetch first available product ID since Product has no POST
        $products = static::$ap21->Product->get(['limit' => 1]);
        $this->assertNotEmpty($products, 'No products available to test PUT');
        $first = reset($products);
        $id = $first['id'];

        $this->expectException('PHPAP21\\Exception\\ApiException');
        static::$ap21->{$this->resourceName}($id)->put($this->putArray);
    }
}
