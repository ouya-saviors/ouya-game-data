#!/bin/sh
# Validate an OUYA game schema file with several validators
set -e
validate-json "$1" ouya-game.schema.json
jsonschema -i "$1" ouya-game.schema.json
