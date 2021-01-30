#!/usr/bin/env php
<?php
/**
 * Create a game data JSON file from an .apk
 *
 * Usage:
 * $ create-from-apk.php <apk> <urlBase?> <screenshots?>
 *
 * @author Christian Weiske <cweiske+ouya@cweiske.de>
 */
require_once __DIR__ . '/functions.php';

if (!function_exists('uuid_create')) {
    fwrite(STDERR, "Error: uuid PHP extension missing\n");
    exit(2);
}
if ($argc < 2) {
    fwrite(STDERR, "Error: apk file missing\n");
    fwrite(STDERR, "Usage: create-from-apk.php <apk> [urlBasePath] [images]\n");
    exit(1);
}
array_shift($argv);
$apk = array_shift($argv);
if (!file_exists($apk)) {
    fwrite(STDERR, "Error: apk file does not exist\n");
    exit(1);
}

$urlBasePath = 'FIXME/';
if (count($argv)) {
    $urlBasePath = array_shift($argv);
}

$data = [
    'packageName' => 'FIXME',
    'title'       => 'FIXME',
    'description' => 'FIXME',
    'players'     => [
        1
    ],
    'genres'      => [
        'FIXME:'
        . implode(
            ',',
            json_decode(
                file_get_contents(__DIR__ . '/../ouya-game.schema.json')
            )->properties->genres->items->enum
        ),
    ],
    'releases'    => [
        [
            'name'        => 'FIXME',
            'versionCode' => 'FIXME',
            'uuid'        => uuid_create(),
            'date'        => gmdate('Y-m-d\TH:i:s\Z', filectime($apk)),
            'latest'      => true,
            'url'         => $urlBasePath . basename($apk),
            'size'        => filesize($apk),
            'md5sum'      => md5_file($apk),
        ]
    ],
    'discover'            => 'FIXME',
    'media'               => [
    ],
    'developer'           => [
        'uuid'    => '11111111' . substr(uuid_create(), 8),
        'name'    => 'FIXME',
        'founder' => false,
    ],
    'contentRating'    => 'FIXME:Everyone,9+,12+,17+',
    'website'          => 'FIXME',
    'firstPublishedAt' => gmdate('Y-m-d\TH:i:s\Z'),
    'inAppPurchases'   => false,
    'overview'         => null,
    'premium'          => false,
    'rating'           => [
        'likeCount' => 0,
        'average'   => 0,
        'count'     => 0,
    ],
];

$badging = loadBadgingInfo($apk);
if (isset($badging['application-label'])) {
    $data['title'] = trim($badging['application-label'], "'");
}

$packageName = null;
if (isset($badging['packageName'])) {
    $packageName = $badging['packageName'];
    $data['packageName'] = $badging['packageName'];
}
if (isset($badging['packageVersionCode'])) {
    $data['releases'][0]['versionCode'] = (int) $badging['packageVersionCode'];
}
if (isset($badging['packageVersionName'])) {
    $data['releases'][0]['name'] = $badging['packageVersionName'];
}

//discover icon
// can be extracted with ./bin/icon-from-apk.php
if ($packageName) {
    $iconBase = dirname($apk) . '/' . $packageName;
    if (file_exists($iconBase . '.jpg')) {
        $data['discover'] = $urlBasePath . $packageName . '.jpg';
    } else if (file_exists($iconBase . '.png')) {
        $data['discover'] = $urlBasePath . $packageName . '.png';
    }
}

//screenshots as additional arguments to this script
if ($argv > 2) {
    foreach ($argv as $image) {
        $data['media'][] = [
            'type' => 'image',
            'url'  => $urlBasePath . basename($image),
        ];
    }
}

$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    . "\n";

if ($packageName) {
    $jsonfile = realpath(__DIR__ . '/../new') . '/' . $packageName . '.json';
    if (!file_exists($jsonfile)) {
        file_put_contents($jsonfile, $json);
        echo 'Wrote file ' . $jsonfile . "\n";
    } else {
        echo $json;
        fwrite(STDERR, 'File exists already: ' . $jsonfile . "\n");
        exit(1);
    }
} else {
    echo $json;
}
?>
