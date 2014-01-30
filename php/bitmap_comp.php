<?php

$data1 = file_get_contents($argv[1]);
$data2 = file_get_contents($argv[2]);
$im1 = imagecreatefromstring($data1);
$im2 = imagecreatefromstring($data2);

$width1  = imagesx($im1);
$height1 = imagesy($im1);

$width2  = imagesx($im2);
$height2 = imagesy($im2);

$width  = min($width1, $width2);
$height  = min($height1, $height2);

$distance_sqrt_sum = 0;

for ($y = 0 ; $y < $height ; $y++) {
    for ($x = 0 ; $x < $width ; $x++) {
        $i1 = imagecolorat($im1, $x, $y);
        $i2 = imagecolorat($im2, $x, $y);
        $rgba1 = imagecolorsforindex($im1, $i1);
        $rgba2 = imagecolorsforindex($im2, $i2);
        $red_diff   = $rgba1['red']   - $rgba2['red'];
        $green_diff = $rgba1['green'] - $rgba2['green'];
        $blue_diff  = $rgba1['blue']  - $rgba2['blue'];
        $distance_sqrt_sum += $red_diff*$red_diff + $green_diff*$green_diff + $blue_diff*$blue_diff;
    }
}

echo $distance_sqrt_sum / $width / $height . "\n";
