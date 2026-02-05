<?php

namespace PHPAP21;

class PersonOrdersTest extends TestResource
{
    /**
     * Test POST order with invalid data triggers ApiException
     *
     * Uses a dummy person ID since fetching the full persons
     * collection is too slow for testing.
     */
    public function testPostOrderError()
    {
        $this->expectException('PHPAP21\\Exception\\ApiException');
        static::$ap21->Person(1)->Orders->post(['invalid' => 'data']);
    }
}
