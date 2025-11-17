#!/usr/bin/env php
<?php
/**
 * Create a gamestick launcher JSON file from the game json file
 *
 * Usage:
 * $ create-launcher.php <game.json>
 *
 * @author Christian Weiske <cweiske+ouya@cweiske.de>
 */
if ($argc < 2) {
    fwrite(STDERR, "Error: json game file missing\n");
    fwrite(STDERR, "Usage: create-launcher.php <game.json>\n");
    exit(1);
}
array_shift($argv);

$gameFile = array_shift($argv);
if (!file_exists($gameFile)) {
    fwrite(STDERR, "Error: game JSON file does not exist\n");
    exit(1);
}

$launcherFile = str_replace('.json', '.launcher.json', $gameFile);
$launcherData = json_decode(file_get_contents($gameFile));

$gamePackageName = $launcherData->packageName;

$launcherData->packageName .= '.launcher';
$launcherData->title       .= ' (Launcher)';

$launcherData->genres[] = 'Launcher';
sort($launcherData->genres);

$launcherData->releases = [];
$launcherData->developer = [
    'uuid' => '11111111-2590-4f1f-b4ad-7a3ea00edfd6',
    'name' => 'Andiweli',
];

$launcherData->relationships = [
    'original' => $gamePackageName,
];

$json = json_encode($launcherData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    . "\n";
file_put_contents($launcherFile, $json);
echo 'Wrote launcher file: ' . $launcherFile . "\n";
