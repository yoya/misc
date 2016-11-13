<?php

if ($argc < 4) {
    echo "Usage: php whitenoize.php <width> <height> <jpg|gif|png|png8|png32>".PHP_EOL;
    echo "ex) php whitenoize.php 240 320 jpg".PHP_EOL;
    exit (1);
}
$type_list = ['jpg', 'gif', 'png', 'png8', 'png32'];
$palette_list = ['gif', 'png8'];

list($prog, $width, $height, $type) = $argv;

if (in_array($type, $type_list) === false) {
    echo "Unknown type:$type".PHP_EOL;
    exit(1);
}

if (in_array($type, $palette_list)) { // gif と png8
    $im = imagecreate($width, $height);
    $palette = array();
    for ($i = 0 ; $i < 256 ; $i++) {
        $palette []= imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
    }
    for ($y = 0 ; $y < $height ; $y++) {
        for ($x = 0 ; $x < $width ; $x++) {
            imagesetpixel($im, $x, $y, $palette[mt_rand(0, 255)]);
        }
    }
} else if ($type !== "png32"){
    $im = imagecreatetruecolor($width, $height);
    for ($y = 0 ; $y < $height ; $y++) {
        for ($x = 0 ; $x < $width ; $x++) {
            $c = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($im, $x, $y, $c);
        }
    }
} else {
    $im = imagecreatetruecolor($width, $height);
    for ($y = 0 ; $y < $height ; $y++) {
        for ($x = 0 ; $x < $width ; $x++) {
            $c = imagecolorallocatealpha($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 127));
            imagesetpixel($im, $x, $y, $c);
        }
    }
    imagesavealpha($im, true);
}

switch ($type) {
case "jpg":
    imagejpeg($im);
    break;
case "gif":
    imagegif($im);
    break;
case "png":
case "png8":
case "png32":
    imagepng($im);
    break;
}
