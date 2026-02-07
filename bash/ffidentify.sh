#! /bin/bash

set -euo pipefail

for f in "$@" ; do
    echo -n "## filesize:"
    ls -s "$f"
    echo -n " "
    # ffmpeg -i "$f" 2>&1 | grep -e Stream -e Duration
    ffprobe "$f" 2>&1 | grep -e Stream -e Duration
done
