#!/bin/sh
# Validate all ouya game data files
# in all the folders listed in the folders file
set -e
foldersToCheck=$(paste -d '|' -s folders)
for gamefile in `git diff --name-status HEAD~1 | grep -v -e "^D" | sed -r 's/^.\s+//' | grep -E "^$foldersToCheck"`; do
    echo "$gamefile"
    validate-json "$gamefile" ouya-game.schema.json
done

for folder in `cat folders`; do
    iparams=""
    for gamefile in $folder/*.json; do
        iparams="$iparams -i $gamefile"
        echo "$gamefile"
        validate-json "$gamefile" ouya-game.schema.json
    done
    jsonschema $iparams ouya-game.schema.json
done
