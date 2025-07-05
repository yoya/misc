#! /bin/bash

for f in "$@" ; do
    ls -s "$f"
    echo -n " "
    ffmpeg -i "$f" >& /dev/stdout | grep -e Stream -e Duration
done
