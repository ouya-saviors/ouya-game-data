#!/usr/bin/env php
<?php
/**
 * Find ouya game images missing in the internet archive
 */
chdir(__DIR__ . '/../old-data/');

$iaDataFiles = glob('ia-data/ouya_*_*.json');
foreach ($iaDataFiles as $iaJsonFile) {
    $iaPackage = basename($iaJsonFile, '.json');
    $parts = explode('_', $iaPackage);
    if (count($parts) != 3) {
        continue;
    }
    $package = $parts[1];
    fwrite(STDERR, $package . "\n");

    $iaImages = loadIaImages($iaJsonFile);

    $ouyaDetailsFile = 'devs.ouya.tv/api/v1/apps/' . $package . '.json';
    if (!file_exists($ouyaDetailsFile)) {
        fwrite(STDERR, " ERR: ouya file missing $ouyaDetailsFile\n");
        continue;
    }
    $details = json_decode(file_get_contents($ouyaDetailsFile));
    if ($details === null) {
        fwrite(STDERR, "error opening " . $ouyaDetailsFile . "\n");
        exit(1);
    }

    $pos = 0;
    foreach ($details->app->filepickerScreenshots as $imageUrl) {
        $pos++;
        findIaImage($iaImages, $imageUrl, $iaPackage);
    }

    findIaImage($iaImages, $details->app->mainImageFullUrl, $iaPackage);
}



function findIaImage($iaImages, $imageUrl, $iaPackage)
{
    // https://d3e4aumcqn8cw3.cloudfront.net/api/file/tC4RIGJLQvG2uG1av9jN
    $imageName = basename($imageUrl);
    if (isset($iaImages[$imageName . '.png'])) {
        return;
    }
    if (isset($iaImages[$imageName . '.jpg'])) {
        return;
    }

    $cwUrl = str_replace('https://', 'http://tmp.cweiske.de/', $imageUrl);

    $localPath = str_replace('https://', '/media/cweiske/videos/ouya-backup/', $imageUrl);
    if (!file_exists($localPath)) {
        fwrite(STDERR, " local file not found: $localPath\n");
    }

    echo $iaPackage . ',' . $cwUrl . ',' . $imageUrl . "\n";
}

function loadIaImages($iaJsonFile)
{
    $iaSlug = basename($iaJsonFile, '.json');
    $data = json_decode(file_get_contents($iaJsonFile));
    $images = [];
    foreach ($data->files as $file) {
        if ($file->source != 'original') {
            continue;
        }
        if ($file->format != 'JPEG' && $file->format != 'PNG') {
            continue;
        }

        $images[$file->name] = 'https://archive.org/download/' . $iaSlug . '/' . $file->name;
    }
    return $images;
}

exit(0);

$gamefiles = glob('devs.ouya.tv/api/v1/apps/*.json');
//$gamefiles = glob('devs.ouya.tv/api/v1/apps/net.froem*.json');

foreach ($gamefiles as $file) {
    echo "Processing $file\n";
    //die();
}

function mapIaImage($iaImages, $imageUrl)
{
    $newUrl = findIaImage($iaImages, $imageUrl);
    if ($newUrl !== $imageUrl) {
        echo " ok\n";
        file_put_contents(
            'map-game-images.ia.csv',
            $imageUrl . ',' . $newUrl . "\n",
            FILE_APPEND
        );
        return;
    }
    //not in internet archive
    echo " Missing in IA: $imageUrl\n";
}
?>
