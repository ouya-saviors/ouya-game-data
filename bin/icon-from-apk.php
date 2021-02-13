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
$iconBase = dirname($apk) . '/' . $badging['packageName'];
if (file_exists($iconBase . '.png')) {
    echo "Icon exists already\n";
    exit(0);
}
if (file_exists($iconBase . '.jpg')) {
    echo "Icon exists already\n";
    exit(0);
}

$zip = new ZipArchive();
$res = $zip->open($apk);
if ($res !== true) {
    fwrite(STDERR, 'Error opening zip file, code #' . $res . "\n");
    exit(10);
}

$pos = $zip->locateName('ouya_icon.png', ZipArchive::FL_NODIR);
if ($pos === false) {
    fwrite(STDERR, "No ouya_icon.png found\n");
    exit(11);
}

$iconPath = $iconBase . '.png';
file_put_contents($iconPath, $zip->getFromIndex($pos));
echo "Icon written to $iconPath\n";
