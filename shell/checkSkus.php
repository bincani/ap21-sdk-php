<?php
/*
php -f checkSkus.php
php -f checkSkus.php > 20220805_x03_missing_skus.txt
php -f checkSkus.php > 20220804_x05_missing_skus.txt
php -f checkSkus.php > 20220804_x06_missing_skus.txt
php -f checkSkus.php > 20220804_x08_missing_skus.txt
*/

$skuFileAp21 = sprintf("%s/%s", getcwd(), "20220805_x03_ap21_skus.txt");
$skuFileMage = sprintf("%s/%s", getcwd(), "20220805_x03_order_skus.txt");

$skuAp21 = [];
//echo sprintf("skuFileAp21: %s\n", $skuFileAp21);
$fh1 = fopen($skuFileAp21,"r") or exit("Unable to open file!");
while ($line = fgets($fh1)) {
    $skuAp21[] = strtolower(trim($line));
}

//echo sprintf("skuFileMage: %s\n", $skuFileMage);
$fh2 = fopen($skuFileMage,"r") or exit("Unable to open file!");
while ($line = fgets($fh2)) {
    $skuMage[] = strtolower(trim($line));
}

$missing = [];
foreach($skuMage as $sku) {
    if (!in_array($sku, $skuAp21)) {
        $missing[] = $sku;
    }
}

$missing = array_unique($missing);
//echo sprintf("result: %s\n", print_r($missing, true));
foreach($missing as $sku) {
    echo sprintf("%s\n", $sku);
}