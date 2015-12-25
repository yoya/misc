<?php

if (php_sapi_name() === 'cli') {
    if ($argc != 5) {
        echo "Usage: php makeHeaderImage.php <imgfile> <width> <height> <global_alpha>\n";
        echo "Usage: php makeHeaderImage.php logo.png 954 1300 0.5\n";
        exit (1);
    }
    list($prog, $imgfile, $width, $height, $global_alpha) = $argv;
    
    $imgdata = file_get_contents($imgfile);
    makeHeaderImage($imgdata, $width, $height, $global_alpha);
} else {
    if (isset($_FILES['imgfile']) === false) {
        echo <<< FORM_MESSAGE
     <html>
     <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
     </head>
     <body>
        <form enctype="multipart/form-data" action="makeHeaderImage.php" method="POST">
        <input type="hidden" name="MAX_FILE_SIZE" value="100000000" />
        <!-- input 要素の名前が $_FILES 配列での名前となります -->
        Wordpressヘッダ背景画像の元にする画像ファイルをアップロード: <input name="imgfile" type="file" />
        <input type="submit" value="ファイル送信" />
       </form>
    <body>
    <html>
FORM_MESSAGE;
    } else {
        $imgfile = $_FILES['imgfile']['tmp_name'];
        $imgdata = file_get_contents($imgfile);
        header('Content-Type: image/png');
	$width = 954;
        $height = 1300;
        $global_alpha = 0.5;
        makeHeaderImage($imgdata, $width, $height, $global_alpha);
    }
}

exit(0);

function overlapimage($dst_im, $src_im, $x, $y, $size, $src_width, $src_height) {
    imagecopyresized($dst_im, $src_im,
                     (int)($x - $size/2), (int)($y - $size/2),  0, 0,
                     (int)$size, (int)$size,
                     (int)$src_width, (int)$src_height);
}

function mt_rand_float($min, $max) {
    $delta = $max - $min;
    return $min + mt_rand(0, mt_getrandmax()) / mt_getrandmax() * $delta;
}

function  conflict_draw_point($x, $y, $size) {
    global $draw_list;
    foreach ($draw_list as $draw_point) {
        $dx = $x - $draw_point[0];
        $dy = $y - $draw_point[1];
        if (($dx*$dx + $dy*$dy) < $size*$size) {
            return true; // conflist
        }
    }
    return false; // ok
}

function makeHeaderImage($imgdata, $width, $height, $global_alpha) {
    global $draw_list;
    $draw_list = array();
    $center_x = $width /2;
    $center_y = $height /2;
    $center_min = min($center_x, $center_y);
    
    $src_im = imagecreatefromstring($imgdata);
    $dst_im = imagecreatetruecolor($width, $height);
    imagesavealpha($dst_im, true);
    $trans = imagecolorallocatealpha($dst_im, 0, 0, 0, 127);
    imagefill($dst_im, 0, 0, $trans);
    $src_width = imagesx($src_im);
    $src_height = imagesy($src_im);
    //
    $limit = 1000;
    $range_min  = 2;
    $range_max  = 10;
    foreach (array_reverse(range($range_min, $range_max)) as $i) {
        $size = $center_min * 0.8 * ($range_max-$i+1) / $range_max;
        //        $size = $center_min * 0.8 * ($range_max-$i+1) / $range_max;
        $distri = ($i-1) / $range_max;
        foreach (range(0, $i*$i) as $j) {
            $length = mt_rand_float($distri * $distri * $distri * $distri , $distri);
            $radius = mt_rand_float(0, 3.14*2);
            $x = $center_x * (1 + $length * cos($radius));
            $y = $center_y * (1 + $length * sin($radius));
            if (conflict_draw_point($x, $y, $size*0.7)) {
                if ($limit-- <= 0) {
                    break 2; // skip
                }
                continue;
            }
            overlapimage($dst_im, $src_im, $x, $y, $size, $src_width, $src_height);
            $draw_list []= [$x, $y];
        }
    }
    $dst_im2 = imagecreatetruecolor($width, $height);
    imagesavealpha($dst_im2, true);
    imagefill($dst_im2, 0, 0, $trans);
    foreach (range(0, $height-1) as $y) {
        foreach (range(0, $width-1) as $x) {
            $c = imagecolorat($dst_im, $x, $y);
            $alpha =  127 - ($c >> 24);
            //            echo "### $alpha ";
            $alpha = (int)($alpha * $global_alpha);
            //            echo "-> $alpha\n";
            $c2 = ((127-$alpha) << 24) + ($c & 0xffffff);
            //            printf("%08x => %08x\n", $c, $c2);
            imagesetpixel($dst_im2, $x, $y, $c2);
        }
    }
    //
    imagepng($dst_im2);
    //
    imagedestroy($src_im);
    imagedestroy($dst_im);
}
