#!/bin/sh
# Create an image that contains a QR code for the given website
# together with the URL as readable text
set -e

if [ "$#" -lt 2 ]; then
    echo "Usage: create-qr.sh https://example.org outfile.png" >> /dev/stderr
    exit 1
fi

url="$1"
filename="$2"

qrencode -s 20 -o tmp-qr.png "$url"

convert\
    -filter point -resize 1260x580\
    -background white\
    tmp-qr.png\
    -size 1260x120\
    -fill black\
    -gravity south\
    label:"$url"\
    -append\
    -bordercolor white\
    -border 10\
    "$filename"

rm tmp-qr.png
