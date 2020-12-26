#!/usr/bin/env php
<?php
/**
 * Rename an .apk file to "$packageName-$versionCode.apk"
 *
 * @author Christian Weiske <cweiske+ouya@cweiske.de>
 */
require_once __DIR__ . '/functions.php';

if ($argc < 2) {
    fwrite(STDERR, "Error: apk file missing\n");
    exit(1);
}
$apk = $argv[1];
if (!file_exists($apk)) {
    fwrite(STDERR, "Error: apk file does not exist\n");
    exit(1);
}

$badging = loadBadgingInfo($apk);

$correctFileName = $badging['packageName']
    . '-' . $badging['packageVersionCode']
    . '.apk';

if (basename($apk) == $correctFileName) {
    echo "Filename is already correct\n";
    exit(0);
}


$correctFilePath = dirname($apk) . '/' . $correctFileName;
if (file_exists($correctFilePath)) {
    fwrite(STDERR, "Target file exists already: $correctFilePath\n");
    exit(10);
}

echo 'Renaming "' . basename($apk) . '" to "' . $correctFileName . "\"\n";
rename($apk, $correctFilePath);
