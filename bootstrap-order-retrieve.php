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

$createCustomer = false;
$email = 'developers+ap21sync@factoryx.com.au';

/*
try {
    $persons = $ap21->Person()->get(['updatedAfter' => '2025-01-01T00:00:00']);
    foreach($persons as $personId => $person) {
        //echo sprintf("person with email %s has id %s\n", print_r($person['contacts'], true), $personId);
        echo sprintf("person with email %s has id %s\n", $person['contacts']['Email'], $personId);
    }
}
catch(Exception $ex) {
    echo sprintf("Error: %s\n", $ex->getMessage());
    Log::error($ex->getMessage());
}
*/

try {
    // get a person
    echo sprintf("Person()->get: %s\n", $email);
    $person = $ap21->Person()->get(['email' => $email]);
    Log::debug("person", [count($person)]);
    //echo sprintf("person: %s\n", print_r($person, true));
    $personId = array_key_first($person);
    echo sprintf("person with email %s has id %s\n", $email, $personId);
}
catch(Exception $ex) {
    if (preg_match("/5008 - No data found/i", $ex->getMessage())) {
        $createCustomer = true;
    }
    else {
        echo sprintf("Error: %s\n", $ex->getMessage());
        Log::error($ex->getMessage());
    }
}

try {
    // get a persons orders
    /*
    $personOrders = $ap21->Person($personId)->Orders->get();
    //echo sprintf("%s->personOrders: %d\n", __METHOD__, count($personOrders));
    foreach($personOrders as $orderId => $personOrder) {
        //echo sprintf("personOrder[%d]: %s\n", $id, print_r($personOrder, true));
        echo sprintf("personOrder[%d]: %s\n", $orderId, $personOrder['number']);
    }
    */

    // get a persons order
    $orderId = 2438674;
    $personOrder = $ap21->Person($personId)->Orders($orderId)->get();
    echo sprintf("personOrders: %s\n", print_r($personOrder, true));

    // @TODO: get a persons orders returns
    // example request : POST https://retailapi.apparel21.com/RetailAPI/Persons/1241/Orders/12354/Returns?countryCode=AU
    //$orderReturns = $ap21->Person($personId)->Orders->get($orderId)->Returns;
    //$orderReturns = $ap21->Person($personId)->Orders($orderId)->Returns->get();
    //echo sprintf("orderReturns: %s\n", print_r($orderReturns, true));

    // @TODO: get a persons retails transactions
    // example request : https://retailapi.apparel21.com/RetailAPI/Persons/4883/RetailTransactions/?countryCode=AU&&OrderNumber= W12345&startRow=1&pageRows=20

    // @TODO: get a persons order shipments
    // /Persons/{PersonId}/Shipments/{OrderId}?countryCode={countryCode}

    // @TODO: get a persons timestamp
    /*
    The timestamp returned is required when updating a person record and is
    used to check that the record being updated has not been modified since
    the record was retrieved.
    */
    // /Persons/{id}/UpdateTimeStamp/?CountryCode={CountryCode}

    /*
    $personId = 1187652;
    $person = $ap21->Person($personId)->get();
    Log::debug("person", [count($person)]);
    echo sprintf(print_r($person, true));
    $transactions = $ap21->Person($personId)->RetailTransactions->get();
    */

}
catch(Exception $ex) {

    if (preg_match("/the order number already exists/i", $ex->getMessage())) {
        // ok error
        echo sprintf("Order Error: %s\n", $ex->getMessage());
    }
    else {
        echo sprintf("Error: %s\n", $ex->getMessage());
        Log::error($ex->getMessage());
    }
}
