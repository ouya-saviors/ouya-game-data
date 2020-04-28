#!/usr/bin/env php
<?php
/**
 * Create a game data JSON file from an .apk
 *
 * @author Christian Weiske <cweiske+ouya@cweiske.de>
 */
if (!function_exists('uuid_create')) {
    fwrite(STDERR, "Error: uuid PHP extension missing\n");
    exit(2);
}
if ($argc < 2) {
    fwrite(STDERR, "Error: apk file missing\n");
    exit(1);
}
$apk = $argv[1];
if (!file_exists($apk)) {
    fwrite(STDERR, "Error: apk file does not exist\n");
    exit(1);
}

$data = [
    'packageName' => 'FIXME',
    'title'       => 'FIXME',
    'description' => 'FIXME',
    'players'     => [
        1
    ],
    'genres'      => [
        'FIXME:'
        . implode(
            ',',
            json_decode(
                file_get_contents(__DIR__ . '/../ouya-game.schema.json')
            )->properties->genres->items->enum
        ),
    ],
    'releases'    => [
        [
            'name'        => 'FIXME',
            'versionCode' => 'FIXME',
            'uuid'        => uuid_create(),
            'date'        => gmdate('c', filectime($apk)),
            'latest'      => true,
            'url'         => 'FIXME',
            'size'        => filesize($apk),
            'md5sum'      => md5_file($apk),
        ]
    ],
    'discover'            => 'FIXME',
    'media'               => [
    ],
    'developer'           => [
        'uuid'    => '11111111' . substr(uuid_create(), 8),
        'name'    => 'FIXME',
        'founder' => false,
    ],
    'contentRating'    => 'FIXME:Everyone,9+,12+,17+',
    'website'          => 'FIXME',
    'firstPublishedAt' => gmdate('c'),
    'inAppPurchases'   => false,
    'premium'          => false,
    'overview'         => null,
    'rating'           => [
        'likeCount' => 0,
        'average'   => 0,
        'count'     => 0,
    ],
];

exec('aapt dump badging ' . escapeshellarg($apk), $lines, $exitcode);
if ($exitcode != 0) {
    fwrite(STDERR, "error running aapt\n");
    exit(2);
}
foreach ($lines as $line) {
    if (strpos($line, ':') === false) {
        continue;
    }
    list($key, $val) = explode(':', $line, 2);
    $badging[$key] = trim($val);
}

if (isset($badging['application-label'])) {
    $data['title'] = trim($badging['application-label'], "'");
}
if (isset($badging['package'])) {
    preg_match_all("#([^ ]+)='([^']*)'#", $badging['package'], $matches);
    $package = array_combine($matches[1], $matches[2]);
    if (isset($package['name'])) {
        $data['packageName'] = $package['name'];
    }
    if (isset($package['versionCode'])) {
        $data['releases'][0]['versionCode'] = (int) $package['versionCode'];
    }
    if (isset($package['versionName'])) {
        $data['releases'][0]['name'] = $package['versionName'];
    }
}
//var_dump($badging);die();


echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    . "\n";
?>
