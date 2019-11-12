#!/bin/sh
set -e
for i in `cat old-data/pkg-download.list`; do
    echo $i
    ./bin/convert-single.sh "$i"
done
