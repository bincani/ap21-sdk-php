<?php

namespace PHPAP21;

class RetailTransactionsTest extends TestResource
{
    /**
     * Test GET retail transactions with invalid person triggers error
     */
    public function testGetTransactionsError()
    {
        $this->expectException('PHPAP21\\Exception\\ApiException');
        // Invalid person ID should throw ApiException from the API
        static::$ap21->Person(999999)->RetailTransactions->get();
    }
}
