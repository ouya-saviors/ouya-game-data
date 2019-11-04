#!/usr/bin/env php
<?php
chdir(__DIR__ . '/../old-data/');
$gamefiles = glob('devs.ouya.tv/api/v1/apps/*.json');
//$gamefiles = glob('devs.ouya.tv/api/v1/apps/net.froem*.json');

foreach ($gamefiles as $file) {
    echo "Processing $file\n";
    $data = json_decode(file_get_contents($file));
    if ($data === null) {
        echo "error opening " . $file . "\n";
        exit(1);
    }
    $package = basename($file, '.json');

    $iaImages = loadIaImages($package);

    $pos = 0;
    foreach ($data->app->filepickerScreenshots as $imageUrl) {
        $pos++;
        mapIaImage($iaImages, $imageUrl);
    }

    mapIaImage($iaImages, $data->app->mainImageFullUrl);
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

function findIaImage($iaImages, $imageUrl)
{
    // https://d3e4aumcqn8cw3.cloudfront.net/api/file/tC4RIGJLQvG2uG1av9jN
    $imageName = basename($imageUrl);
    if (isset($iaImages[$imageName . '.png'])) {
        return $iaImages[$imageName . '.png'];
    }
    if (isset($iaImages[$imageName . '.jpg'])) {
        return $iaImages[$imageName . '.jpg'];
    }
    return $imageUrl;
}

function loadIaImages($package)
{
    $images = [];
    $iaDataFiles = glob('ia-data/ouya_' . $package . '_*.json');
    foreach ($iaDataFiles as $iaJsonFile) {
        $iaSlug = basename($iaJsonFile, '.json');
        $data = json_decode(file_get_contents($iaJsonFile));
        foreach ($data->files as $file) {
            if ($file->source != 'original') {
                continue;
            }
            if ($file->format != 'JPEG' && $file->format != 'PNG') {
                continue;
            }

            $images[$file->name] = 'https://archive.org/download/' . $iaSlug . '/' . $file->name;
        }
    }
    return $images;
}
?>
