<?php
/**
 * Add internet archive apk downloads to game json files
 */
array_shift($argv);
$iaJsonDir = array_shift($argv);
if ($iaJsonDir === null || !is_dir($iaJsonDir)) {
    echo "First parameter must be directory with internet archive json files\n";
    exit(1);
}

$files = $argv;
if (count($files) == 0) {
    echo "Pass some game json files\n";
    exit(1);
}

foreach ($files as $file) {
    $game = json_decode(file_get_contents($file));
    echo $game->packageName . "\n";

    $iaFiles = glob($iaJsonDir . '/ouya_' . $game->packageName . '*.json');
    if (!count($iaFiles)) {
        echo "No internet archive files for " . $game->packageName . "\n";
        continue;
    }

    foreach ($iaFiles as $iaJsonFile) {
        $iaData = json_decode(file_get_contents($iaJsonFile));
        $parts   = explode('_', basename($iaJsonFile, '.json'));
        $version = end($parts);

        $url = null;
        $iapks = [];
        foreach ($iaData->files as $iaFile) {
            if ($iaFile->format == 'Android Package Archive') {
                $iaSlug = basename($iaJsonFile, '.json');
                $url = 'https://archive.org/download/' . $iaSlug . '/'
                     . rawurlencode($iaFile->name);

                $iapks[] = [
                    'url' => $url,
                    'date' => isset($iaData->metadata->date)
                        ? str_replace(
                            ' ', 'T',
                            date('Y-m-d H:i:s', strtotime($iaData->metadata->date))
                            . 'Z'
                        )
                        : null,
                    'size' => (int) $iaFile->size,
                    'md5sum' => $iaFile->md5,
                ];
            }
        }

        if (count($iapks) == 0) {
            echo " no apk in $iaJsonFile\n";
            continue;
        } else if (count($iapks) == 1) {
            //one file in this version
            $apk = $iapks[0];
            $found = false;
            foreach ($game->releases as $release) {
                if ($release->name == $version) {
                    //same version! update!
                    echo " updating release $version\n";
                    $found = true;
                    $release->url    = $apk['url'];
                    //$release->date   = $apk['date'];
                    $release->size   = $apk['size'];
                    $release->md5sum = $apk['md5sum'];
                }
            }
            if (!$found) {
                //no existing release has this version. add new one.
                echo " adding new release $version\n";
                $game->releases[] = (object) [
                    'name'        => $version,
                    'versionCode' => 'FIXME',
                    'uuid'        => uuid_create(),
                    'date'        => $apk['date'],
                    'latest'      => false,
                    'url'         => $apk['url'],
                    'size'        => $apk['size'],
                    'md5sum'      => $apk['md5sum'],
                ];
            }
        } else {
            echo " multiple apks in IA!\n";
            $found = false;
            foreach ($game->releases as $release) {
                foreach ($iapks as $apk) {
                    if ($release->md5sum != $apk['md5sum']) {
                        continue;
                    }
                    echo " updating release $version\n";
                    $found = true;
                    $release->url    = $apk['url'];
                    //$release->date   = $apk['date'];
                    $release->size   = $apk['size'];
                    $release->md5sum = $apk['md5sum'];
                }
            }
            if (!$found) {
                //release md5sums do not match any of the apks
                echo " ERR: None of the .apks in IA have the correct md5sum\n";
                continue;
            }
        }

        //var_dump($game);
    }
    //die();
    file_put_contents(
        $file,
        json_encode($game, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
    );
    echo " written\n";
}

?>
