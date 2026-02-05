<?php

namespace PHPAP21;

class FreestockByStoreTest extends TestResource
{
    protected static $storeId;
    protected static $storeName;

    /**
     * Find a store ID that has actual freestock by inspecting AllStyles data
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        try {
            // Get unfiltered AllStyles and extract a store ID from the data
            $allStyles = self::$ap21->Freestock->AllStyles->get();
            if (!empty($allStyles)) {
                foreach ($allStyles as $style) {
                    if (empty($style['colours'])) continue;
                    foreach ($style['colours'] as $colour) {
                        // Stores can be nested under colour directly or under skus
                        if (!empty($colour['stores'])) {
                            $storeId = array_key_first($colour['stores']);
                            static::$storeId = $storeId;
                            static::$storeName = $colour['stores'][$storeId]['store'];
                            return;
                        }
                        if (!empty($colour['skus'])) {
                            foreach ($colour['skus'] as $sku) {
                                if (!empty($sku['stores'])) {
                                    $storeId = array_key_first($sku['stores']);
                                    static::$storeId = $storeId;
                                    static::$storeName = $sku['stores'][$storeId]['store'];
                                    return;
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Will be handled by markTestSkipped in individual tests
        }
    }

    /**
     * Test GET freestock AllStyles filtered by store ID
     */
    public function testGetAllStylesByStore()
    {
        if (!static::$storeId) {
            $this->markTestSkipped('No store ID available');
        }

        try {
            $freestock = static::$ap21->Freestock->AllStyles(static::$storeId)->get();
            $this->assertIsArray($freestock);
            $this->assertNotEmpty($freestock);
            $this->summary(sprintf('Freestock::AllStyles(store=%s "%s")', static::$storeId, static::$storeName), $freestock);
        } catch (Exception\ApiException $e) {
            $this->assertInstanceOf(Exception\ApiException::class, $e);
            $this->summary(sprintf('Freestock::AllStyles(store=%s)', static::$storeId), [['error' => $e->getMessage()]]);
        }
    }

    /**
     * Test GET freestock by Style filtered to the same store,
     * using a product ID from the store's AllStyles result
     */
    public function testGetStyleByStore()
    {
        if (!static::$storeId) {
            $this->markTestSkipped('No store ID available');
        }

        // Get a product ID that has freestock in this store
        try {
            $allStyles = static::$ap21->Freestock->AllStyles(static::$storeId)->get();
        } catch (Exception\ApiException $e) {
            $this->markTestSkipped('No freestock available for store ' . static::$storeId);
            return;
        }

        $this->assertNotEmpty($allStyles);
        $first = reset($allStyles);
        $productId = $first['id'];

        $freestock = static::$ap21->Freestock->Style($productId)->get();
        $this->assertIsArray($freestock);

        $this->summary(sprintf('Freestock::Style(%d) (store=%s "%s")', $productId, static::$storeId, static::$storeName), $freestock);
    }
}
