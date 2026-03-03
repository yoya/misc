#! /bin/bash

#set -euo pipefail
set -eu

color="blue"
size="320x240"
rate="24"
fontcolor="white"

if [ $# -ne 3 ]; then
   echo "Usage: ffstill.sh <input.wav> <output.mp4> <text>"
   exit 1;
fi

input="$1" ; output="$2" ; text="$3"

strlen=${#text}
fontsize="(h*1.5/$strlen)"

duration=`ffmpeg -i $input 2>&1 | grep Duration | awk -F '[, ]' '{print $4}'`
echo "Duration:" $duration

ffmpeg -i $input -f lavfi -i "color=c=$color:s=$size:r=$rate,drawtext=text=$text:fontcolor=$fontcolor:fontsize=$fontsize:x=(w-text_w)/2:y=(h-text_h)/2" -t $duration $output
