<?php
/**
 * php -f dumpStockByStyle.php > 20230324_x04_ap21_freestock.csv &
 *
RETRIEVE FREESTOCK By style or sku
/Freestock/style/{styleid}?countryCode={countryCode}
/Freestock/clr/{clr}?countryCode={countryCode}
/Freestock/sku/{skuid}?countryCode={countryCode}

RETRIEVE FREESTOCK OF ALL STYLES
/Freestock/AllStyles?countryCode={countryCode}&startRow={startingPositionofStyleReturned}&pageRows={numberOfStylesReturned}
/Freestock/AllStyles/{StoreId}?countryCode={countryCode}&startRow={startingPositionofStyleReturned}&pageRows={numberOfStylesReturned}
 */

require_once __DIR__ . './../vendor/autoload.php';

use Dotenv\Dotenv;

use PHPAP21\Ap21SDK as Ap21SDK;
use PHPAP21\Log as Log;

// load .env config
$dotenv = Dotenv::createImmutable(__DIR__ . './../');
$dotenv->safeLoad();
Log::debug("env", $_ENV);

$config = array(
    'ApiUrl'       => $_ENV['ApiUrl'],
    'ApiUser'      => $_ENV['ApiUser'],
    'ApiPassword'  => $_ENV['ApiPassword'],
    'CountryCode'  => $_ENV['CountryCode'],
    'useCache'     => true
);

// Create the ap21 client object
$ap21 = new Ap21SDK($config);
$cnt = 0;
$limit = 0;
$totalFreestock = 0;
echo sprintf("ap21_product_id,style_code,style_name,units\r\n");

try {
    $products = $ap21->Product()->get([
        'CustomData'    => "false",
        "ExtendedRefs"  => "false"
    ]);
    $productCodes = [];
    foreach($products as $productId => $product) {
        if ($productId) {
            $productCodes[$productId] = $product['code'];
        }
        else {
            throw new Exception(sprintf("product has no id: %s", print_r($product, true)));
        }
    }

    $styles = $ap21->Freestock()->get([
        /*
        "StoreId"   => 0,   // store id
        "startRow"  => 0,   // start row
        "pageRows"  => 0    // page rows
        */
    ]);
    Log::debug("styles.count: ", [count($styles)]);
    //echo sprintf("products: %s", print_r($styles, true));
    foreach($styles as $productId => $style) {
        //echo sprintf("product: %s", print_r($style, true));
        if ($limit != 0 && $cnt >= $limit) {
            echo sprintf("limit %d reached\r\n", $limit);
            break;
        }
        if (array_key_exists($productId, $productCodes)) {
            $productCode = $productCodes[$productId];
        }
        else {
            throw new Exception(sprintf("product id %d has no code!", $productId));
        }
        echo sprintf("%s,%s,%s,%d\r\n", $productId, $productCode, $style['name'], $style['freestock']);
        $totalFreestock += $style['freestock'];
        $cnt++;
    }
    //echo sprintf("%s->product.count: %d\n", __METHOD__, $cnt);
    //echo sprintf("%s->total.stock: %d\n", __METHOD__, $totalFreestock);
}
catch(Exception $ex) {
    Log::error($ex->getMessage());
}
