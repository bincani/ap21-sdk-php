<?php
/**
 * class HTTPResource
 */

namespace PHPAP21;

use PHPAP21\CurlRequest;
use PHPAP21\Exception\ApiException;
use PHPAP21\Exception\SdkException;
use PHPAP21\Exception\CurlException;
use Psr\Http\Message\ResponseInterface;

abstract class HTTPResource implements HTTPResourceInterface
{
    /**
     * Resource config
     *
     * @var array
     */
    protected $config = [];

    /**
     * HTTP request headers
     *
     * @var array
     */
    protected $httpHeaders = array();

    /**
     * HTTP response headers of last executed request
     *
     * @var array
     */
    public static $lastHttpResponseHeaders = array();

    /**
     * The base URL of the API Resource (excluding ...).
     *
     * Example : https://myshop.myap21.com/admin/products
     *
     * @var string
     */
    protected $resourceUrl;

    /**
     * Key of the API Resource which is used to fetch data from request responses
     *
     * @var string
     */
    protected $resourceKey;

    /**
     * List of child Resource names / classes
     *
     * If any array item has an associative key => value pair, value will be considered as the resource name
     * (by which it will be called) and key will be the associated class name.
     *
     * @var array
     */
    protected $childResource = array();

    /**
     * If search is enabled for the resource
     *
     * @var boolean
     */
    public $searchEnabled = false;

    /**
     * If count is enabled for the resource
     *
     * @var boolean
     */
    public $countEnabled = true;

    /**
     * If the resource is read only. (No POST / PUT / DELETE actions)
     *
     * @var boolean
     */
    public $readOnly = false;

    /**
     * List of custom GET / POST / PUT / DELETE actions
     *
     * Custom actions can be used without calling the get(), post(), put(), delete() methods directly
     * @example: ['enable', 'disable', 'remove','default' => 'makeDefault']
     * Methods can be called like enable(), disable(), remove(), makeDefault() etc.
     * If any array item has an associative key => value pair, value will be considered as the method name
     * and key will be the associated path to be used with the action.
     *
     * @var array $customGetActions
     * @var array $customPostActions
     * @var array $customPutActions
     * @var array $customDeleteActions
     */
    protected $customGetActions = array();
    protected $customPostActions = array();
    protected $customPutActions = array();
    protected $customDeleteActions = array();

    /**
     * The ID of the resource
     *
     * If provided, the actions will be called against that specific resource ID
     *
     * @var integer
     */
    public $id;

    /**
     * Create a new Ap21 API resource instance.
     *
     * @param integer $id
     * @param string $parentResourceUrl
     *
     * @throws SdkException if Either AccessToken or ApiKey+Password Combination is not found in configuration
     */

    /**
     * Response Header Link, used for pagination
     * @see: https://help.ap21.com/en/api/guides/paginated-rest-results?utm_source=exacttarget&utm_medium=email&utm_campaign=api_deprecation_notice_1908
     * @var string $nextLink
     */
    private $nextLink = null;

    /**
     * Response Header Link, used for pagination
     * @see: https://help.ap21.com/en/api/guides/paginated-rest-results?utm_source=exacttarget&utm_medium=email&utm_campaign=api_deprecation_notice_1908
     * @var string $prevLink
     */
    private $prevLink = null;

    public function __construct($id = null, $parentResourceUrl = '')
    {
        //Log::debug(sprintf("%s->id: %s", __METHOD__, $id), [func_get_args()]);
        $this->id = $id;

        $this->config = Ap21SDK::$config;

        $this->resourceUrl = ($parentResourceUrl ? $parentResourceUrl . '/' :  $this->config['ApiUrl']) . $this->getResourcePath() . ($this->id ? '/' . $this->id : '');
        //Log::debug(sprintf("%s->resourceUrl", __METHOD__), [$parentResourceUrl, $this->resourceUrl]);

        if (isset($this->config['ApiUser']) && isset($this->config['ApiPassword'])) {
            $auth = base64_encode($this->config['ApiUser'] . ":" . $this->config['ApiPassword']);
            $this->httpHeaders['Authorization'] = sprintf("Basic %s", $auth);
        }
        elseif (isset($this->config['AccessToken'])) {
            $this->httpHeaders['X-Ap21-Access-Token'] = $this->config['AccessToken'];
        }
        elseif (!isset($this->config['ApiKey']) || !isset($this->config['Password'])) {
            throw new SdkException("Either AccessToken or ApiKey+Password Combination (in case of private API) is required to access the resources. Please check SDK configuration!");
        }

        if (isset($this->config['Ap21ApiFeatures'])) {
            foreach($this->config['Ap21ApiFeatures'] as $apiFeature) {
                $this->httpHeaders['X-Ap21-Api-Features'] = $apiFeature;
            }
        }
    }

