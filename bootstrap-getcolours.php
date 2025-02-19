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
     * colours
     */
    Log::info("running Colour()->get...", []);
    $colours = $ap21->Colour()->get();
    foreach($colours as $colourCode => $colour) {
        Log::info("colour:", [$colourCode, $colour['name']]);
    }
    Log::info("colours.count:", [count($colours)]);

    /**
     * product colour references
     */
    /*
    //$pColRef = $ap21->ProductColourReference()->get();
    $pColRef = $ap21->ProductColourReference(1242)->get();
    Log::debug("ProductColourReferences", [count($pColRef)]);
    */
}
catch(Exception $ex) {
    Log::error($ex->getMessage());
}
