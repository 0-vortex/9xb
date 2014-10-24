#!/usr/bin/php -q
<?php
require_once __DIR__ . '/DeliveryEstimate.php';

$arguments = $argv;

if (! isset($argv[1])) {
    echo "Date parameter missing" . PHP_EOL
         . "The script accepts any valid php DateTime" . PHP_EOL
         . "Example: 2014-10-30T15:00:00, 2014-10-30T15:00:00+02:00, 1414150000" . PHP_EOL;
} else {
    // get estimate if date is set
    $date = new DeliveryEstimate($argv[1]);

    if ($date->error !== false) {
        echo $date->error;
    } else {
        echo $date->deliveryDate;
    }
}