    /**
     * Return HTTPResource instance for the child resource.
     *
     * @example $ap21->Product($productID)->Image->get(); //Here Product is the parent resource and Image is a child resource
     * Called like an object properties (without parenthesis)
     *
     * @param string $childName
     *
     * @return HTTPResource
     */
    public function __get($childName)
    {
        return $this->$childName();
    }

    /**
     * Return HTTPResource instance for the child resource or call a custom action for the resource
     *
     * @example $ap21->Product($productID)->Image($imageID)->get(); //Here Product is the parent resource and Image is a child resource
     * Called like an object method (with parenthesis) optionally with the resource ID as the first argument
     * @example $ap21->Discount($discountID)->enable(); //Calls custom action enable() on Discount resource
     *
     * Note : If the $name starts with an uppercase letter, it's considered as a child class and a custom action otherwise
     *
     * @param string $name
     * @param array $arguments
     *
     * @throws SdkException if the $name is not a valid child resource or custom action method.
     *
     * @return mixed / HTTPResource
     */
    public function __call($name, $arguments)
    {
        //If the $name starts with an uppercase letter, it's considered as a child class
        //Otherwise it's a custom action
        Log::debug(__METHOD__, ["name[0]:" . $name[0] . "|" . $name, "ctype_upper:" . ctype_upper($name[0])]);

        if (ctype_upper($name[0])) {
            //Get the array key of the childResource in the childResource array
            $childKey = array_search($name, $this->childResource);
            if ($childKey === false) {
                throw new SdkException(
                    sprintf("Child Resource %s is not available for %s", $name, $this->getResourceName())
                );
            }
            // If any associative key is given to the childname, then it will be considered as the class name,
            // otherwise the childname will be the class name
            $childClassName = !is_numeric($childKey) ? $childKey : $name;
            $childClass = sprintf(
                "%s\%s\%s",
                __NAMESPACE__,
                $this->getResourceName(),
                $childClassName
            );
            Log::debug(__METHOD__, ["childClass:" . $childClass]);

            if (!class_exists($childClass)) {
                throw new SdkException(
                    sprintf("Child class %s is not defined", $childClass)
                );
            }
            // If first argument is provided, it will be considered as the ID of the resource.
            $resourceID = !empty($arguments) ? $arguments[0] : null;
            $api = new $childClass($resourceID, $this->resourceUrl);
            return $api;
        }
        else {
            $actionMaps = array(
                'post'  =>  'customPostActions',
                'put'   =>  'customPutActions',
                'get'   =>  'customGetActions',
                'delete'=>  'customDeleteActions',
            );
            //Get the array key for the action in the actions array
            foreach ($actionMaps as $httpMethod => $actionArrayKey) {
                $actionKey = array_search($name, $this->$actionArrayKey);
                if ($actionKey !== false) break;
            }

            if ($actionKey === false) {
                throw new SdkException(
                    sprintf("No action named '%s' is defined for '%s'", $name, $this->getResourceName())
                );
            }

            //If any associative key is given to the action, then it will be considered as the method name,
            //otherwise the action name will be the method name
            $customAction = !is_numeric($actionKey) ? $actionKey : $name;


            //Get the first argument if provided with the method call
            $methodArgument = !empty($arguments) ? $arguments[0] : array();

            //Url parameters
            $urlParams = array();
            //Data body
            $dataArray = array();

            //Consider the argument as url parameters for get and delete request
            //and data array for post and put request
            if ($httpMethod == 'post' || $httpMethod == 'put') {
                $dataArray = $methodArgument;
            } else {
                $urlParams =  $methodArgument;
            }

            $url = $this->generateUrl($urlParams, $customAction);

            if ($httpMethod == 'post' || $httpMethod == 'put') {
                return $this->$httpMethod($dataArray, $url, false);
            } else {
                return $this->$httpMethod($dataArray, $url);
            }
        }
    }

    /**
     * Get the resource name (or the class name)
     *
     * @return string
     */
    public function getResourceName()
    {
        return substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
    }

    /**
     * Get the resource key to be used for while sending data to the API
     *
     * Normally its the same as $resourceKey, when it's different, the specific resource class will override this function
     *
     * @return string
     */
    public function getResourcePostKey()
    {
        return $this->resourceKey;
    }

