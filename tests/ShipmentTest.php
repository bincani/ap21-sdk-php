<?php

namespace PHPAP21;

class ShipmentTest extends TestResource
{
    /**
     * Test GET shipment with invalid ID triggers ApiException
     */
    public function testGetShipmentError()
    {
        $this->expectException('PHPAP21\\Exception\\ApiException');
        // Invalid shipment ID should throw ApiException from the API
        static::$ap21->Shipment(999999)->get();
    }
}
