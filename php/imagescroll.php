<?php

$tmpFormat = 'tmp%04d.png';

function usage() {
    fprintf(STDERR, "Usage: php imagescroll.php <in.png> <out.mov> <width> <height> <seconds> <fps>\n");
    fprintf(STDERR, "ex) php imagescroll.php in.png out.mov 640 480 187 8\n");
}

if ($argc != 7) {
   usage();
   exit (1);
}

list($progname, $inputfilename, $outputfilename,
		 $width, $height, $seconds, $fps) = $argv;
echo "$progname, $inputfilename, $outputfilename, $width, $height, $seconds, $fps\n";

$width  = (int) $width;
$height = (int) $height;
$seconds = (float) $seconds;
$fps     = (float) $fps;

$imageCount = (int) ($seconds * $fps);

$size = getimagesize($inputfilename);
list($orig_width, $orig_height) = $size;

$orig_crop_height = round($orig_width * $height / $width);

$scroll_height = $orig_height - $orig_crop_height;

// make images.
foreach (range(1, $imageCount) as $i) {
    $y = round($scroll_height * $i / $imageCount * 1.1); // XXX: 1.1
    if ($y > $scroll_height) {
        $y = $scroll_height;
    }
    $tmpFile = sprintf($tmpFormat, $i);
    $cmd = "convert -crop {$orig_width}x{$orig_crop_height}+0+{$y} $inputfilename -resize {$width}x{$height} $tmpFile";
    echo $cmd.PHP_EOL;
    exec($cmd);
}

// make movie
$cmd = "ffmpeg -f image2 -r $fps -i $tmpFormat -r $fps  -an -vcodec libx264  -pix_fmt yuv420p $outputfilename";
echo $cmd.PHP_EOL;
exec($cmd);

// delete tmp file.
echo "unlink:";
foreach (range(1, $imageCount) as $i) {
    $tmpFile = sprintf($tmpFormat, $i);
    echo " $tmpFile";
    unlink($tmpFile);
}
echo PHP_EOL;
