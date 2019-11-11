#!/bin/sh
# Validate an OUYA game schema file with several validators
set -e
jsonschema -i "$1" ouya-game.schema.json
validate-json "$1" ouya-game.schema.json
