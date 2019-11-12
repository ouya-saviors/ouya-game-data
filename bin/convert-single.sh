#!/bin/sh
set -e

packageName=$1
./bin/convert-original.php\
    old-data/details/$packageName.json\
    old-data/devs.ouya.tv/api/v1/apps/$packageName.json\
    old-data/devs.ouya.tv/api/v1/apps/$packageName/download.json\
    > games/$packageName.json
./bin/validate-game.sh games/$packageName.json
