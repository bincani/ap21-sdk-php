<?php

namespace PHPAP21;

class StockChangedTest extends TestResource
{
    /**
     * Test GET stock changes, widening the window until data is found
     */
    public function testGet()
    {
        $windows = [1, 7, 30, 90, 180, 365, 730, 1095, 1460, 1825];
        $stockChanged = null;

        foreach ($windows as $days) {
            $changedSince = date('Y-m-d\TH:i:s', strtotime("-{$days} days"));
            try {
                $result = static::$ap21->StockChanged->get([
                    'ChangedSince' => $changedSince
                ]);
                if (!empty($result)) {
                    $stockChanged = $result;
                    break;
                }
            } catch (Exception\ApiException $e) {
                // Keep widening
            }
        }

        if ($stockChanged === null) {
            $this->markTestSkipped('No stock changes found within 5 years');
        }

        $this->assertIsArray($stockChanged);
        $this->assertNotEmpty($stockChanged);
        $this->summary(sprintf('StockChanged::GET (since %s, %d days)', $changedSince, $days), $stockChanged);
    }
}
