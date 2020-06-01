<?php
if ($argc < 2) {
    fwrite(STDERR, ".csv file path missing\n");
    exit(1);
}
$csvfile = $argv[1];
$hdl = fopen($csvfile, 'r');
while ($lineFields = fgetcsv($hdl)) {
    if (count($lineFields) != 2) {
        fwrite(STDERR, "Not 2 fields on this line\n");
        fwrite(STDERR, var_export($lineFields, true) . "\n");
        exit(2);
    }
    list($packageName, $url) = $lineFields;
    $jsonFile = __DIR__ . '/../classic/' . $packageName . '.json';
    if (!file_exists($jsonFile)) {
        fwrite(STDERR, "Game file does not exist: $jsonFile\n");
        continue;
    }

    $json = json_decode(file_get_contents($jsonFile));
    if (isset($json->developer->website) && $json->developer->website != '') {
        continue;
    }

    $json->developer->website = $url;
    file_put_contents(
        $jsonFile,
        json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
    );
}
fclose($hdl);
?>
