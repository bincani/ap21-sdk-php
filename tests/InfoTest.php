<?php

namespace PHPAP21;

class InfoTest extends TestResource
{
    /**
     * Test retrieving API info
     */
    public function testGet()
    {
        $info = static::$ap21->Info->get();

        $this->assertIsArray($info);
        $this->assertNotEmpty($info);
        $this->assertArrayHasKey('api_ver', $info);
        $this->assertArrayHasKey('payloads', $info);
        $this->summary('Info::GET', [$info]);
    }
}
