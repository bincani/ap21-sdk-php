<?php
/**
 * class Ap21SDK
 */

namespace PHPAP21;

use PHPAP21\Exception\SdkException;

class Ap21SDK
{
    /**
     * List of available resources which can be called from this client
     *
     * @var string[]
     */
    protected $resources = array(
        'Colour',
        'Freestock',
        'Info',
        'Order',
        'Person',
        'Product',
        'ProductColourReference',
        'Reference',
        'ReferenceType',
        'Size',
        'StockChanged',
        'Store'
    );

    /**
     * @var float microtime of last api call
     */
    public static $microtimeOfLastApiCall;

    /**
     * @var float Minimum gap in seconds to maintain between 2 api calls
     */
    public static $timeAllowedForEachApiCall = .5;

    /**
     * @var string Default Ap21 API version
     */
    public static $defaultApiVersion = '2022.2';

    /**
     * Shop / API configurations
     *
     * @var array
     */
    public static $config = array(
    );

    /**
     * List of resources which are only available through a parent resource
     *
     * @var array Array key is the child resource name and array value is the parent resource name
     */
    protected $childResources = array(
    );

    /*
     * Ap21SDK constructor
     *
     * @param array $config
     *
     * @return void
     */
    public function __construct($config = array())
    {
        if (!empty($config)) {
            Ap21SDK::config($config);
        }
    }

    /**
     * Return HTTPResource instance for a resource.
     * @example $ap21->Product->get(); //Returns all available Products
     * Called like an object properties (without parenthesis)
     *
     * @param string $resourceName
     *
     * @return HTTPResource
     */
    public function __get($resourceName)
    {
        return $this->$resourceName();
    }

    /**
     * Return HTTPResource instance for a resource.
     * Called like an object method (with parenthesis) optionally with the resource ID as the first argument
     * @example $ap21->Product($productID); //Return a specific product defined by $productID
     *
     * @param string $resourceName
     * @param array $arguments
     *
     * @throws SdkException if the $name is not a valid HTTPResource resource.
     *
     * @return HTTPResource
     */
    public function __call($resourceName, $arguments)
    {
        Log::debug(sprintf("%s->resourceName: %s", __METHOD__, $resourceName), $arguments);
        if (!in_array($resourceName, $this->resources)) {
            if (isset($this->childResources[$resourceName])) {
                $message = "$resourceName is a child resource of " . $this->childResources[$resourceName] . ". Cannot be accessed directly.";
            } else {
                $message = "Invalid resource name $resourceName. Pls check the API Reference to get the appropriate resource name.";
            }
            throw new SdkException($message);
        }

        $resourceClassName = __NAMESPACE__ . "\\$resourceName";
        Log::debug(sprintf("%s->resourceClassName: %s", __METHOD__, $resourceClassName));
        //If first argument is provided, it will be considered as the ID of the resource.
        $resourceID = !empty($arguments) ? $arguments[0] : null;

        //Initiate the resource object
        $resource = new $resourceClassName($resourceID);

        return $resource;
    }

    /**
     * Configure the SDK client
     *
     * @param array $config
     *
     * @return Ap21SDK
     */
    public static function config($config)
    {
        /**
         * Reset config to it's initial values
         */
        self::$config = array(
            'ApiVersion' => self::$defaultApiVersion
        );

        foreach ($config as $key => $value) {
            self::$config[$key] = $value;
        }

        //Re-set the admin url if shop url is changed
        if(isset($config['ShopUrl'])) {
            self::setAdminUrl();
        }

        //If want to keep more wait time than .5 seconds for each call
        if (isset($config['AllowedTimePerCall'])) {
            static::$timeAllowedForEachApiCall = $config['AllowedTimePerCall'];
        }

        if (isset($config['Curl']) && is_array($config['Curl'])) {
            CurlRequest::config($config['Curl']);
        }

        return new Ap21SDK;
    }

    /**
     * Set the admin url, based on the configured shop url
     *
     * @return string
     */
    public static function setAdminUrl()
    {
        $shopUrl = self::$config['ShopUrl'];

        //Remove https:// and trailing slash (if provided)
        $shopUrl = preg_replace('#^https?://|/$#', '', $shopUrl);
        $apiVersion = self::$config['ApiVersion'];

        if(isset(self::$config['ApiKey']) && isset(self::$config['Password'])) {
            $apiKey = self::$config['ApiKey'];
            $apiPassword = self::$config['Password'];
            $adminUrl = "https://$apiKey:$apiPassword@$shopUrl/admin/";
        } else {
            $adminUrl = "https://$shopUrl/admin/";
        }

        self::$config['AdminUrl'] = $adminUrl;
        self::$config['ApiUrl'] = $adminUrl . "api/$apiVersion/";

        return $adminUrl;
    }

    /**
     * Get the admin url of the configured shop
     *
     * @return string
     */
    public static function getAdminUrl() {
        return self::$config['AdminUrl'];
    }

    /**
     * Get the api url of the configured shop
     *
     * @return string
     */
    public static function getApiUrl() {
        return self::$config['ApiUrl'];
    }

    /**
     * Maintain maximum 2 calls per second to the API
     *
     * @see https://help.ap21.com/api/guides/api-call-limit
     *
     * @param bool $firstCallWait Whether to maintain the wait time even if it is the first API call
     */
    public static function checkApiCallLimit($firstCallWait = false)
    {
        $timeToWait = 0;
        if (static::$microtimeOfLastApiCall == null) {
            if ($firstCallWait) {
                $timeToWait = static::$timeAllowedForEachApiCall;
            }
        } else {
            $now = microtime(true);
            $timeSinceLastCall = $now - static::$microtimeOfLastApiCall;
            //Ensure 2 API calls per second
            if($timeSinceLastCall < static::$timeAllowedForEachApiCall) {
                $timeToWait = static::$timeAllowedForEachApiCall - $timeSinceLastCall;
            }
        }

        if ($timeToWait) {
            //convert time to microseconds
            $microSecondsToWait = $timeToWait * 1000000;
            //Wait to maintain the API call difference of .5 seconds
            usleep($microSecondsToWait);
        }

        static::$microtimeOfLastApiCall = microtime(true);
    }
}
