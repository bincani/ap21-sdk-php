<?php

namespace PHPAP21;

class RetailTransactionsTest extends TestResource
{
    /**
     * Test that RetailTransactions child resource is resolvable
     *
     * Note: RetailTransactions.php lives in lib/ (PHPAP21\RetailTransactions)
     * but Person's childResource resolution looks for PHPAP21\Person\RetailTransactions.
     * This test verifies the current behavior.
     */
    public function testGetTransactionsError()
    {
        $this->expectException('PHPAP21\\Exception\\SdkException');
        static::$ap21->Person(999999)->RetailTransactions->get();
    }
}
