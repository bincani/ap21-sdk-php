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
     * reference types + references
     */
    /*
    try {
        $rTypes = $ap21->ReferenceType()->get();
        Log::info("referenceTypes", [count($rTypes)]);
        foreach ($rTypes as $rType) {
            Log::info("ReferenceType", [$rType['code'], $rType['name']]);
            try {
                $ref = $ap21->Reference($rType['id'])->get();
                Log::debug("Reference", [$rType['name'], count($ref->references)]);
            }
            catch(Exception $ex) {
                echo sprintf("Error: %s\n", $ex->getMessage());
            }
        }
    }
    catch(Exception $ex) {
        echo sprintf("Error: %s\n", $ex->getMessage());
    }
    */

    /**
     * reference type by code or id
     */
    /*
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
    list($code, $val) = $ap21->Reference($id = 1)->getValue($id = 5025);
    Log::debug("Reference", [$id, $code, $val]);
    */

    /**
     * colours
     */
    /*
    $colours = $ap21->Colour()->get();
    Log::debug("colours.count:", [count($colours)]);
    foreach($colours as $colourCode => $colour) {
        Log::debug("colour:", [$colourCode, $colour['name']]);
    }
    */

    /**
     * sizes
     */
    /*
    $sizes = $ap21->Size()->get();
    Log::debug("sizes.count:", [count($sizes)]);
    foreach($sizes as $sizeCode => $size) {
        Log::debug("size:", [$sizeCode]);
    }
    */

    /**
     * stores
     */
    /*
    $stores = $ap21->Store()->get();
    Log::debug("stores.count:", [count($stores)]);
    foreach($stores as $storeCode => $store) {
        Log::debug("store:", [$store]);
    }
    */

    /**
     * product colour references
     */
    /*
    //$pColRef = $ap21->ProductColourReference()->get();
    $pColRef = $ap21->ProductColourReference(1242)->get();
    Log::debug("ProductColourReferences", [count($pColRef)]);
    */

    /**
     * get free stock by Style, Clr, Sku & AllStyles
     */
    $productId = 25192;
    //$productId = 7752;
    try {
        $freestock = $ap21->Freestock->Style($productId)->get();
        echo sprintf("freestock.style(%d): %s\n", $productId, print_r($freestock, true));
    }
    catch(\Exception $ex) {
        echo sprintf("%s.Error: %s\n", get_class($ex), $ex->getMessage());
    }

    /*
    $skuId = 57611;
    $freestock = $ap21->Freestock->Sku($skuId)->get();
    echo sprintf("freestock.sku(%d): %s\n", $skuId, print_r($freestock, true));
    */

    /*
    $clrId = 16850;
    $freestock = $ap21->Freestock->Clr($clrId)->get();
    echo sprintf("freestock.clr(%d): %s\n", $clrId, print_r($freestock, true));
    */

    //$freestock = $ap21->Freestock->AllStyles->get();
    /*
    foreach($freestock as $styleId => $style) {
        echo sprintf("%s - %s\n", $styleId, $style['freestock']);
    }
    */

    /**
     * Product->FuturePrices
     */
    //$product = $ap21->Product->FuturePrice()->get();

    /**
     * get a product
     */
    /*
    $productId = 1344; // AGF2562747
    if ($productId) {
        $product = $ap21->Product($productId)->get([
            'CustomData' => "true"
            //"ExtendedRefs" => "true"
        ]);
        Log::debug("product", [count($product)]);
        echo sprintf("product: %s", print_r($product, true));
    }
    */

    /**
     * add reference data to product
     */
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

    /**
     * get all products
     */
    /*
    $products = [];
    $cnt = 0;
    $limit = 10;
    $startPage = 1;
    $pageRows = 10;
    $maxPages = 1;
    do {
        $cnt++;
        $urlParams = [
            //'CustomData' => "true"
            "ExtendedRefs" => "true",
            "startRow"  => $startPage,
            "pageRows"  => $pageRows,
            "limit"     => $limit
        ];
        $productApi = $ap21->Product();
        $products = array_merge($products, $productApi->get($urlParams));
        echo sprintf("=========> pages %d of %d (%d|%d)\n", $cnt, $maxPages, count($products), $productApi->getTotalProducts());
        $startPage += $pageRows;
    } while($cnt < $maxPages);

    foreach($products as $product) {
        echo sprintf("%s,%s\n", $product['id'], $product['code']);
    }
    */

    //echo sprintf("products: %s", print_r($products, true));
    //echo sprintf("totalPages: %d\n", $totalPages);

    /*
    //echo sprintf("products: %s", print_r($products, true));
    $brands = [];
    foreach($products as $product) {
        echo sprintf("%s,%s\n", $product['id'], $product['code']);
        echo sprintf("product: %s", print_r($product, true));

        $brand = substr($product['code'], 0, 1);
        if (!array_key_exists($brand, $brands)) {
            $brands[$brand] = [
                'enabled' => 0,
                'disabled' => 0
            ];
        }
        if (array_key_exists('customData', $product) && !empty($product['customData'])) {
            echo sprintf("product['customData']: %s", print_r($product['customData'], true));
            if (array_key_exists('Web Data', $product['customData'])) {
                //echo sprintf("product['customData']['Web Data']: %s\n", print_r($keys, true));
                $keys = array_keys($product['customData']['Web Data']);
                if (array_key_exists('Magento Attribute Set Name', $product['customData']['Web Data'])) {
                    $attSetName = $product['customData']['Web Data']['Magento Attribute Set Name'];
                    //echo sprintf("product['customData']: %s\n", $attSetName);
                }
                if (array_key_exists('Images', $product['customData']['Web Data'])) {
                    $images = $product['customData']['Web Data']['Images'];
                    //echo sprintf("product['customData']['Web Data']['Images']: %s\n", $images);
                    $json = json_decode($images);
                    //echo sprintf("product['customData']['Web Data']['Images']: %s\n", print_r($json, true));
                }
            }
        }
        if (array_key_exists('references', $product) && !empty($product['references'])) {
            //echo sprintf("product['references']: %s", print_r($product['references'], true));
            if ($product['references'][1521]['key']) {
                $brands[$brand]['enabled']++;
            }
            else {
                $brands[$brand]['disabled']++;
            }
        }
        if (array_key_exists('children', $product) && !empty($product['children'])) {
            foreach($product['children'] as $child) {
                echo sprintf("child: %s", print_r($child, true));
            }
        }
    }
    Log::info("products", [count($products)]);
    Log::info("apienabled", [$brands]);
    */

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
