<?php

namespace PHPAP21;

class PersonTest extends TestResource
{
    /**
     * Test GET single person by ID from a product order
     *
     * Find a person ID from the products/orders data rather than
     * fetching the full persons collection (which can be very large).
     */
    public function testPostError()
    {
        $this->expectException('PHPAP21\\Exception\\ApiException');
        // Missing mandatory fields (Firstname, Surname, Email, Billing address)
        static::$ap21->Person->post(['Firstname' => '']);
    }
}
