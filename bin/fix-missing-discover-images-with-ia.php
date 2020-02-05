#!/usr/bin/env php
<?php
/**
 * Walk through json game files and replace discover images
 * that link to cloudfront or filepicker with the internet archive icon00.png
 */
array_shift($argv);
$iaJsonDir = array_shift($argv);
if ($iaJsonDir === null || !is_dir($iaJsonDir)) {
    echo "First parameter must be directory with internet archive json files\n";
    exit(1);
}

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

    if (!isMissingUrl($game->discover)) {
        echo " discover image already ok\n";
        continue;
    }
    
    $iaFiles = glob($iaJsonDir . '/ouya_' . $game->packageName . '*.json');
    $last = end($iaFiles);
    if (!$last) {
        echo " ERR no IA json file\n";
        continue;
    }
    $iaData = json_decode(file_get_contents($last));
    $found = false;
    foreach ($iaData->files as $iaFile) {
        if ($iaFile->name != '00ICON.png') {
            continue;
        }
        $iaUrl = 'https://archive.org/download/'
            . basename($last, '.json')
            . '/00ICON.png';
        $localDir = 'missing-images/' . $game->packageName;
        $localPath = $localDir . '/discover';
        if (!is_dir($localDir)) {
            mkdir($localDir, 0777, true);
        }
        $found = true;
        if (!file_exists($localPath)) {
            echo ' Downloading ' . $iaUrl . " \n";
            file_put_contents(
                $localPath,
                file_get_contents($iaUrl)
            );
            break;
        }
    }

    if (!$found) {
        echo " ERROR: no icon found\n";
        continue;
    }

    $game->discover = 'http://ouya.cweiske.de/game-images/'
        . $game->packageName . '/discover';
    file_put_contents(
        $file,
        json_encode($game, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
    );
    echo " written\n";
}
?>
