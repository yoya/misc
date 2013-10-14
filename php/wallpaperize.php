<?php

if (php_sapi_name() === 'cli') {
    if ($argc !== 3) {
        echo "Usage: php wallpaperize.php <src_dir> <dest_dir>".PHP_EOL;
        exit (1);
    }
    list($prog, $srcDir, $destDir) = $argv;
    
    if (! is_readable($srcDir)) {
        echo "ERROR: $srcDir is NOT readable".PHP_EOL;
        exit (1);
    }
    
    $files = array();
    foreach (scandir($srcDir) as $file) {
        $path = $srcDir."/".$file;
        if (is_file($path) && ($file[0] !== '.')) {
            $files []= $file;
        }
    }
    
    if (count($files) === 0) {
        echo "ERROR: file not found in $srcDir\n";
        exit (1);
    }
    
    if (! file_exists($destDir)) {
        mkdir($destDir);
    }
    
    foreach ($files as $file) {
        $srcPath = $srcDir."/".$file;
        $destPath = $destDir."/".$file;
        $imgData = file_get_contents($srcPath);
        $type = detectType($imgData);
        if ($type === false) { continue; } // skip unknown format
        $im = ImageCreateFromString($imgData);
        if ($im === false) { continue ; } // skip corrupted image;
        
        echo "$destPath\n";
        $ret = filterImage($im);
        if ($ret === false) { continue; } // skip
        
        switch ($type) {
        case IMG_JPEG:
            imagejpeg($im, $destPath);
            break;
        case IMG_PNG:
            imagepng($im, $destPath);
            break;
        case IMG_GIF:
            imagegif($im, $destPath);
            break;
        }
        ImageDestroy($im);
    }
} else {
    if (isset($_FILES['imgfile']) === false) {
        echo <<< FORM_MESSAGE
     <html>
     <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
     </head>
     <body>
        <form enctype="multipart/form-data" action="wallpaperize.php" method="POST">
        <input type="hidden" name="MAX_FILE_SIZE" value="100000000" />
        <!-- input 要素の名前が $_FILES 配列での名前となります -->
        壁紙用に暗くしたい画像ファイルをアップロード: <input name="imgfile" type="file" />
        <input type="submit" value="ファイル送信" />
       </form>
    <body>
    <html>
FORM_MESSAGE;
    } else {
        $imgfile = $_FILES['imgfile'];
        $imgData = file_get_contents($imgfile['tmp_name']);
        $im = ImageCreateFromString($imgData);
        $ret = filterImage($im);
        $mimetype =  $imgfile['type'];
        header("Content-Type: $mimetype");
        switch ($mimetype) {
        case 'image/jpeg':
            imagejpeg($im, $destPath);
            break;
        case 'image/png':
            imagepng($im, $destPath);
            break;
        case 'image/gif':
            imagegif($im, $destPath);
            break;
        }
    }
}

exit(0);

function detectType($imgData) {
    if (strncmp($imgData, "\xff\xd8\xff", 3) == 0) {
        return IMG_JPEG;
    } else if (strncmp($imgData, "\x89PNG", 4) == 0) {
        return IMG_PNG;
    } else if (strncmp($imgData, 'GIF', 3) == 0) {
        return IMG_GIF;
    }
    return false;
}

function  filterValue(&$value, $total, $maxvalue) {
    $ratio = $value / 255;
    $value = ($value * $ratio) + ($value * 127 / $maxvalue) * (1-$ratio);
    $value = ($value * 3 + $total) / 12;
}

function  filterPixel(&$red, &$green, &$blue, $maxvalue) {
    $total = $red + $green + $blue;
    filterValue($red,   $total, $maxvalue);
    filterValue($green, $total, $maxvalue);
    filterValue($blue,  $total, $maxvalue);
}

function filterImage($im) {
    $sx = imagesx($im);  $sy = imagesy($im);
    if (imageistruecolor($im)) { // true color
        $maxvalue = 0;
        for ($y = 0 ; $y < $sy; $y++) {
            for ($x = 0 ; $x < $sx; $x++) {
                $c = imagecolorat($im, $x, $y);
                $red   = ($c >> 16) & 0xff;
                $green = ($c >>  8) & 0xff;
                $blue  =  $c        & 0xff;
                $maxvalue = MAX($maxvalue, $red, $green, $blue);
            }
        }
        for ($y = 0 ; $y < $sy; $y++) {
            for ($x = 0 ; $x < $sx; $x++) {
                $c = imagecolorat($im, $x, $y);
                $alpha =  $c >> 24;
                $red   = ($c >> 16) & 0xff;
                $green = ($c >>  8) & 0xff;
                $blue  =  $c        & 0xff;
                filterPixel($red, $green, $blue, $maxvalue);
                $c2 = ($alpha << 24) + ($red << 16) + ($green << 8) + ($blue << 0);
                imagesetpixel($im, $x, $y, $c2);
            }
        }
    } else { // palette color
        $ct = imagecolorstotal($im);
        $maxvalue = 0;
        for ($i = 0 ; $i < $ct; $i++) {
            $c = imagecolorsforindex($im, $i);
            $red   = $c['red'];
            $green = $c['green'];
            $blue  = $c['blue'];
            $maxvalue = MAX($maxvalue, $red, $green, $blue);
        }
        for ($i = 0 ; $i < $ct; $i++) {
            $c = imagecolorsforindex($im, $i);
            $alpha = $c['alpha'];
            $red   = $c['red'];
            $green = $c['green'];
            $blue  = $c['blue'];
            filterPixel($red, $green, $blue, $maxvalue);
            if (($alpha < 0) || (PHP_VERSION_ID < 50400)) {
                imagecolorset($im, $i, $red, $green, $blue);
            } else {
                imagecolorset($im, $i, $red, $green, $blue, $alpha); // >= 5.4.0
            }
        }
    }
    return true;
}
