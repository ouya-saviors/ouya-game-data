#!/usr/bin/env php
<?php
/**
 * Walk through json game files and remove all media images
 * that link to cloudfront or filepicker.
 */
array_shift($argv);

$files = $argv;
if (count($files) == 0) {
    echo "Pass some game json files\n";
    exit(1);
}

function isMissingUrl($url)
{
    return strpos($url, '.cloudfront.net/') !== false
        || strpos($url, 'www.filepicker.io/api') !== false;
}

foreach ($files as $file) {
    $game = json_decode(file_get_contents($file));
    echo $game->packageName . "\n";

    $unset = [];
    foreach ($game->media as $key => $medium) {
        if (isMissingUrl($medium->url)) {
            $unset[] = $key;
        }
    }
    if (count($unset) == 0) {
        continue;
    }
    foreach ($unset as $key) {
        unset($game->media[$key]);
    }
    $game->media = array_values($game->media);//re-key
    file_put_contents(
        $file,
        json_encode($game, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
    );
    echo " written\n";
}
?>
