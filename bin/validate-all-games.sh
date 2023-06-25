#!/bin/sh
# Validate all ouya game data files
# in all the folders listed in the folders file
set -e
foldersToCheck=$(paste -d '|' -s folders)
#validate recently changed first
iparams=""
for gamefile in `git diff --name-status HEAD~1 | grep -v -e "^D" | sed -r 's/^.\s+//' | grep -E "^$foldersToCheck"`; do
    iparams="$iparams $gamefile"
done
if [ -n "$iparams" ]; then
   check-jsonschema --schemafile ouya-game.schema.json $iparams
fi

for folder in `cat folders`; do
    iparams=""
    for gamefile in $folder/*.json; do
        iparams="$iparams $gamefile"
    done
    check-jsonschema --schemafile ouya-game.schema.json $iparams
done
