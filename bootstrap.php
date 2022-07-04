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
    'CountryCode'  => $_ENV['CountryCode'],
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
    /*
    $id = 4332;
    $product = $ap21->Product($id)->get(['CustomData' => "true", "ExtendedRefs" => "true"]);
    Log::debug("product", [count($product)]);
    echo sprintf(print_r($product, true));
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

    /*
    $products = $ap21->Product()->get();
    Log::debug("products", [count($products)]);
    //echo sprintf(print_r($products, true));
    */

    // persons
    $id = 1146;
    $person = $ap21->Person($id)->get();
    Log::debug("person", [count($person)]);
    echo sprintf(print_r($person, true));
    $transactions = $ap21->Person($id)->RetailTransactions->get();

    // add a new person
    /*
    $dataFile = sprintf("%s/data/post/person.xml", __DIR__);
    $personDataXml = file_get_contents($dataFile);
    $person = $ap21->Person()->post($personDataXml);
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
