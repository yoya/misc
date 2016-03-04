<?php

$im = imagecreate(240,320);
$black = imagecolorallocate($im, 0, 0, 0);
imagefill($im, 0, 0, $black);
//imagepng($im);
//imagegif($im);
imagejpeg($im);

