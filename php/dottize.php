<?php
if (empty($_FILES['imagefile']['tmp_name']) && (empty($argv[1]))) {
?>
<html>
<head>
<title> ドット絵っぽく見せる変換ツール </title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body bgcolor="#f0ffff">
<form enctype="multipart/form-data" action="dottize.php?ext=.png" method="POST">
     <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
      画像ファイル(GIF/PNG/JPEGをアップロード: <input name="imagefile" type="file" /><br />
     倍率: <input name="scale" value="8" type="text"/> <br />
     マージン: <input name="margin" value="1" type="text"/> <br />
     <input type="submit" value="ファイルを送信" />

<p> 小さい画像を入れてね (はーと) </p>
</form>
<?php
        exit (0);
}

if (isset($_FILES['imagefile']['tmp_name'])) {
    $imagefile = $_FILES['imagefile']['tmp_name'];
    $scale = $_REQUEST['scale'];
    $margin = $_REQUEST['margin'];
} else {
    $imagefile = $argv[1];
    $scale = (int) $argv[2];
    $margin = (int) $argv[3];
}

function getimagecoloralpha($im, $red, $green, $blue, $alpha) {
	$color = imagecolorexactalpha($im, $red, $green, $blue, $alpha);
	if ($color < 0) {
		$color = imagecolorallocatealpha($im, $red, $green, $blue, $alpha);
	}
	return $color;
}

$imageinfo = getimagesize($imagefile);

switch ($imageinfo[2]) {
    case IMAGETYPE_GIF:
	$im = imagecreatefromgif($imagefile);
	break;
    case IMAGETYPE_PNG:
	$im = imagecreatefrompng($imagefile);
	break;
    case IMAGETYPE_JPEG:
	$im = imagecreatefromjpeg($imagefile);
	break;
    default:
	echo "we want gif or png file\n";
	usage();
	exit(1);
}

$width  = imagesx($im);
$height = imagesy($im);

$im2 = imagecreatetruecolor($scale*$width + $margin, $scale*$height + $margin);

for ($y = 0 ; $y < $height ; $y++) {
    for ($x = 0 ; $x < $width ; $x++) {
	$color = imagecolorat($im, $x, $y);
	$xx = $scale*$x;
	$yy = $scale*$y;
	$rgb = imagecolorsforindex($im, $color);
	$color2 = getimagecoloralpha($im2, $rgb['red'], $rgb['green'], $rgb['blue'], $rgb['alpha']);
	$xx = $scale*$x + $margin;
	$yy = $scale*$y + $margin;
	imagefilledrectangle($im2, $xx, $yy, $xx + $scale - $margin, $yy + $scale - $margin, $color2);
    }
}

$black = getimagecoloralpha($im2, 0, 0, 0, 0);

if ($margin > 0) {
  for ($x = 0 ; $x <= $width ; $x++) {
    $xx = $scale*$x;
    imagefilledrectangle($im2, $xx, 0, $xx, $scale*$height + $margin - 1, $black);
  }
  for ($y = 0 ; $y <= $height ; $y++) {
    $yy = $scale*$y;
    imagefilledrectangle($im2, 0, $yy, $scale*$width + $margin - 1, $yy, $black);
  }
}

header('Content-type: image/png');
imagepng($im2);

