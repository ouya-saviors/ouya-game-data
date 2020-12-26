<?php
/**
 * Load manifest information from a .apk file
 *
 * @return array Keys are "packageName", "packageVersionCode" etc
 */
function loadBadgingInfo($apkFilePath)
{
    exec('aapt dump badging ' . escapeshellarg($apkFilePath), $lines, $exitcode);
    if ($exitcode != 0) {
        fwrite(STDERR, "error running aapt\n");
        exit(2);
    }
    foreach ($lines as $line) {
        if (strpos($line, ':') === false) {
            continue;
        }
        list($key, $val) = explode(':', $line, 2);
        $val = trim($val);
        if ($val[0] == "'" && $val[strlen($val) - 1] == "'") {
            $val = trim($val, "'");
        }
        $badging[$key] = $val;

        $matches = [];
        preg_match_all("#([^ ]+)='([^']*)'#", $badging[$key], $matches);
        if (isset($matches[2]) && count($matches[2]) > 0) {
            $parts = array_combine($matches[1], $matches[2]);
            foreach ($parts as $partKey => $partValue) {
                $badging[$key . ucfirst($partKey)] = $partValue;
            }
        }
    }
    return $badging;
}

?>
