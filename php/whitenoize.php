<?php

$im = imagecreate(240,320);
$palette = array();
for ($i = 0 ; $i < 256 ; $i++) {
    $palette []= imagecolorallocate(
        $im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)
        );
}
for ($y = 0 ; $y < 320 ; $y++) {
    for ($x = 0 ; $x < 240 ; $x++) {
        imagesetpixel($im, $x, $y, $palette[mt_rand(0, 255)]);
    }
}

// imagegif($im);
imagejpeg($im);
