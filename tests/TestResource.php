<?php
/**
 * class SimpleResource
 */

namespace PHPAP21;

class TestResource extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Ap21SDK $ap21;
     */
    public static $ap21;

    /**
     * setUpBeforeClass
     */
    public static function setUpBeforeClass(): void
    {
        $config = array(
            'ApiUrl'       => getenv('ApiUrl'),
            'ApiUser'      => getenv('ApiUser'),
            'ApiPassword'  => getenv('ApiPassword'),
            'CountryCode'  => getenv('CountryCode')
        );

        self::$ap21 = Ap21SDK::config($config);
        //Ap21SDK::checkApiCallLimit();
    }

    /**
     * tearDownAfterClass
     */
    public static function tearDownAfterClass(): void
    {
        self::$ap21 = null;
    }
}