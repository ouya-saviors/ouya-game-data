#!/usr/bin/env php
<?php
/**
 * Compare apk sizes in the JSON files with the server
 *
 * @author Christian Weiske <cweiske@cweiske.de>
 */
array_shift($argv);
$files = $argv;
if (count($files) == 0) {
    fwrite(STDERR, "Pass some game json files\n");
    exit(1);
}

$errors = 0;
foreach ($files as $file) {
    $game = json_decode(file_get_contents($file));
    echo $game->packageName . "\n";
    foreach ($game->releases as $key => $release) {
        echo ' ' . $release->url . "\n";

        $opts = ['http' => ['method' => 'HEAD']];
        $headers = get_headers(
            $release->url, true, stream_context_create($opts)
        );
        $headers = array_change_key_case($headers);
        if (!isset($headers['content-length'])) {
            fwrite(STDERR, "  No content-length in HTTP response\n");
            $errors++;
            continue;
        }

        if ($headers['content-length'] != $release->size) {
            fwrite(
                STDERR,
                  "  File size different:\n"
                . "   JSON: " . $release->size . "\n"
                . "   HTTP: " . $headers['content-length'] . "\n"
            );
            $errors++;
            continue;
        }
    }
}

if ($errors) {
    fwrite(STDERR, "There were $errors errors\n");
    exit(1);
}
?>
