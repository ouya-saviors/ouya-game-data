<?php
/**
 * Replace vimeo.com links with .mp4 files from the internet archive
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

foreach ($files as $file) {
    $game = json_decode(file_get_contents($file));
    echo $game->packageName . "\n";

    $iaFiles = glob($iaJsonDir . '/ouya_' . $game->packageName . '_*.json');
    if (!count($iaFiles)) {
        echo "No internet archive files for " . $game->packageName . "\n";
        continue;
    }

    $modified = false;
    $iamp4s = [];
    foreach ($iaFiles as $iaJsonFile) {
        $iaData  = json_decode(file_get_contents($iaJsonFile));
        $parts   = explode('_', basename($iaJsonFile, '.json'));

        $url = null;
        foreach ($iaData->files as $iaFile) {
            if ($iaFile->format != 'MPEG4' || $iaFile->source != 'original') {
                continue;
            }

            $iaSlug = basename($iaJsonFile, '.json');
            $url = 'https://archive.org/download/' . $iaSlug . '/'
                 . rawurlencode($iaFile->name);

            if (!preg_match('# - ([0-9]+)\\.mp4#', $iaFile->name, $matches)) {
                echo " Cannot find vimeo ID in name " . $iaFile->name . "\n";
                continue;
            }
            $vimeoId = $matches[1];

            $iamp4s[$vimeoId] = $url;
        }

        if (count($iamp4s) == 0) {
            echo " no .mp4 in $iaJsonFile\n";
            continue;
        }
    }

    echo " found " . count($iamp4s) . " videos in IA!\n";

    foreach ($game->media as $medium) {
        if ($medium->type != 'video') {
            continue;
        }
        if (substr($medium->url, 0, 18) != 'https://vimeo.com/') {
            continue;
        }

        $vimeoId = substr($medium->url, 18);
        if (isset($iamp4s[$vimeoId])) {
            echo " Replacing " . $medium->url . " with " . $iamp4s[$vimeoId] . "\n";
            $modified = true;
            $medium->url = $iamp4s[$vimeoId];
        }
    }

    if (!$modified) {
        continue;
    }

    file_put_contents(
        $file,
        json_encode($game, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
    );
    echo " written\n";
}
?>
