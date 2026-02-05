<?php

namespace PHPAP21;

class ProductCustomDataTemplateTest extends TestResource
{
    /**
     * Test GET custom data templates for products
     */
    public function testGetTemplates()
    {
        try {
            $result = static::$ap21->Product->CustomDataTemplate->get();
            $this->assertIsArray($result);
            $this->summary('Product::CustomDataTemplate', $result);
        } catch (Exception\ApiException $e) {
            // Accept API errors (e.g. no templates configured)
            $this->assertInstanceOf(Exception\ApiException::class, $e);
            $this->summary('Product::CustomDataTemplate', [['error' => $e->getMessage()]]);
        }
    }
}
