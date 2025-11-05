#!/usr/bin/env php
<?php
/**
 * Convert gamestick game data json into an OUYA game data json file
 *
 * Usage:
 * $ create-from-gamestick.php gamestick.json
 *
 * @author Christian Weiske <cweiske+ouya@cweiske.de>
 */
$discoverUrl = 'http://ouya.cweiske.de/apks/gamestick/%s/discover';

if ($argc < 2) {
    fwrite(STDERR, "Error: json file argument missing\n");
    fwrite(STDERR, "Usage: create-from-gamestick.php <gamestick.json>\n");
    exit(1);
}

array_shift($argv);
$gsFile = array_shift($argv);
if (!file_exists($gsFile)) {
    fwrite(STDERR, "Error: gamestick json file does not exist\n");
    exit(1);
}

$gsGame = json_decode(file_get_contents($gsFile));
$ouyaGame = (object) [
    'packageName' => $gsGame->package,
    'title'       => $gsGame->name,
    'description' => $gsGame->description,
    'players'     => $gsGame->players ?? [1],
    'genres'      => [
        'PlayJam GameStick',
    ],
    'releases'    => [],
    'discover'    => sprintf($discoverUrl, $gsGame->package),
    'media'       => [],
    'developer'   => [
        'name'    => $gsGame->companyname
    ],
    'contentRating' => match($gsGame->minAge) {
        3  => 'Everyone',
        7  => '9+',
        12 => '12+',
        17 => '17+',
    },
    'website'          => $gsGame->companyurl ?? null,
    'firstPublishedAt' => min(array_column($gsGame->releases, 'date')),
    'inAppPurchases'   => count($gsGame->products ?? []) > 0,
    //'overview'         => null,
    'premium'          => true,
    //'rating' => $gsGame->,
    'relationships' => [
        'launcher' => $gsGame->package . '.launcher',
    ],
];

foreach ($gsGame->genres as $genre) {
    $ouyaGame->genres[] = match($genre) {
        'Arcade' => 'Arcade/Pinball',
        default => $genre,
    };
}

foreach ($gsGame->images->videos ?? [] as $gsVideo) {
    $ouyaGame->media[] = [
        'type'  => 'video',
        'url'   => $gsVideo->url,
        'thumb' => $gsVideo->thumb,
    ];
}
foreach ($gsGame->images->screenshots ?? [] as $url) {
    $ouyaGame->media[] = [
        'type' => 'image',
        'url'  => $url,
    ];
}

$json = json_encode($ouyaGame, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";

$jsonfile = realpath(__DIR__ . '/../gamestick') . '/' . $gsGame->package . '.json';
if (!file_exists($jsonfile)) {
    file_put_contents($jsonfile, $json);
    echo 'Wrote file ' . $jsonfile . "\n";
} else {
    echo $json;
    fwrite(STDERR, 'File exists already: ' . $jsonfile . "\n");
    exit(1);
}
