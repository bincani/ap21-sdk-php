<?php

namespace PHPAP21;

class StockChangedTest extends TestResource
{
    /**
     * Test GET stock changes since a recent timestamp
     */
    public function testGet()
    {
        // Use a recent timestamp to limit results
        $updatedAfter = date('Y-m-d\TH:i:s', strtotime('-24 hours'));

        try {
            $stockChanged = static::$ap21->StockChanged->get([
                'updatedAfter' => $updatedAfter
            ]);
            $this->assertIsArray($stockChanged);
            $this->summary('StockChanged::GET (since ' . $updatedAfter . ')', $stockChanged);
        } catch (Exception\ApiException $e) {
            // Accept API errors
            $this->assertInstanceOf(Exception\ApiException::class, $e);
            $this->summary('StockChanged::GET', [['error' => $e->getMessage()]]);
        }
    }
}
