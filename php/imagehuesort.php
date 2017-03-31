<?php

// original: http://php-archive.net/php/hsv-similar-images/
// modified by yoya at 2013/09/17

if ($argc !== 2) {
    echo "Usage: php imagehuesort.php <dir>\n";
    echo "ex) php imagehuesort.php img/\n";
    exit(1);
}

// 対象画像ディレクトリ
$dir = $argv[1];

$list = scandir($dir);
$files = array();
foreach ($list as $value) {
    $path = $dir . $value;
    if (is_file($path)) {
        $files[] = $path;
    }
}
 
$hueTable = array();
foreach ($files as $file) {
    $image  = loadImage($file);
    if ($image === false) {
        continue; // skip
    }
    $hsv = imageHsv($image);
    ImageDestroy($image); // 明示的に後始末しないとメモリリークする
    $hueTable[$file] = $hsv['h'];
}

asort($hueTable); // 色相値の小さい順にソート
$result = array_keys($hueTable);

header("Content-type: text/html;charset=utf-8");
foreach ($result as $file) {
    echo "<a href=\"$file\" target=\"_blank\"> <img src='$file' width='64' height='64' alt='$file' /> </a>".PHP_EOL;
}

exit (0);
 
// 画像を読み込む
function loadImage($filepath) {
    $data = file_get_contents($filepath);
    $image = @ImageCreateFromString($data); // XXX
    if ($image === false) {
        return false;
    }
    if (is_null($image)) {
        echo $filepath."\n";
    }
    return $image;
}

function imageHsv($image) {
    $width = imagesx($image);
    $height = imagesy($image);
    $thumb_width  = 1;
    $thumb_height = 1;
    $thumb = imagecreatetruecolor($thumb_width, $thumb_height);
    imagecopyresampled($thumb, $image, 0, 0, 0, 0,
                       $thumb_width, $thumb_height, $width, $height);
    $index = imagecolorat($thumb, 0, 0);
    $rgb   = imagecolorsforindex($thumb, $index);
    return rgb2hsv($rgb);
}
 
function rgb2hsv($rgb){
    $r = $rgb['red']   / 255;
    $g = $rgb['green'] / 255;
    $b = $rgb['blue']  / 255;
   
    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $v = $max;
   
    if($max === $min){
        $h = 0;
    } else if($r === $max){
        $h = 60 * ( ($g - $b) / ($max - $min) ) + 0;
    } else if($g === $max){
        $h = 60 * ( ($b - $r) / ($max - $min) ) + 120;
    } else {
        $h = 60 * ( ($r - $g) / ($max - $min) ) + 240;
    }
    if($h < 0) $h = $h + 360;
 
    $s = ($v != 0) ? ($max - $min) / $max : 0;
   
    $hsv = array('h' => $h, 's' => $s, 'v' => $v);
    return $hsv;
}
