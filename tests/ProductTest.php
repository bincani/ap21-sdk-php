<?php
/**
 * class ProductTest
 */

namespace PHPAP21;

class ProductTest extends TestSimpleResource
{
    /**
     * @inheritDoc
     */
    public $postArray = [];

    /**
     * posting invalid data to trigger an error
     */
    public $errorPostArray = ["description" => "blah"];

    /**
     * data ok
     */
    public $putArray = array("title" => "New product title");
}