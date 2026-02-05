<?php

namespace PHPAP21;

class PersonShipmentsTest extends TestResource
{
    /**
     * Test GET shipments with invalid person/order triggers error
     */
    public function testGetShipmentsError()
    {
        $this->expectException('PHPAP21\\Exception\\ApiException');
        // Invalid person/order combination should throw
        static::$ap21->Person(999999)->Shipments(999999)->get();
    }
}
