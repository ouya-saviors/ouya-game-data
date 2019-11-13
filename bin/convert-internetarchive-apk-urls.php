#!/usr/bin/env php
<?php
/**
 * Take OUYA game data files and replace the broken download links
 * with archive.org links
 */
foreach (glob(__DIR__ . '/../games/*.json') as $gameFile) {
    $gameData = json_decode(file_get_contents($gameFile));
    echo $gameFile . "\n";
    $changed = false;
    foreach ($gameData->releases as $release) {
        if (parse_url($release->url, PHP_URL_HOST) != 'devs-ouya-tv-prod.s3.amazonaws.com') {
            echo " release url ok\n";
            continue;
        }

        $releaseNameFile = str_replace(
            [' ', '(', ')', '!'],
            ['_', '', '', ''],
            $release->name
        );
        $iaJsonFile = __DIR__ . '/../old-data/ia-data/'
            . 'ouya_' . $gameData->packageName . '_' . $releaseNameFile . '.json';
        if (!file_exists($iaJsonFile)) {
            echo " IA file not found: $iaJsonFile\n";
            continue;
        }
        $iaData = json_decode(file_get_contents($iaJsonFile));

        $url = null;
        foreach ($iaData->files as $iaFile) {
            if ($iaFile->format == 'Android Package Archive') {
                $iaSlug = basename($iaJsonFile, '.json');
                $url = 'https://archive.org/download/' . $iaSlug . '/' . rawurlencode($iaFile->name);
            }
        }
        if ($url === null) {
            echo " No apk found!\n";
            continue;
        }

        $release->url = $url;
        $changed = true;
    }

    if ($changed) {
        echo " Saving game data\n";
        file_put_contents($gameFile, json_encode($gameData, JSON_PRETTY_PRINT) . "\n");
    }
}
