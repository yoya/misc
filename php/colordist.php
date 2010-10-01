<?php
if (empty($_FILES['imagefile']['tmp_name']) && (empty($argv[1]))) {
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
      </head>
<body>
<form enctype="multipart/form-data" action="" method="POST">
          <input type="hidden" name="MAX_FILE_SIZE" value="67108864" />
      画像ファイルをアップロード: <input name="imagefile" type="file" />
          <input type="submit" value="ファイルを送信" />
</form>
<?php
        exit (0);
}
if (isset($_FILES['imagefile']['tmp_name'])) {
    $file = $_FILES['imagefile']['tmp_name'];
} else {
    $file = $argv[1];
}

/*
 * color totaling
 */

$imageinfo = getimagesize($file);

if (! isset($imageinfo[2])) {
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
    echo 'もしかして、画像ファイルじゃない？';
    exit (0);
}
$imagetype = $imageinfo[2];

switch ($imagetype) {
  case IMAGETYPE_GIF:
    $image = imagecreatefromgif($file);
    break;
  case IMAGETYPE_JPEG:
    $image = imagecreatefromjpeg($file);
    break;
  case IMAGETYPE_PNG:
    $image = imagecreatefrompng($file);
    break;
  default:
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
    echo 'もしかして、GIF,JPEG,PNG のどれでもない？';
    exit (0);
}

$width  = imagesx($image);
$height = imagesy($image);

$colordist = array();

for ( $y = 0 ; $y < $height ; $y++ ) {
    for ( $x = 0 ; $x < $width ; $x++ ) {
        $ci = imagecolorat($image, $x, $y);
        $c = imagecolorsforindex($image, $ci);
        @$colordist[$c['blue']][$c['green']][$c['red']]++;
    }
}

/*
 * display
 */

require_once('Y3D.php');

$rotate = array('x' => 10, 'y' => 20, 'z' => 30);
$params = array('width' => 240, 'height' => 240,
                'distance_to_eye' => 1200,
                'obj_rotation' => $rotate);

$y3d = new Y3D($params);

// axis
$y3d->setColor(255, 100, 100);
$y3d->drawLine(-50, -50, -50, 50, -50, -50);
$y3d->setColor(100, 255, 100);
$y3d->drawLine(-50, -50, -50, -50, 50, -50);
$y3d->setColor(100, 100, 255);
$y3d->drawLine(-50, -50, -50, -50, -50, 50);
// box frame backend
$y3d->setColor(50, 50, 50);
$y3d->drawLine(50, 50, -50, 50, -50, -50);
$y3d->drawLine(50, -50, 50, 50, -50, -50);

function Normalize255() {
    $a = func_get_args();
    if (is_array($a[0])) {
        $a = $a[0];
    }
    $m = max($a);
    if ($m == 0) {
        return $a;
    }
    $result = array();
    foreach ($a as $elem) {
        $result []= $elem * 255/$m;
    }
    return $result;
}

// plot
ksort($colordist); // z(=blue) axis sort
foreach ( $colordist as $b => $bt ) {
    foreach ( $bt as $g => $gt ) {
        foreach ( $gt as $r => $dummy ) {
            // (0 - 255) => (-50 - 50)
            $x = $r * 100 / 256 - 50;
            $y = $g * 100 / 256 - 50;
            $z = $b * 100 / 256 - 50;
            list($r, $g, $b) = Normalize255($r, $g, $b);
            $y3d->setColor($r, $g, $b);
            $y3d->drawPoint($x, $y, $z);
//            print "rgb=($r,$g,$b): xyz=($x,$y,$z)<br />\n";
        }
    }
}

// box frame front
$y3d->setColor(120, 120, 120);
$y3d->drawLine(50, 50, -50, -50, 50, -50);
$y3d->drawLine(-50, 50, 50, -50, 50, -50);
$y3d->setColor(100, 100, 100);
$y3d->drawLine(50, -50, 50, -50, -50, 50);
$y3d->drawLine(-50, 50, 50, -50, -50, 50);
$y3d->setColor(150, 150, 150);
$y3d->drawLine(-50, 50, 50, 50, 50, 50);
$y3d->setColor(100, 100, 100);
$y3d->drawLine(50, -50, 50, 50, 50, 50);
$y3d->drawLine(50, 50, -50, 50, 50, 50);

header('Content-type: image/png');
$y3d->outputpng();
