#!/usr/bin/env php
<?php
/**
 * Convert OUYA storefront data to a game json file
 *
 * @author Christian Weiske <cweiske@cweiske.de>
 */
if (isset($argv[1]) && in_array($argv[1], ['-h', '--help'])) {
    error(
        'Usage: convert-original.php game-details.json game-apps.json game-apps-download.json'
    );
}

//cli arguments
if (!isset($argv[1])) {
    error('details json file parameter missing (api/v1/details?app=...');
}
$detailsFile = $argv[1];

if (!isset($argv[2])) {
    error('apps json file parameter missing (api/v1/apps/xxx');
}
$appsFile = $argv[2];

if (!isset($argv[3])) {
    error('apps download json file parameter missing (api/v1/apps/xxx/download');
}
$downloadFile = $argv[3];


//file loading
$detailsJson = file_get_contents($detailsFile);
if ($detailsJson === false || trim($detailsJson) === '') {
    error('Details file is empty');
}
$detailsData = json_decode($detailsJson);
if ($detailsData === null) {
    error('Details JSON cannot de loaded');
}

$appsJson = file_get_contents($appsFile);
if ($appsJson === false || trim($appsJson) === '') {
    error('Apps file is empty');
}
$appsData = json_decode($appsJson);
if ($appsData === null) {
    error('App JSON cannot de loaded');
}

$package = basename($detailsFile, '.json');

if (file_exists($downloadFile)) {
    $downloadJson = file_get_contents($downloadFile);
    if ($downloadJson === false || trim($downloadJson) === '') {
        error('Download file is empty');
    }
    $downloadData = json_decode($downloadJson);
    if ($downloadData === null) {
        error('Download JSON cannot de loaded');
    }
    $downloadUrl = $downloadData->app->downloadLink;
} else {
    $downloadData = null;
    $downloadUrl  = null;
    //fetch download URL from internet archive files
    $version = $appsData->app->versionNumber;
    $iaJsonFile = __DIR__ . '/../old-data/ia-data/'
        . 'ouya_' . $package . '_' . $version . '.json';
    if (!file_exists($iaJsonFile)) {
        error('No download file given, and no internet archive version found');
    }
    $iaData = json_decode(file_get_contents($iaJsonFile));
    foreach ($iaData->files as $iaFile) {
        if ($iaFile->format == 'Android Package Archive') {
            $iaSlug = basename($iaJsonFile, '.json');
            $downloadUrl = 'https://archive.org/download/' . $iaSlug . '/' . $iaFile->name;
        }
    }
    if ($downloadUrl === null) {
        error('No .apk download URL found in internet archive json file');
    }
}


//data building

$developerUuid = null;
if (isset($detailsData->developer->url)) {
    parse_str(parse_url($detailsData->developer->url, PHP_URL_QUERY), $devParams);
    $developerUuid = $devParams['developer'];
}

$gameData = [
    'packageName' => $package,
    'title'       => $appsData->app->title,
    'description' => $appsData->app->description,
    'players'     => $appsData->app->gamerNumbers,
    'genres'      => $appsData->app->genres,
    
    'releases' => [
        [
            'name'        => $appsData->app->versionNumber,
            'versionCode' => (int) $detailsData->apk->versionCode,
            'uuid'        => $appsData->app->latestVersion,
            'date'        => $appsData->app->publishedAt,
            'url'         => $downloadUrl,
            'size'        => isset($downloadData->app->fileSize)
                ? intval($downloadData->app->fileSize)
                : intval($appsData->app->apkFileSize),
            'md5sum'      => $appsData->app->md5sum,
            'publicSize'  => $appsData->app->publicSize,
            'nativeSize'  => $appsData->app->nativeSize,
        ]
    ],

    'media' => [
        'discover'    => 'http://ouya.cweiske.de/game-images/' . strtolower($package) . '/discover',
        'large'       => $appsData->app->mainImageFullUrl,
        'video'       => $appsData->app->videoUrl,
        'screenshots' => $appsData->app->filepickerScreenshots,
        'details'     => details($detailsData->mediaTiles),
    ],

    'developer' => [
        'uuid'         => $developerUuid,
        'name'         => $appsData->app->developer,
        'supportEmail' => $appsData->app->supportEmailAddress != ''
            ? $appsData->app->supportEmailAddress : null,
        'supportPhone' => $appsData->app->supportPhone,
        'founder'      => $appsData->app->founder,
    ],

    'contentRating'    => $appsData->app->contentRating,
    'website'          => $appsData->app->website,
    'firstPublishedAt' => $appsData->app->firstPublishedAt,
    'inAppPurchases'   => $detailsData->inAppPurchases,
    'overview'         => $appsData->app->overview,
    'premium'          => $appsData->app->premium,

    'rating' => [
        'likeCount' => $appsData->app->likeCount,
        'average'   => $appsData->app->ratingAverage,
        'count'     => $appsData->app->ratingCount,
    ],
];

if (isset($appsData->app->promotedProduct)) {
    $gameData['products'][] = [
        'promoted'      => true,
        'identifier'    => $appsData->app->promotedProduct->identifier,
        'name'          => $appsData->app->promotedProduct->name,
        'description'   => $appsData->app->promotedProduct->description,
        'localPrice'    => $appsData->app->promotedProduct->localPrice,
        'originalPrice' => $appsData->app->promotedProduct->originalPrice,
        'currency'      => $appsData->app->promotedProduct->currency,
    ];
}

echo json_encode($gameData, JSON_PRETTY_PRINT) . "\n";

function details($mediaTiles)
{
    $details = [];
    foreach ($mediaTiles as $tile) {
        if ($tile->type == 'video') {
            $details[] = [
                'type' => 'video',
                'url'  => $tile->url,
            ];
        } else {
            $details[] = [
                'type'  => 'image',
                'url'   => $tile->urls->full,
                'thumb' => $tile->urls->thumbnail,
            ];
        }
    }
    return $details;
}

function error($msg)
{
    file_put_contents('php://stderr', $msg . "\n");
    exit(1);
}
?>
