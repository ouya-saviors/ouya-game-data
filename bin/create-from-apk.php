#!/usr/bin/env php
<?php
/**
 * Create a game data JSON file from an .apk
 *
 * Usage:
 * $ create-from-apk.php <apk> <urlBase?> <screenshots?>
 *
 * @author Christian Weiske <cweiske+ouya@cweiske.de>
 */
require_once __DIR__ . '/functions.php';

if (!function_exists('uuid_create')) {
    fwrite(STDERR, "Error: uuid PHP extension missing\n");
    exit(2);
}
if ($argc < 2) {
    fwrite(STDERR, "Error: apk file missing\n");
    fwrite(STDERR, "Usage: create-from-apk.php <apk> [urlBasePath] [images|.url|.txt]\n");
    exit(1);
}
array_shift($argv);
$apk = array_shift($argv);
if (!file_exists($apk)) {
    fwrite(STDERR, "Error: apk file does not exist\n");
    exit(1);
}

$urlBasePath = 'FIXME/';
if (count($argv)) {
    $urlBasePath = array_shift($argv);
} else {
    if (strpos($apk, '/cweiske-apks/') !== false) {
        $urlBasePath = 'http://ouya.cweiske.de/apks/'
            . dirname(preg_replace('#^.+/cweiske-apks/#', '', $apk)) . '/';
    } elseif (str_contains($apk, '/gamestick-assets/')) {
        $urlBasePath = 'http://ouya.cweiske.de/apks/gamestick/'
            . dirname(preg_replace('#^.+/gamestick-assets/games/#', '', $apk)) . '/';
    } elseif (str_contains($apk, '/gamestick/archive/')) {
        $urlBasePath = 'http://gamestick.cweiske.de/archive/'
            . dirname(preg_replace('#^.+/gamestick/archive/#', '', $apk)) . '/';
    }
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
            'date'        => gmdate('Y-m-d\TH:i:s\Z', filectime($apk)),
            'url'         => $urlBasePath . rawurlencode(basename($apk)),
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
    'firstPublishedAt' => gmdate('Y-m-d\TH:i:s\Z'),
    'inAppPurchases'   => false,
    'overview'         => null,
    'premium'          => false,
    'rating'           => [
        'likeCount' => 0,
        'average'   => 0,
        'count'     => 0,
    ],
];

$badging = loadBadgingInfo($apk);
if (isset($badging['application-label'])) {
    $data['title'] = trim($badging['application-label'], "'");
}

$packageName = null;
if (isset($badging['packageName'])) {
    $packageName = $badging['packageName'];
    $data['packageName'] = $badging['packageName'];
}
if (isset($badging['packageVersionCode'])) {
    $data['releases'][0]['versionCode'] = (int) $badging['packageVersionCode'];
}
if (isset($badging['packageVersionName'])) {
    $data['releases'][0]['name'] = $badging['packageVersionName'];
}

$za = new ZipArchive();
$za->open($apk);
for ($n = 0; $n < $za->count(); $n++) {
    $name = $za->statIndex($n)['name'];
    if (!fnmatch('META-INF/*.RSA', $name)) {
        continue;
    }
    //extract certificate file
    $fpZip = $za->getStream($name);
    $tmpCertFile = tempnam(sys_get_temp_dir(), 'apk-cert-');
    $fpTmpCertFile = fopen($tmpCertFile, 'w');
    while (!feof($fpZip)) {
        fwrite($fpTmpCertFile, fread($fpZip, 2048));
    }
    fclose($fpTmpCertFile);
    //extract meta info from cert file
    exec(
        'openssl pkcs7 -inform DER -in ' . escapeshellarg($tmpCertFile) . ' -print_certs'
        . ' | openssl x509 -noout -subject -fingerprint -sha256',
        $lines
    );
    if (strtolower(substr($lines[1], 0, 19)) == 'sha256 fingerprint=') {
        $data['releases'][0]['cert_fingerprint'] = substr($lines[1], 19);
    }
    if (substr($lines[0], 0, 8) == 'subject=') {
        $data['releases'][0]['cert_subject'] = substr($lines[0], 8);
    }
    unlink($tmpCertFile);
}

//discover icon
// can be extracted with ./bin/icon-from-apk.php
if ($packageName) {
    $iconBase = dirname($apk) . '/' . $packageName;
    if (file_exists($iconBase . '.jpg')) {
        $data['discover'] = $urlBasePath . $packageName . '.jpg';
    } else if (file_exists($iconBase . '.png')) {
        $data['discover'] = $urlBasePath . $packageName . '.png';
    }
}

//screenshots as additional arguments to this script
if ($argc > 2) {
    $additionalFiles = $argv;
} else {
    $additionalFiles = glob(dirname($apk) . '/*.{txt,url,jpg,png}', GLOB_BRACE);
}

if (count($additionalFiles)) {
    foreach ($additionalFiles as $image) {
        $imageName = basename($image);
        if (substr($image, -4) == '.txt') {
            $data['description'] = trim(file_get_contents($image));
            continue;
        } else if (substr($image, -4) == '.url') {
            $data['website'] = trim(file_get_contents($image));
            continue;
        } else if ($imageName == 'discover.jpg' || $imageName == 'discover.png'
            || $imageName == 'main.jpg' || $imageName == 'main.png'
        ) {
            $data['discover'] = $urlBasePath . $imageName;
            continue;
        }

        $data['media'][] = [
            'type' => 'image',
            'url'  => $urlBasePath . $imageName,
        ];
    }
}

$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    . "\n";

if (!$packageName) {
    echo $json;
}

$candidates = [
    realpath(__DIR__ . '/../gamestick') . '/' . $packageName . '.json',
    realpath(__DIR__ . '/../new') . '/' . $packageName . '.json',
];
foreach ($candidates as $jsonfile) {
    if (file_exists($jsonfile)) {
        echo $json;
        fwrite(STDERR, 'File exists already: ' . $jsonfile . "\n");
        exit(1);
    }
}

file_put_contents($jsonfile, $json);
echo 'Wrote file ' . $jsonfile . "\n";
?>
