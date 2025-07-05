#! /bin/bash
set -euo pipefail

if [ $# -ne 3 ]; then
   echo "Usage: ffjoin.sh input1.m4a input2.m4a [input3.m4a [...]] output.m4a"
   exit 1;
fi

dir=`pwd`
listfile=`mktemp`
for f in "${@:1:($#-1)}" ; do
    echo "file '$dir/$f'" >> "$listfile"
done
output="${@:$#:1}"

cat "$listfile"
echo ">> $output"

ffmpeg -loglevel 24 -f concat -safe 0 -i "$listfile" -c copy "$output"

rm -f "$listfile"
