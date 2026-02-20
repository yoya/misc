#! /bin/bash
set -euo pipefail

function time2secs()
{
    t=$1
    echo $t | awk -F'h|m|:' '{ if($3!=""){ print $1*3600+$2*60+$3 } else if($2!="") { print $1*60+$2} else { print $1 } } '
}

# 時間情報を残してトリミング

if [ $# -ne 4 ]; then
   echo "Usage: fftrim.sh input.mp4 output.mp4 <start time> <end time>"
   exit 1;
fi

input="$1" ; output="$2" ; start=`time2secs $3` ; end=`time2secs $4`

echo "time: $start => $end"

cv=`ffprobe -v error -select_streams v:0 -show_entries stream=codec_name -of default=noprint_wrappers=1:nokey=1 "$input"`
ca=`ffprobe -v error -select_streams a:0 -show_entries stream=codec_name -of default=noprint_wrappers=1:nokey=1 "$input"`

echo "video:$cv audio:$ca"

if [[ "$cv" == "" ]]; then
    echo "Audio only: $ca"
    ffmpeg -loglevel 24 -i "$input" -filter_complex \
	   "[0:a]atrim=start=$start:end=$end[aout]" \
	   -map "[aout]" -c:a $ca $output  -progress pipe:1 | \
	awk '{ if ($1 ~ /^frame/) { printf "*" ; fflush() } }' ;
echo ;
    
elif [[ "$ca" == "" ]]; then
    echo "Video only: $cv"
    ffmpeg -loglevel 24 -i "$input" -filter_complex \
	   "[0:v]trim=start=$start:end=$end[vout]" \
	   -map "[vout]" -c:v $cv $output  -progress pipe:1 | \
	awk '{ if ($1 ~ /^frame/) { printf "*" ; fflush() } }' ;
echo ;
else
    echo "Video and Audio: $cv $ca"
    ffmpeg -loglevel 24 -i "$input" -filter_complex \
	   "[0:v]trim=start=$start:end=$end[vout];[0:a]atrim=start=$start:end=$end[aout]" \
	   -map "[vout]" -map "[aout]" -c:v $cv -c:a $ca $output \
	   -progress pipe:1 | \
	awk '{ if ($1 ~ /^frame/) { printf "*" ; fflush() } }' ;
echo ;
fi
