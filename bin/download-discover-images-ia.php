#!/usr/bin/env php
<?php
$files = glob(__DIR__ . '/../old-data/ia-data/*.json');
foreach ($files as $path) {
    $file = basename($path);
    $parts = explode('_', $file);
    if (count($parts) != 3) {
        continue;
    }
    if ($parts[0] != 'ouya') {
        continue;
    }
    $package = $parts[1];
    echo $package . "\n";

    $data = json_decode(file_get_contents($path));
    foreach ($data->files as $dfile) {
        if ($dfile->name == '00ICON.png') {
            $url = 'http://' . $data->d1
                 . $data->dir . '/' . $dfile->name;
            $dlfile = '/media/cweiske/videos/ouya-backup/game-images/' . strtolower($package) . '/discover';
            if (file_exists($dlfile)) {
                echo " S\n";
                break;
            }
            echo ' ' . $url . "\n";
            $cmd = 'curl -s ' . escapeshellarg($url)
                 . ' -o ' . escapeshellarg($dlfile);
            passthru($cmd);
            break;
        }
    }
    die();
}
?>
