#! /bin/bash
set -euo pipefail

if [ $# -ne 3 ]; then
   echo "Usage: ffmerge.sh input.mp4 input.wav output.mp4"
   exit 1;
fi

iv="$1" ; ia="$2" ; o="$3"

# ref) https://stackoverflow.com/questions/2869281/how-to-determine-video-codec-of-a-file-with-ffmpeg
cv=`ffprobe -v error -select_streams v:0 -show_entries stream=codec_name -of default=noprint_wrappers=1:nokey=1 "$iv"`
ca=`ffprobe -v error -select_streams a:0 -show_entries stream=codec_name -of default=noprint_wrappers=1:nokey=1 "$ia"`

ev="h264" ; ea="aac"

if [[ "$cv" =~ "$ev" ]]; then
    ev="copy"
fi
if [[ "$ca" =~ "$ea" ]]; then
    ea="copy"
fi

echo "cvodec:$cv=>$ev" "caodec:$ca=>$ea"

ffmpeg -loglevel 24 -i "$iv" -i "$ia" -c:v "$ev" -c:a "$ea" \
       -map 0:v:0 -map 1:a:0 "$o" -progress pipe:1 | \
    awk '{ if ($1 ~ /^frame/) { printf "*" ; fflush() } }' ;
echo ;
