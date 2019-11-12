#!/bin/sh
set -e
for i in `cat old-data/pkg-download.list`; do
    echo $i
    ./bin/convert-original.php\
        old-data/details/$i.json\
        old-data/devs.ouya.tv/api/v1/apps/$i.json\
        old-data/devs.ouya.tv/api/v1/apps/$i/download.json\
        > games/$i.json
    ./bin/validate-game.sh games/$i.json
done
