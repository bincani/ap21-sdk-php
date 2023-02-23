<?php
/*
php -f uppercaseStyles.php > /var/www/brands/ap21/ap21-sdk-php/data/styles/202210/prod_ref_20221026_x02_no.csv
php -f uppercaseStyles.php > /var/www/brands/ap21/ap21-sdk-php/data/styles/202210/prod_ref_20221026_x03_no.csv
php -f uppercaseStyles.php > /var/www/brands/ap21/ap21-sdk-php/data/styles/202210/prod_ref_20221026_x04_no.csv
php -f uppercaseStyles.php > /var/www/brands/ap21/ap21-sdk-php/data/styles/202210/prod_ref_20221026_x05_no.csv
php -f uppercaseStyles.php > /var/www/brands/ap21/ap21-sdk-php/data/styles/202210/prod_ref_20221026_x06_no.csv
php -f uppercaseStyles.php > /var/www/brands/ap21/ap21-sdk-php/data/styles/202210/prod_ref_20221026_x08_no.csv

php -f uppercaseStyles.php > /var/www/brands/ap21/ap21-sdk-php/data/styles/202210/prod_ref_20221026_x02_yes.csv
php -f uppercaseStyles.php > /var/www/brands/ap21/ap21-sdk-php/data/styles/202210/prod_ref_20221026_x03_yes.csv
php -f uppercaseStyles.php > /var/www/brands/ap21/ap21-sdk-php/data/styles/202210/prod_ref_20221026_x04_yes.csv
php -f uppercaseStyles.php > /var/www/brands/ap21/ap21-sdk-php/data/styles/202210/prod_ref_20221026_x05_yes.csv
php -f uppercaseStyles.php > /var/www/brands/ap21/ap21-sdk-php/data/styles/202210/prod_ref_20221026_x06_yes.csv
php -f uppercaseStyles.php > /var/www/brands/ap21/ap21-sdk-php/data/styles/202210/prod_ref_20221026_x08_yes.csv
*/

$styleAp21 = [];
$skuFileAp21 = sprintf("%s/../data/styles/202210/%s", getcwd(), "prod_ref_20221025_x05_no.csv");
//echo sprintf("read: %s\n", $skuFileAp21);

//echo sprintf("skuFileAp21: %s\n", $skuFileAp21);
$fh1 = fopen($skuFileAp21, "r") or exit("Unable to open file!");

$row = 0;
$header = fgetcsv($fh1, 1000, ",");
while (($data = fgetcsv($fh1, 1000, ",")) !== FALSE) {
    $num = count($data);
    $row++;
    $style = strtoupper($data[0]);
    if (empty($style)) {
        continue;
    }
    $styleAp21[$style] = [];
    //echo sprintf("style: %s\n", $style);
    for ($c = 0; $c < $num; $c++) {
        $key = (array_key_exists($c, $header) ? $header[$c] : sprintf("col-%d", $c));
        if ($c == 2) {
            $data[$c] = "APIenabl";
        }
        $styleAp21[$style][$key] = $data[$c];
    }
}
fclose($fh1);

echo sprintf("%s\n", implode(",", $header));
foreach($styleAp21 as $styleCode => $data) {
    //echo sprintf("data: %s\n", print_r($data, true));
    echo sprintf("%s,%s\r\n", $styleCode, implode(",", array_slice(array_values($data), 1)));
}
