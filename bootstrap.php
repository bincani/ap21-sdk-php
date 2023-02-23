<?php
/**
 * composer dump-autoload
 *
 * php -f bootstrap.php
 * see https://getcomposer.org/doc/01-basic-usage.md#autoloading
 */
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

use PHPAP21\Ap21SDK as Ap21SDK;
use PHPAP21\Log as Log;

// load .env config
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
Log::debug("env", $_ENV);

$config = array(
    'ApiUrl'       => $_ENV['ApiUrl'],
    'ApiUser'      => $_ENV['ApiUser'],
    'ApiPassword'  => $_ENV['ApiPassword'],
    'CountryCode'  => $_ENV['CountryCode']
    //'useCache'     => true
);

// Create the ap21 client object
$ap21 = new Ap21SDK($config);

try {
    /**
     * API info
     */
    $info = $ap21->Info->get();
    Log::info("info", [$info]);

    /**
     * reference types
     */
    /*
    $rTypes = $ap21->ReferenceType()->get();
    Log::debug("referenceTypes", [count($rTypes)]);
    $rTypes = $ap21->ReferenceType()->getByCode('brand');
    echo sprintf(print_r($rTypes, true));
    $rTypes = $ap21->ReferenceType(1)->get();
    echo sprintf(print_r($rTypes, true));
    */

    /**
     * reference
     */
    /*
    $ref = $ap21->Reference(1)->get();
    Log::debug("Reference", [count($ref->references)]);
    //echo sprintf("Reference[%s]=%s", $ref->name, print_r($ref->references, true));
    $val = $ap21->Reference(1)->getValue($id = 5025);
    Log::debug("Reference", [$id, $val]);
    */

    /**
     * product colour references
     */
    /*
    //$pColRef = $ap21->ProductColourReference()->get();
    $pColRef = $ap21->ProductColourReference(1242)->get();
    Log::debug("ProductColourReferences", [count($pColRef)]);
    */

    //list($code, $val) = $ap21->Reference($id)->getValue();

    /**
     * product
     */
    //$productId = 1933;
    //$productId = 4332;
    //$productId = 1486;
    //$productId = 26183;     // GGFU083214
    $productId = 1344; // AGF2562747
    //$productId = 147859; // GHFS644199
    //$productId = 18349; // GHFS644199

    /*
    if ($productId) {
        $product = $ap21->Product($productId)->get([
            'CustomData' => "true"
            //"ExtendedRefs" => "true"
        ]);
        Log::debug("product", [count($product)]);
        echo sprintf("product: %s", print_r($product, true));
    }
    */

    // populate references
    /*
    foreach($product['references'] as $id => $ref) {
        Log::debug("ref", [$id, $ref['key']]);
        list($code, $val) = $ap21->Reference($id)->getValue($ref['key']);
        Log::debug("ref", [$id, $ref['key'], $val]);
        $product['references'][$id]['code'] = $code;
        $product['references'][$id]['val'] = $val;
    }
    echo sprintf(print_r($product, true));
    */

    $products = $ap21->Product()->get([
        'CustomData' => "true"
        //"ExtendedRefs" => "true"
    ]);
    Log::debug("products", [count($products)]);
    //echo sprintf("products: %s", print_r($products, true));
    foreach($products as $product) {
        //echo sprintf("product: %s", print_r($product, true));
        //echo sprintf("product: %s", print_r($product, true));
        echo sprintf("product['customData']: %s", print_r($product['customData'], true));

        $keys = array_keys($product['customData']['Web Data']);
        //echo sprintf("product['customData']['Web Data']: %s\n", print_r($keys, true));

        $attSetName = $product['customData']['Web Data']['Magento Attribute Set Name'];
        //echo sprintf("product['customData']: %s\n", $attSetName);

        $images = $product['customData']['Web Data']['Images'];
        //echo sprintf("product['customData']['Web Data']['Images']: %s\n", $images);
        $json = json_decode($images);
        //echo sprintf("product['customData']['Web Data']['Images']: %s\n", print_r($json, true));
    }

    // persons
    /*
    $id = 1146;
    $person = $ap21->Person($id)->get();
    Log::debug("person", [count($person)]);
    echo sprintf(print_r($person, true));
    $transactions = $ap21->Person($id)->RetailTransactions->get();
    */

    // add a new person
    /*
    $dataFile = sprintf("%s/data/post/person.xml", __DIR__);
    $personDataXml = file_get_contents($dataFile);
    $person = $ap21->Person()->post($personDataXml);
    echo sprintf("person: %s\n", print_r($person, true));
    */

    // create a new order for a person
    /*
    $dataFile = sprintf("%s/data/post/order.xml", __DIR__);
    $orderDataXml = file_get_contents($dataFile);
    $person = $ap21->Person($personId)->Order->post($orderDataXml);
    */

    /**
     * update a person
     *
     * requires the correct UpdateTimeStamp value
     */
    /*
    $dataFile = sprintf("%s/data/put/person-update.xml", __DIR__);
    $personDataXml = file_get_contents($dataFile);
    $person = $ap21->Person(1149)->put($personDataXml);
    */

    /*
    $people = $ap21->Person()->get();
    Log::debug("people", [count($people)]);
    echo sprintf(print_r($people, true));
    */
}
catch(Exception $ex) {
    Log::error($ex->getMessage());
}
