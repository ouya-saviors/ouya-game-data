#!/usr/bin/env php
<?php
/**
 * - s3.amazonaws.com are still ok
 * - d3e4aumcqn8cw3.cloudfront.net URLs are down
 *   - 8522 mirrored by cweiske
 *   - 2039 missing
 *     - 583 in internet archive
 * - filepicker-URLs have the same IDs as cloudfront, and are down :/
 */
$files = glob(__DIR__ . '/../games/*.json');
foreach ($files as $file) {
    $data = json_decode(file_get_contents($file));
    $package = $data->packageName;
    collectFile($package, $data->media->discover, 'discover');
    collectFile($package, $data->media->large, 'large');
    if (count($data->media->screenshots ?? [])) {
        $pos = 0;
        foreach ($data->media->screenshots as $url) {
            collectFile($package, $url, 'screenshot-' . ++$pos);
        }
    }
    if (count($data->media->details ?? [])) {
        $pos = 0;
        foreach ($data->media->details as $detail) {
            if ($detail->type == 'image') {
                collectFile($package, $detail->url, 'detail-' . ++$pos);
                collectFile($package, $detail->thumb, 'detail-' . $pos . '-thumb');
            }
        }
    }
    //die();
}

function collectFile($package, $url, $type)
{
    preg_match('#https://www.filepicker.io/api/file/([^/]+)/convert\?w=720#', $url, $matches);
    if (isset($matches[1])) {
        $url = 'https://d3e4aumcqn8cw3.cloudfront.net/api/file/' . $matches[1];
    }
    echo $url . "\n";
    return;
    echo $package
        . "," . $type
        . "," . $url
        . "\n";
}
?>
