<?php

namespace PHPAP21;

class ReferenceTest extends TestResource
{
    /**
     * Test GET references by type ID
     *
     * Uses a known reference type to fetch references.
     * First get reference types, then use the first one.
     */
    public function testGet()
    {
        $referenceTypes = static::$ap21->ReferenceType->get();
        $this->assertIsArray($referenceTypes);

        if (empty($referenceTypes)) {
            $this->markTestSkipped('No reference types available');
        }

        $firstType = reset($referenceTypes);
        $typeId = $firstType['id'];

        $result = static::$ap21->Reference($typeId)->get();
        $this->assertIsObject($result);
        $this->assertObjectHasProperty('id', $result);
        $this->assertObjectHasProperty('code', $result);
        $this->assertObjectHasProperty('references', $result);
        $this->summary('Reference(' . $typeId . ')::GET', $result);
    }
}
