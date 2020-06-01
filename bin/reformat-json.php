#!/usr/bin/env php
<?php
/**
 * Fix formatting of the given JSON file
 */
if (!isset($argv[1])) {
    error('Pass a path to a JSON file');
}
$jsonFile = $argv[1];
if (!is_file($jsonFile)) {
    error('Given path is not a file: ' . $jsonFile);
}

$json = json_decode(file_get_contents($jsonFile));
if ($json === null) {
    error('Could not decode JSON file. Maybe a syntax error?');
}

file_put_contents(
    $jsonFile,
    json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
);

function error($msg)
{
    fwrite(STDERR, $msg . "\n");
    exit(1);
}
