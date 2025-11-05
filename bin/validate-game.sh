#!/bin/sh
# Validate an OUYA game schema file with several validators
set -e

if [ $# -lt 1 ]; then
    echo Error: game json filename missing
    exit 1
fi

for file in "$@"; do
    if [ ! -f "$file" ]; then
        echo "Error: file does not exist: $file"
        exit 2
    fi

    check-jsonschema --schemafile ouya-game.schema.json "$file"
done
