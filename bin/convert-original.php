#!/usr/bin/env php
<?php
/**
 * Convert OUYA storefront data to a game json file
 *
 * @author Christian Weiske <cweiske@cweiske.de>
 */
if (isset($argv[1]) && in_array($argv[1], ['-h', '--help'])) {
    error(
        'Usage: convert-original.php ouya-game-details.json ouya-game-download.json'
    );
}

//cli arguments
if (!isset($argv[1])) {
    error('details json file parameter missing');
}
$detailsFile = $argv[1];

if (!isset($argv[2])) {
    error('download json file parameter missing');
}
$downloadFile = $argv[2];


//file loading
$detailsJson = file_get_contents($detailsFile);
if ($detailsJson === false || trim($detailsJson) === '') {
    error('Details file is empty');
}
$detailsData = json_decode($detailsJson);
if ($detailsData === null) {
    error('Details JSON cannot de loaded');
}

$downloadJson = file_get_contents($downloadFile);
if ($downloadJson === false || trim($downloadJson) === '') {
    error('Download file is empty');
}
$downloadData = json_decode($downloadJson);
if ($downloadData === null) {
    error('Download JSON cannot de loaded');
}

$package = basename($detailsFile, '.json');
//data building
$gameData = [
    'uuid'        => $detailsData->app->uuid,
    'package'     => $package,
    'title'       => $detailsData->app->title,
    'description' => $detailsData->app->description,
    'players'     => $detailsData->app->gamerNumbers,
    'genres'      => $detailsData->app->genres,
    
    'releases' => [
        [
            'name'       => $detailsData->app->versionNumber,
            'uuid'       => $detailsData->app->latestVersion,
            'date'       => $detailsData->app->publishedAt,
            'url'        => $downloadData->app->downloadLink,
            'size'       => $downloadData->app->fileSize,
            'md5sum'     => $detailsData->app->md5sum,
            'publicSize' => $detailsData->app->publicSize,
            'nativeSize' => $detailsData->app->nativeSize,
        ]
    ],

    'media' => [
        'discover'    => 'http://ouya.cweiske.de/game-images/' . strtolower($package) . '/discover',
        'large'       => newImage($detailsData->app->mainImageFullUrl),
        'video'       => $detailsData->app->videoUrl,
        'screenshots' => newImages($detailsData->app->filepickerScreenshots),
    ],

    'developer' => [
        'name'         => $detailsData->app->developer,
        'supportEmail' => $detailsData->app->supportEmailAddress,
        'supportPhone' => $detailsData->app->supportPhone,
        'founder'      => $detailsData->app->founder,
    ],

    'contentRating'    => $detailsData->app->contentRating,
    'website'          => $detailsData->app->website,
    'firstPublishedAt' => $detailsData->app->firstPublishedAt,
    'inAppPurchases'   => false,//FIXME: we would need discover data here
    'overview'         => $detailsData->app->overview,
    'premium'          => $detailsData->app->premium,

    'rating' => [
        'likeCount' => $detailsData->app->likeCount,
        'average'   => $detailsData->app->ratingAverage,
        'count'     => $detailsData->app->ratingCount,
    ],
];

if (isset($detailsData->app->promotedProduct)) {
    $gameData['products'][] = [
        'promoted'      => true,
        'identifier'    => $detailsData->app->promotedProduct->identifier,
        'name'          => $detailsData->app->promotedProduct->name,
        'description'   => $detailsData->app->promotedProduct->description,
        'localPrice'    => $detailsData->app->promotedProduct->localPrice,
        'originalPrice' => $detailsData->app->promotedProduct->originalPrice,
        'currency'      => $detailsData->app->promotedProduct->currency,
    ];
}

$iaDataFiles = glob(__DIR__ . '/../old-data/ia-data/ouya_' . $package . '_*.json');
if (count($iaDataFiles)) {
    $iaPkg = [];
    foreach ($iaDataFiles as $iaJsonFile) {
        $iaData = json_decode(file_get_contents($iaJsonFile));
        foreach ($iaData->files as $iaFile) {
            if ($iaFile->format == 'Android Package Archive') {
                $iaSlug = basename($iaJsonFile, '.json');
                $iaFile->url = 'https://archive.org/download/' . $iaSlug . '/' . $iaFile->name;
                $versionName = explode('_', $iaSlug)[2];
                $iaPkg[$versionName] = $iaFile;
            }
        }
    }

    //update existing release
    $exVersion = $gameData['releases'][0]['name'];
    if (isset($iaPkg[$exVersion])) {
        $gameData['releases'][0]['url'] = $iaPkg[$exVersion]->url;
        unset($iaPkg[$exVersion]);
    }
    foreach ($iaPkg as $iaVersion => $iaApk) {
        $gameData['releases'][] = [
            'name'       => $iaVersion,
            'uuid'       => null,
            'date'       => '2010-01-01T00:00:00Z',//gmdate('c', $iaApk->mtime),
            'url'        => $iaApk->url,
            'size'       => $iaApk->size,
            'md5sum'     => $iaApk->md5,
            'publicSize' => 0,//FIXME
            'nativeSize' => 0,//FIXME
        ];
    }
    var_dump($iaPkg, $gameData);die();
}


echo json_encode($gameData, JSON_PRETTY_PRINT) . "\n";

function newImage($imageUrl)
{
    return $imageUrl;
    static $mapping;
    if ($mapping === null) {
        $hdl = fopen(__DIR__ . '/../old-data/map-game-images.csv', 'r');
        if (!$hdl) {
            error('Cannot load image url map file');
        }
        $mapping = [];
        while ($data = fgetcsv($hdl, 4096, ',')) {
            if (count($data) == 2) {
                $mapping[$data[0]] = $data[1];
            }
        }
    }
    return $mapping[$imageUrl] ?? $imageUrl;
}

function newImages($urls)
{
    $new = [];
    foreach ($urls as $url) {
        $new[] = newImage($url);
    }
    return $new;
}

function error($msg)
{
    file_put_contents('php://stderr', $msg . "\n");
    exit(1);
}
?>
