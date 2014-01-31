<?php

  /*
   * bitmap diff each pixels.
   * 2012/07/12- yoya@awm.jp
   */

function usage() {
    echo "Usage: php bitmap_diff.php <file1> <file2> <file3>\n";
  }

if ($argc != 4) {
    usage();
    exit (1);
}

$output_file = $argv[3];

if (is_readable($output_file)) {
    echo "Error: $output_file is exist.\n";
    usage();
    exit (1);
}

$files = array_slice($argv, 1, 2);

$images = array();
$widths = array();
$heights = array();

foreach ($files as $file) {
    if (strncmp($file, 's3://', 5) === 0) {
        require_once('S3_GetFile.php');
        $data = S3_GetFile($file);
    } else {
        $data = file_get_contents($file);
        if ($data === false) {
            echo "Error: Can't open file($file)\n";
            exit (1);
        }
    }
    $im = imagecreatefromstring($data);
    if ($im === false) {
        echo "Error: image($file) has broken.\n";
        exit (1);
    }
    $images []= $im;
    $widths  []= imagesx($im);
    $heights []= imagesy($im);
}

list($im1,$im2) = $images;
list($width1, $width2)   = $widths;
list($height1, $height2) = $heights;

if (($width1 != $width2) || ($height1 != $height2)) {
    echo "Error: image1(".$width1."x".$height1.") image2(".$width2."x".$height2.")\n";
    exit (1);
}

$width  = min($widths);
$height = min($heights);

$im3 = imagecreatetruecolor($width, $height);

$pixeldata = array();
$max_diff = 0;
for ($y = 0 ; $y < $height ; $y++) {
    for ($x = 0 ; $x < $width ; $x++) {
        $i1 = imagecolorat($im1, $x, $y);
        $i2 = imagecolorat($im2, $x, $y);
        $rgb1 = imagecolorsforindex($im1, $i1);
        $rgb2 = imagecolorsforindex($im2, $i2);
        $red_diff   = abs($rgb2['red']   - $rgb1['red']);
        $green_diff = abs($rgb2['green'] - $rgb1['green']);
        $blue_diff  = abs($rgb2['blue']  - $rgb1['blue']);
        $pixeldiff []= array($red_diff, $green_diff, $blue_diff);
        $max_diff = max($max_diff, max($red_diff, max($green_diff, $blue_diff)));
    }
}

if ($max_diff === 0) {
    $black = imagecolorallocate($im3, 0, 0, 0);
    imagefill($im3, $x, $y, $black);
} else {
    foreach ($pixeldiff as $idx => $diff) {
        $pixeldiff[$idx] = array($diff[0] * 255 / $max_diff,
                                 $diff[1] * 255 / $max_diff,
                                 $diff[2] * 255 / $max_diff);
    }
    $i = 0;
    for ($y = 0 ; $y < $height ; $y++) {
        for ($x = 0 ; $x < $width ; $x++) {
            $diff = $pixeldiff[$i++];
            $color = imagecolorallocate($im3, $diff[0], $diff[1], $diff[2]);
            imagesetpixel($im3, $x, $y, $color);
        }
    }
}

imagepng($im3, $output_file);
