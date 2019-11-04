<?php
$gamefiles = glob('devs.ouya.tv/api/v1/apps/*.json');

foreach ($gamefiles as $file) {
    echo "Processing $file\n";
    $data = json_decode(file_get_contents($file));
    if ($data === null) {
        echo "error opening " . $file . "\n";
        exit(1);
    }
    $package = strtolower(basename($file, '.json'));

    $dir = 'game-images/' . $package . '/';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $pos = 0;
    foreach ($data->app->filepickerScreenshots as $imageUrl) {
        $pos++;
        copyImageUrl($imageUrl, $dir . 'screenshot-' . $pos);
    }

    copyImageUrl($data->app->mainImageFullUrl, $dir . 'main');
    echo "\n";
}

function copyImageUrl($imageUrl, $newPath)
{
    $imageLocal = getLocalPath($imageUrl);
    if (!file_exists($imageLocal)) {
        echo "Local file not found: $imageLocal\n";
        return;
    }
    if (file_exists($newPath)) {
        echo "S";
        return;
    }
    
    echo ".";
    copy($imageLocal, $newPath);
    file_put_contents(
        'map-game-images.csv',
        $imageUrl . ','
        . 'http://ouya.cweiske.de/' . $newPath
        . "\n",
        FILE_APPEND
    );
}

function getLocalPath($imageUrl)
{
    return str_replace('https://', '', $imageUrl);
}
?>
