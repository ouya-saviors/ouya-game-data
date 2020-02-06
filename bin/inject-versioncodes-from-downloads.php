<?php
/**
 * Parse downloaded .apk files and get their version code, updating the json
 * file.
 *
 * Download apk files:
 * $ jq -r .releases[].url ../second/*.json|grep -v FIXME |xargs -L1 wget
 */
array_shift($argv);
$dlDir = array_shift($argv);
if ($dlDir === null || !is_dir($dlDir)) {
    echo "First parameter must be directory with downloaded .apks\n";
    exit(1);
}

$files = $argv;
if (count($files) == 0) {
    echo "Pass some game json files\n";
    exit(1);
}

$errorFiles = [];
foreach ($files as $file) {
    $game = json_decode(file_get_contents($file));
    echo $game->packageName . "\n";

    $modified = false;
    $unset = [];
    foreach ($game->releases as $key => $release) {
        if ($release->versionCode !== null
            && $release->versionCode !== 0//may be ok, but sometimes not
            && $release->versionCode !== 'FIXME'
        ) {
            echo " already ok for version " . $release->name . "\n";
            continue;
        }
        if ($release->url === 'FIXME') {
            echo " URL is FIXME\n";
            continue;
        }

        $apkFile = $dlDir . '/' . basename(urldecode($release->url));
        if (!file_exists($apkFile)) {
            echo " ERROR: .apk file missing at $apkFile\n";
            echo " Download-URL: " . $release->url . "\n";
            continue;
        }

        $output = shell_exec('aapt dump badging ' . escapeshellarg($apkFile));
        $firstLine = explode("\n", $output)[0];
        if (substr($firstLine, 0, 8) != 'package:') {
            echo " ERROR: aapt output unexpected!\n";
            echo $firstLine . "\n";
            continue;
        }

        //versionCode may be empty string
        preg_match_all("#([^ ]+)='([^']*)'#", substr($firstLine, 8), $matches);
        $data = array_combine($matches[1], $matches[2]);

        if (!isset($data['versionCode'])) {
            echo " ERROR: versionCode missing in $apkFile\n";
            $errorFiles[] = $file;
            continue;
        } else if ($data['versionCode'] === '') {
            $data['versionCode'] = null;
        } else {
            $data['versionCode'] = (int) $data['versionCode'];
        }

        if (!isset($data['versionName'])) {
            echo " ERROR: versionName missing in $apkFile\n";
            $errorFiles[] = $file;
            continue;
        }
        if (!isset($data['name'])) {
            echo " ERROR: name missing in $apkFile\n";
            $errorFiles[] = $file;
            continue;
        }

        if ($data['name'] != $game->packageName) {
            echo " WARNING: Different package names in $apkFile:\n";
            echo "  JSON: " . $game->packageName . "\n";
            echo "  .apk: " . $data['name'] . "\n";
            echo " removing release\n";
            $unset[] = $key;
            continue;
        }

        $release->versionCode = $data['versionCode'];
        $release->name        = $data['versionName'];
        $modified = true;
    }

    if (count($unset)) {
        foreach ($unset as $key) {
            unset($game->releases[$key]);
            $modified = true;
        }
        $game->releases = array_values($game->releases);//re-key
    }

    if ($modified) {
        echo " updating file\n";
        file_put_contents(
            $file,
            json_encode($game, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
        );        
    }
}

if (count($errorFiles)) {
    echo "Errors in:\n";
    foreach ($errorFiles as $errorFile) {
        echo $errorFile . "\n";
    }
    exit(1);
}
?>
