<?php

/*
 *  (c) 2017/08/20- yoya@awm.jp
 * $ composer require yoya/php-svg
 */ 
require_once("vendor/autoload.php");
             
use SVG\SVGImage;
use SVG\Nodes\Structures\SVGStyle;
use SVG\Nodes\Structures\SVGDefs;
use SVG\Nodes\Gradients\SVGStop;
use SVG\Nodes\Gradients\SVGRadialGradient;
use SVG\Nodes\Shapes\SVGCircle;

list($width, $height) =  [1000, 1000];

$image = new SVGImage($width, $height);
$doc = $image->getDocument();

$style = new SVGStyle();
$style->setCss(":root {background-color:black; }");
$doc->addChild($style);

$defs = new SVGDefs();
$doc->addChild($defs);

$gradTable = [
    "rgrad" => ["#F00", "#B00", "black"],
    "ggrad" => ["#0F0", "#0B0", "black"],
    "bgrad" => ["#22F", "#11B", "black"],
];

foreach ($gradTable as $id => $colors) {
    $grad = new SVGRadialGradient(0.5, 0.5, 0.45);
    $grad->setAttribute("id", $id);
    $stop0 = new SVGStop("60%", $colors[0]);
    $stop1 = new SVGStop("90%", $colors[1]);
    $stop2 = new SVGStop("100%",$colors[2]);
    $grad->addChild($stop0);
    $grad->addChild($stop1);
    $grad->addChild($stop2);
    $defs->addChild($grad);
}

$unitX = 40;
$unitY = sqrt($unitX*$unitX - ($unitX/2)*($unitX/2));
$radius = 17;

for ($y = 0, $row = 0 ; $y < ($height+$unitY) ; $y+= $unitY, $row++) {
    for ($x = ($row%2)?0:($unitX/2), $column = 0 ; $x < $width ; $x+= $unitX, $column++) {
        $circle = new SVGCircle($x, $y, $radius, $radius);
        $idx = (((int)($row*3/2) + $column)%3);
        // $color = ["#FF0000", "#00FF00", "#0000FF"][$idx];
        $color = ["url(#rgrad)","url(#ggrad)","url(#bgrad)"][$idx];
        $circle->setStyle('fill', $color);
        $doc->addChild($circle);
    }
}

header('Content-Type: image/svg+xml');
echo $image;
