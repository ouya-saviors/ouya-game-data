#!/usr/bin/env php
<?php
/**
 * Create JSON product list from an adb log product data request URL
 *
 * URLs look like "/api/v1/developers/8ccff94e-6ac3-4f99-9d29-2f21e8d881f9/products/"
 * and have a "&only=" somewhere at the end.
 *
 * Capture them by adding DEBUG=1 to ouya_config.properties and then starting
 * the game.
 */
if ($argc < 2) {
    fwrite(STDERR, 'Pass the URL containing "&only=" or the part after that' . "\n");
    exit(1);
}
$url = $argv[1];

if (strpos($url, '&only=') !== false) {
    $param = substr($url, strpos($url, '&only=') + 6);
} else {
    //assume it's only the only parameter value
    $param = $url;
}

$parts = explode('%2C', $param);

$tpl = [
    'promoted'      => false,
    'type'          => 'entitlement',
    'identifier'    => '',
    'name'          => '',
    'localPrice'    => 0.01,
    'originalPrice' => 0.01,
    'currency'      => 'EUR',
];

$products = [];
foreach ($parts as $ident) {
    $product = $tpl;
    $product['identifier'] = $ident;
    $product['name'] = 'FIXME';

    $products[] = $product;
}

//make the first unlocked automatically
$products[0]['promoted'] = true;

echo json_encode(
    ['products' => $products],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) . "\n";
?>
