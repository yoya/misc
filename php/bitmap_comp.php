<?php

$files = array_slice($argv, 1);

$images = array();
$width = $height = PHP_INT_MAX;

foreach ($files as $file) {
    if (strncmp($file, 's3://', 4) === 0) {
        require_once('S3_GetFile.php');
        $data = S3_GetFile($file);
    } else {
        $data = file_get_contents($file);
    }
    $im = imagecreatefromstring($data);
    $width  = min($width,  imagesx($im));
    $height = min($height, imagesy($im));
    $images []= $im;
}
list($im1, $im2) = $images;

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
