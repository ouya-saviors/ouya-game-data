<?php
/**
 * Remove releases with "FIXME" download URLs
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

    $unset = [];
    foreach ($game->releases as $key => $release) {
        if ($release->url !== 'FIXME') {
            echo " already ok for version " . $release->name . "\n";
            continue;
        }

        echo " URL is FIXME for " . $release->name . "\n";
        $unset[] = $key;
    }

    if (count($unset)) {
        if (count($unset) == count($game->releases)) {
            echo " NO releases anymore - skipping!\n";
            continue;
        }
        foreach ($unset as $key) {
            unset($game->releases[$key]);
        }
        $game->releases = array_values($game->releases);//re-key
        echo " updating file\n";
        file_put_contents(
            $file,
            json_encode($game, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
        );        
    }
}
?>
