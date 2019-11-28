#!/bin/sh
# Validate all ouya game data files
# in all the folders listed in the folders file
set -e
for folder in `cat folders`; do
    for gamefile in $folder/*.json; do
        echo "$gamefile"
        ./bin/validate-game.sh "$gamefile"
    done
done
