<?php
/**
 * Set the "latest" property correctly if there are multiple releases.
 */
array_shift($argv);
$files = $argv;
if (count($files) == 0) {
    echo "Pass some game json files\n";
    exit(1);
}

foreach ($files as $file) {
    $game = json_decode(file_get_contents($file));
    echo $game->packageName . "\n";

    $modified = false;
    $highestCode = 0;
    $highestKey  = null;
    foreach ($game->releases as $key => $release) {
        if (version_compare($release->versionCode, $highestCode, '>')) {
            $highestCode = $release->versionCode;
            $highestKey  = $key;
        }
    }
    foreach ($game->releases as $key => $release) {
        $highest = $highestKey == $key;
        if ($release->latest == $highest) {
            continue;
        }
        $release->latest = $highest;
        $modified = true;
    }

    if ($modified) {
        echo " updating file\n";
        file_put_contents(
            $file,
            json_encode($game, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
        );        
    }
}
?>
