<?php
/*
php -f dumpStyles.php > data/styles/202210/20221025_x02_styles.txt
php -f dumpStyles.php > data/styles/202210/20221025_x03_styles.txt
php -f dumpStyles.php > data/styles/202210/20221025_x04_styles.txt
php -f dumpStyles.php > data/styles/202210/20221025_x05_styles.txt
php -f dumpStyles.php > data/styles/202210/20221025_x06_styles.txt
php -f dumpStyles.php > data/styles/202210/20221025_x08_styles.txt
*/

$skuFileAp21 = sprintf("%s/data/skus/202210/%s", getcwd(), "20221025_x08_ap21_skus.txt");
//echo $skuFileAp21;

$skuAp21 = [];
//echo sprintf("skuFileAp21: %s\n", $skuFileAp21);
$fh1 = fopen($skuFileAp21,"r") or exit("Unable to open file!");
while ($line = fgets($fh1)) {
    $skuAp21[] = strtolower(trim($line));
}

$styleAp21 = [];
foreach($skuAp21 as $sku) {
    $style = substr($sku, 0, 10);
    if (!in_array($style, $styleAp21)) {
        $styleAp21[] = $style;
    }
}

$styleAp21 = array_unique($styleAp21);
//echo sprintf("result: %s\n", print_r($styleAp21, true));
foreach($styleAp21 as $style) {
    echo sprintf("%s\r\n", $style);
}