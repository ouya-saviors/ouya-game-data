#!/bin/sh
# Validate an OUYA game schema file with several validators
set -e

if [ $# -lt 1 ]; then
    echo Error: game json filename missing
    exit 1
fi

if [ ! -f "$1" ]; then
    echo Error: file does not exist
    exit 2
fi

validate-json "$1" ouya-game.schema.json
jsonschema -i "$1" ouya-game.schema.json
