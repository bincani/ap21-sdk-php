<?php
/**
 * class TestSimpleResource
 */

namespace PHPAP21;

class TestSimpleResource extends TestResource
{

    /**
     * @var string Resource name
     */
    public $resourceName;

    /**
     * @var array sample array for testing post
     */
    public $postArray;

    /**
     * @var array sample array for testing put
     */
    public $putArray;

    /**
     * @var array sample post with invalid data
     */
    public $errorPostArray;


    protected function setUp(): void
    {
        parent::setUp();
        $this->resourceName = preg_replace('/.+\\\\(\w+)Test$/', '$1', get_called_class());
    }

    /**
     * Test post resource
     *
     * @return int
     */
    public function testPost()
    {
        if (!$this->postArray) {
            $this->markTestSkipped($this->resourceName . ' is read-only: POST not supported');
        }
        $result = static::$ap21->{$this->resourceName}->post($this->postArray);
        $this->assertTrue(is_array($result));
        $this->assertNotEmpty($result);
        $this->summary($this->resourceName . '::POST', $result);
        return $result['id'];
    }

    /**
     * Test get resource
     */
    public function testGet()
    {
        $resource = static::$ap21->{$this->resourceName};
        $result = $resource->get();

        $this->assertTrue(is_array($result));
        $this->summary($this->resourceName . '::GET', $result);
        //Data posted, so cannot be empty
        if($this->postArray) {
            $this->assertNotEmpty($result);
        }
        if($resource->countEnabled) {
            //Count should match the result array count
            $count = static::$ap21->{$this->resourceName}->count();
            $this->assertEquals($count, count($result));
        }
    }

    /**
     * Test getting single resource by id
     *
     * @depends testPost
     */
    public function testGetSelf($id)
    {
        if (!$id) {
            $this->markTestSkipped($this->resourceName . '::testGetSelf skipped: no ID available (POST not supported or failed)');
        }
        $product = static::$ap21->{$this->resourceName}($id)->get();

        $this->assertTrue(is_array($product));
        $this->assertNotEmpty($product);
        $this->assertEquals($id, $product['id']);
        $this->summary($this->resourceName . '::GET(' . $id . ')', $product);
    }

    /**
     * Test put resource
     *
     * @depends testPost
     */
    public function testPut($id)
    {
        if (!$this->putArray) {
            $this->markTestSkipped($this->resourceName . ' is read-only: PUT not supported');
        }
        $result = static::$ap21->{$this->resourceName}($id)->put($this->putArray);
        $this->assertTrue(is_array($result));
        $this->assertNotEmpty($result);
        $this->summary($this->resourceName . '::PUT(' . $id . ')', $result);
        foreach($this->putArray as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }

    /**
     * Test delete resource
     *
     * @depends testPost
     */
    public function testDelete($id)
    {
        if (!$id) {
            $this->markTestSkipped($this->resourceName . '::testDelete skipped: no ID available (POST not supported or failed)');
        }
        $result = static::$ap21->{$this->resourceName}($id)->delete();
        $this->assertEmpty($result);
    }

    public function testPostError() {
        if (!$this->errorPostArray) {
            $this->markTestSkipped($this->resourceName . '::testPostError skipped: no error post data defined (POST not supported)');
        }
        $this->expectException('PHPAP21\\Exception\\ApiException');
        static::$ap21->{$this->resourceName}->post($this->errorPostArray);
    }
}
