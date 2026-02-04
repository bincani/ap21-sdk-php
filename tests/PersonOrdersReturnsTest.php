<?php

namespace PHPAP21;

class PersonOrdersReturnsTest extends TestResource
{
    /**
     * Test GET returns with invalid person/order triggers ApiException
     */
    public function testGetReturnsError()
    {
        $this->expectException('PHPAP21\\Exception\\ApiException');
        // Invalid person/order combination should throw ApiException from the API
        static::$ap21->Person(999999)->Orders(999999)->Returns->get();
    }
}
