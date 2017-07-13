<?php

if ($argc < 3) {
    echo "Usage: php primarynoize.php <width> <height>".PHP_EOL;
    echo "ex) php primarynoize.php 240 320".PHP_EOL;
    exit (1);
}

list($prog, $width, $height) = $argv;

$im = imagecreate($width, $height);
$palette = array();
for ($i = 1 ; $i < 8 ; $i++) 
{
    $r = ($i & 4) >> 2;
    $g = ($i & 2) >> 1;
    $b = ($i & 1);
    $palette[$i] = imagecolorallocate($im, 255*$r, 255*$g, 255*$b);
}
for ($y = 0 ; $y < $height ; $y++) {
    for ($x = 0 ; $x < $width ; $x++) {
        imagesetpixel($im, $x, $y, $palette[mt_rand(1, 7)]);
    }
}
imagesavealpha($im, false);

imagepng($im);
