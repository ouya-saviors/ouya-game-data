#!/usr/bin/env php
<?php
chdir(__DIR__ . '/../old-data/');

// see ouya-games.rst to update that one
$packages = file('ia-packages');

foreach ($packages as $package) {
    $package = trim($package);
    $url = 'https://archive.org/metadata/' . $package;
    $cmd = 'curl -s ' . escapeshellarg($url)
         . ' | jq . > ia-data/' . $package . '.json';
    passthru($cmd);
}
?>
