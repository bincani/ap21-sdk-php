<?php

namespace PHPAP21;

class OrderTest extends TestResource
{
    /**
     * Test GET order with invalid ID triggers ApiException
     */
    public function testGetOrderError()
    {
        $this->expectException('PHPAP21\\Exception\\ApiException');
        // Invalid order ID should throw ApiException from the API
        static::$ap21->Order(999999)->get();
    }
}
