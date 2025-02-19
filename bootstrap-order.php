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

$email = 'ben.incani+1@factoryx.com.au';

try {
    // add a new person
    $dataFile = sprintf("%s/data/post/person.xml", __DIR__);
    $personDataXml = file_get_contents($dataFile);
    //echo sprintf("Person()->post: %s\n", $personDataXml);
    $person = $ap21->Person()->post($personDataXml);
    echo sprintf("person: %s\n", print_r($person, true));
}
catch(Exception $ex) {
    if (preg_match("/email already exists for another person/i", $ex->getMessage())) {
        // ok error
    }
    else {
        echo sprintf("Error: %s\n", $ex->getMessage());
        Log::error($ex->getMessage());
    }
}

try {
    // get a person
    echo sprintf("Person()->get: %s\n", $email);
    $person = $ap21->Person()->get(['email' => $email]);
    Log::debug("person", [count($person)]);
    echo sprintf("person: %s\n", print_r($person, true));
    $personId = array_key_first($person);
    echo sprintf("person with email %s has id %s\n", $email, $personId);

    // get a persons orders
    $personOrders = $ap21->Person($personId)->Orders->get();
    echo sprintf("personOrders: %s\n", print_r($personOrders, true));

    // get a persons order
    //$orderId = ;
    //$personOrders = $ap21->Person($personId)->Orders->get($orderId);
    //echo sprintf("personOrders: %s\n", print_r($personOrders, true));

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

    // create a new order for a person
    $dataFile = sprintf("%s/data/post/order.xml", __DIR__);
    $orderDataXml = file_get_contents($dataFile);
    $orderDataXml = preg_replace("/%PersonId%/", $personId, $orderDataXml);
    echo sprintf("Person(%s)->Order->post: %s\n", $personId, $orderDataXml);
    // example request https://retailapi.apparel21.com/RetailAPI/Persons/101451/Orders/?countryCode=AU
    $order = $ap21->Person($personId)->Orders->post($orderDataXml);
    echo sprintf("order: %s\n", print_r($order, true));

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

    /**
     * get all persons
     */
    //$people = $ap21->Person()->get();
    //Log::debug("people", [count($people)]);
    //echo sprintf(print_r($people, true));

    /**
     * get all persons - with paging
     */
    /*
    $persons = [];
    $cnt = 0;
    $limit = 10;
    $startPage = 1;
    $pageRows = 10;
    $maxPages = 10;
    do {
        $cnt++;
        $urlParams = [
            "ExtendedRefs" => "true",
            "startRow"  => $startPage,
            "pageRows"  => $pageRows,
            "limit"     => $limit
        ];
        $personApi = $ap21->Person();
        $persons = array_merge($persons, $personApi->get($urlParams));
        echo sprintf("=========> pages %d of %d (%d|%d)\n", $cnt, $maxPages, count($persons), $personApi->getTotalProducts());
        $startPage += $pageRows;
    } while($cnt < $maxPages);

    foreach($persons as $person) {
        echo sprintf("%s,%s\n", $person['id'], $person['email']);
    }
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
