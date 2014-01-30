<?php

  /*
   * bitmap diff each pixels.
   * 2012/07/12- yoya@awm.jp
   */

function usage() {
    echo "Usage: php bitmap_diff.php <file1> <file2> <pngfile>\n";
}

if (($argc != 4) || (! is_readable($argv[1])) || (! is_readable($argv[2]))) {
    usage();
    exit (1);
}

$output_file = $argv[3];

if (is_readable($output_file)) {
    echo "ERROR: $output_file is exist.".PHP_EOL;
    usage();
    exit (1);
}

$data1 = file_get_contents($argv[1]);
$data2 = file_get_contents($argv[2]);
$im1 = imagecreatefromstring($data1);
$im2 = imagecreatefromstring($data2);

if (($im1 === false) || ($im2 === false)) {
    echo "Error: image1 or image2 has broken.";
    exit (1);
}

$width1  = imagesx($im1); $height1 = imagesy($im1);
$width2  = imagesx($im2); $height2 = imagesy($im2);

if (($width1 != $width2) || ($height1 != $height2)) {
    echo "Error: image1(".$width1."x".$height1.") image2(".$width2."x".$height2.")\n";
    exit (1);
}

$width = $width1;
$height = $height1;

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
