#!/bin/sh
#Check if all URLs in the game data file exist and return no 404

if [ "$#" -lt 1 ]; then
    echo "Usage: check-links.sh gamedatafile.json" >> /dev/stderr
    exit 1
fi

hasError=0
for gamefile in "$@"; do
    if [ ! -f "$gamefile" ]; then
        echo "Error: File does not exist: $gamefile" >> /dev/stderr
        exit 2
    fi

    echo Checking $gamefile
    for url in $(jq -r '(.releases[].url, .discover, .media[].url, .media[].thumb)|strings' < "$gamefile"); do
        curl --fail --silent --output /dev/null -I "$url"
        if [ $? -eq 0 ]; then
            echo "OK $url"
        else
            hasError=1
            echo "ERROR $url" >> /dev/stderr
        fi
    done
done

if [ $hasError -eq 1 ]; then
    exit 1
fi