    /**
     * Get the pluralized version of the resource key
     *
     * Normally its the same as $resourceKey appended with 's', when it's different, the specific resource class will override this function
     *
     * @return string
     */
    public function pluralizeKey()
    {
        return $this->resourceKey . 's';
    }

    /**
     * Get the resource path to be used to generate the api url
     *
     * Normally its the same as the pluralized version of the resource key,
     * when it's different, the specific resource class will override this function
     *
     * @return string
     */
    public function getResourcePath()
    {
        return $this->pluralizeKey();
    }

    /**
     * Generate the custom url for api request based on the params and custom action (if any)
     *
     * @param array $param
     * @param string $customAction
     *
     * @return string
     */
    public function generateUrl($params = [], $customAction = null)
    {
        $urlParams['CountryCode'] = Ap21SDK::$config['CountryCode'];
        if (is_array($params)) {
            $urlParams = array_merge($urlParams, $params);
        }
        Log::debug(sprintf("%s->params", __METHOD__), [$this->resourceUrl, $params, $customAction, $urlParams]);
        $url = sprintf("%s%s%s",
            $this->resourceUrl,
            ($customAction ? "/$customAction" : '') ,
            (!empty($urlParams) ? '?' . http_build_query($urlParams) : '')
        );
        Log::debug(sprintf("%s->url", __METHOD__), [$url]);
        return $url;
    }

    /**
     * Generate a HTTP GET request and return results as an array
     *
     * @param array $urlParams Check Ap21 API reference of the specific resource for the list of URL parameters
     * @param string $url
     * @param string $dataKey Keyname to fetch data from response array
     *
     * @uses HttpRequest::get() to send the HTTP request
     *
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    public function get($urlParams = array(), $url = null, $dataKey = null)
    {
        if (!$url) {
            $url  = $this->generateUrl($urlParams);
        }
        $response = HttpRequest::get($url, $this->httpHeaders);
        if (!$dataKey) {
            $dataKey = $this->id ? $this->resourceKey : $this->pluralizeKey();
        }
        return $this->processResponse($response, $dataKey);
    }

    /**
     * Get count for the number of resources available
     *
     * @param array $urlParams Check Ap21 API reference of the specific resource for the list of URL parameters
     *
     * @throws SdkException
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return integer
     */
    public function count($urlParams = array())
    {
        if (!$this->countEnabled) {
            throw new SdkException("Count is not available for " . $this->getResourceName());
        }
        $url = $this->generateUrl($urlParams, 'count');
        return $this->get(array(), $url, 'count');
    }

    /**
     * Search within the resouce
     *
     * @param mixed $query
     *
     * @throws SdkException if search is not enabled for the resouce
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    public function search($query)
    {
        if (!$this->searchEnabled) {
            throw new SdkException("Search is not available for " . $this->getResourceName());
        }

        if (!is_array($query)) $query = array('query' => $query);

        $url = $this->generateUrl($query, 'search');

        return $this->get(array(), $url);
    }

    /**
     * Call POST method to create a new resource
     *
     * @param array $dataArray Check Ap21 API reference of the specific resource for the list of required and optional data elements to be provided
     * @param string $url
     * @param bool $wrapData
     *
     * @uses HttpRequest::post() to send the HTTP request
     *
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    public function post($dataArray, $url = null, $wrapData = false)
    {
        if (!$url) {
            $url = $this->generateUrl();
        }
        if ($wrapData && !empty($dataArray)) {
            $dataArray = $this->wrapData($dataArray);
        }
        $response = HttpRequest::post($url, $dataArray, $this->httpHeaders);
        return $this->processResponse($response, $this->resourceKey);
    }

    /**
     * Call PUT method to update an existing resource
     *
     * @param array $dataArray Check Ap21 API reference of the specific resource for the list of required and optional data elements to be provided
     * @param string $url
     * @param bool $wrapData
     *
     * @uses HttpRequest::put() to send the HTTP request
     *
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    public function put($dataArray, $url = null, $wrapData = true)
    {
        if (!$url) {
            $url = $this->generateUrl();
        }
        if ($wrapData && !empty($dataArray)) {
            $dataArray = $this->wrapData($dataArray);
        }
        $response = HttpRequest::put($url, $dataArray, $this->httpHeaders);
        return $this->processResponse($response, $this->resourceKey);
    }

    /**
     * Call DELETE method to delete an existing resource
     *
     * @param array $urlParams Check Ap21 API reference of the specific resource for the list of URL parameters
     * @param string $url
     *
     * @uses HttpRequest::delete() to send the HTTP request
     *
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array an empty array will be returned if the request is successfully completed
     */
    public function delete($urlParams = array(), $url = null)
    {
        if (!$url) {
            $url = $this->generateUrl($urlParams);
        }
        $response = HttpRequest::delete($url, $this->httpHeaders);
        return $this->processResponse($response);
    }

