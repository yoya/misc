<?php
/*
 *  (c) 2017/08/21- yoya@awm.jp
 * $ composer require meyfa/php-svg
 */ 
require_once("vendor/autoload.php");
             
use SVG\SVGImage;
use SVG\Nodes\Structures\SVGStyle;
use SVG\Nodes\Structures\SVGDefs;
use SVG\Nodes\Gradients\SVGStop;
use SVG\Nodes\Gradients\SVGRadialGradient;
use SVG\Nodes\Shapes\SVGRect;

list($width, $height) =  [1000, 1000];

$image = new SVGImage($width, $height);
$doc = $image->getDocument();

$style = new SVGStyle();
$style->setCss(":root{background-color:black;}");

$doc->addChild($style);

$defs = new SVGDefs();
$doc->addChild($defs);

$gradTable = [
    "rgrad" => ["#F00", "#D00", "black"],
    "ggrad" => ["#0F0", "#0D0", "black"],
    "bgrad" => ["#22F", "#11D", "black"],
];

foreach ($gradTable as $id => $colors) {
    $grad = new SVGRadialGradient(0.5, 0.5, 0.7);
    $grad->setAttribute("id", $id);
    $stop0 = new SVGStop("40%", $colors[0]);
    $stop1 = new SVGStop("60%", $colors[1]);
    $stop2 = new SVGStop("100%",$colors[2]);
    $grad->addChild($stop0);
    $grad->addChild($stop1);
    $grad->addChild($stop2);
    $defs->addChild($grad);
}

$scale = 2;
list($unitX, $unitY) = [10*$scale, 40*$scale];
list($marginX1 , $marginY1) = [4*$scale, 2*$scale];
list($marginX2 , $marginY2) = [4*$scale, 4*$scale];

$dy = $unitY + $marginY1 +$marginY2;
for ($y = 0, $row = 0 ; $y < ($height+$marginY1+$unitY) ; $y+=$dy, $row++) {
    for ($x = 0, $column = 0 ; $x < $width ; $column++) {
        for ($i = 0 ; $i < 3 ; $i++) {
            $rect = new SVGRect($x, $y - (($column%2)?0:($dy/2)), $unitX, $unitY, $unitX/2, $unitY/7);
            //$color = ["#F00", "#0F0", "#44F"][$i];
            $color = ["url(#rgrad)","url(#ggrad)","url(#bgrad)"][$i];
            $rect->setStyle('fill', $color);
            $doc->addChild($rect);
            $x += $unitX + $marginX1;
        }
        $x += $marginX2;
    }
}

header('Content-Type: image/svg+xml');
echo $image;
exit (0);

//$rasterImage = $image->toRasterImage($width*2, $height*2);
//header('Content-Type: image/png');
//imagepng($rasterImage);
