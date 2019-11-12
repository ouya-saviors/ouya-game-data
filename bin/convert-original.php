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

$downloadJson = file_get_contents($downloadFile);
if ($downloadJson === false || trim($downloadJson) === '') {
    error('Download file is empty');
}
$downloadData = json_decode($downloadJson);
if ($downloadData === null) {
    error('Download JSON cannot de loaded');
}


//data building
$package = basename($detailsFile, '.json');

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
            'versionCode' => $detailsData->apk->versionCode,
            'uuid'        => $appsData->app->latestVersion,
            'date'        => $appsData->app->publishedAt,
            'url'         => $downloadData->app->downloadLink,
            'size'        => (int) $downloadData->app->fileSize,
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
        'supportEmail' => $appsData->app->supportEmailAddress,
        'supportPhone' => $appsData->app->supportPhone,
        'founder'      => $appsData->app->founder,
    ],

    'contentRating'    => $appsData->app->contentRating,
    'website'          => $appsData->app->website,
    'firstPublishedAt' => $appsData->app->firstPublishedAt,
    'inAppPurchases'   => false,//FIXME: we would need discover data here
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
    //var_dump($iaPkg, $gameData);die();
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