    /**
     * Wrap data array with resource key
     *
     * @param array $dataArray
     * @param string $dataKey
     *
     * @return array
     */
    public function wrapData($dataArray, $dataKey = null)
    {
        if (!$dataKey) {
            $dataKey = $this->getResourcePostKey();
        }
        return array($dataKey => $dataArray);
    }

    /**
     * Convert an array to string
     *
     * @param array $array
     *
     * @internal
     *
     * @return string
     */
    public function castString($array)
    {
        if ( ! is_array($array)) return (string) $array;

        $string = '';
        $i = 0;
        foreach ($array as $key => $val) {
            //Add values separated by comma
            //prepend the key string, if it's an associative key
            //Check if the value itself is another array to be converted to string
            $string .= ($i === $key ? '' : "$key - ") . $this->castString($val) . ', ';
            $i++;
        }

        //Remove trailing comma and space
        $string = rtrim($string, ', ');

        return $string;
    }

    /**
     * Process the request response
     *
     * @param array $responseArray Request response in array format
     * @param string $dataKey Keyname to fetch data from response array
     *
     * @throws ApiException if the response has an error specified
     * @throws CurlException if response received with unexpected HTTP code.
     *
     * @return array
     */
    abstract public function processResponse($responseArray, $dataKey = null);

    /*
    public function processResponse($responseArray, $dataKey = null) {
        self::$lastHttpResponseHeaders = CurlRequest::$lastHttpResponseHeaders;

        $lastResponseHeaders = CurlRequest::$lastHttpResponseHeaders;
        $this->getLinks($lastResponseHeaders);

        if (isset($responseArray['errors'])) {
            $message = $this->castString($responseArray['errors']);
            // check account already enabled or not
            if ($message == 'account already enabled') {
                return ['account_activation_url' => false];
            }
            throw new ApiException($message, CurlRequest::$lastHttpCode);
        }
        if ($dataKey && isset($responseArray[$dataKey])) {
            return $responseArray[$dataKey];
        }
        else {
            return $responseArray;
        }
    }
    */

    public function getLinks($responseHeaders){
        $this->nextLink = $this->getLink($responseHeaders,'next');
        $this->prevLink = $this->getLink($responseHeaders,'previous');
    }

    public function getLink($responseHeaders, $type='next'){

        if(array_key_exists('x-ap21-api-version', $responseHeaders)
            && $responseHeaders['x-ap21-api-version'] < '2019-07'){
            return null;
        }

        if(!empty($responseHeaders['link'])) {
            if (stristr($responseHeaders['link'], '; rel="'.$type.'"') > -1) {
                $headerLinks = explode(',', $responseHeaders['link']);
                foreach ($headerLinks as $headerLink) {
                    if (stristr($headerLink, '; rel="'.$type.'"') === -1) {
                        continue;
                    }

                    $pattern = '#<(.*?)>; rel="'.$type.'"#m';
                    preg_match($pattern, $headerLink, $linkResponseHeaders);
                    if ($linkResponseHeaders) {
                        return $linkResponseHeaders[1];
                    }
                }
            }
        }

        return null;
    }

    public function getPrevLink(){
        return $this->prevLink;
    }

    public function getNextLink(){
        return $this->nextLink;
    }

    public function getUrlParams($url) {
        if ($url) {
            $parts = parse_url($url);
            return $parts['query'];
        }
        return '';
    }

    public function getNextPageParams(){
        $nextPageParams = [];
        parse_str($this->getUrlParams($this->getNextLink()), $nextPageParams);
        return $nextPageParams;
    }

    public function getPrevPageParams(){
        $nextPageParams = [];
        parse_str($this->getUrlParams($this->getPrevLink()), $nextPageParams);
        return $nextPageParams;
    }

    /**
     * innerHTML
     *
     * @param \DOMElement $element
     * @return void
     */
    function innerHTML(\DOMElement $element) {
        $doc = $element->ownerDocument;
        $html = '';
        foreach ($element->childNodes as $node) {
            $html .= $doc->saveHTML($node);
        }
        return $html;
    }
}
