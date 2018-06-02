<?php
/*
 *  (c) 2017/08/21- yoya@awm.jp
 * $ composer require yoya/php-svg
 */ 
require_once("vendor/autoload.php");
             
use SVG\SVGImage;
use SVG\Nodes\Structures\SVGStyle;
use SVG\Nodes\Structures\SVGDefs;
use SVG\Nodes\Gradients\SVGStop;
use SVG\Nodes\Gradients\SVGLinearGradient;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Structures\SVGUse;

list($width, $height) =  [1000, 1000];

$image = new SVGImage($width, $height);
$doc = $image->getDocument();

$style = new SVGStyle();
$style->setCss(":root {background-color:black; }");
$doc->addChild($style);

$defs = new SVGDefs();
$doc->addChild($defs);

$opacities1 = [1, 1, 1, 1, 1];
$opacities2 = [0.5, 0.1, 0.0, 0.1, 0.5];
$gradTable = [
    "rgrad" => [[0, 0, 0, 1], ["#200", "#D00", "#F00", "#D00", "#200"],
                $opacities1],
    "ggrad" => [[0, 0, 0, 1], ["#010", "#0D0", "#0F0", "#0D0", "#010"],
                $opacities1],
    "bgrad" => [[0, 0, 0, 1], ["#004", "#11D", "#22F", "#11D", "#004"],
                $opacities1],
    "grad2" => [[0, 0, 1, 0], ["#000", "#000", "#000", "#000", "#000"],
                 $opacities2],
];

foreach ($gradTable as $id => $gradEntry) {
    list($posi, $colors, $opacities) = $gradEntry;
    list($x1, $y1, $x2, $y2) = $posi;
    $grad = new SVGLinearGradient($x1, $y1, $x2, $y2);
    $grad->setAttribute("id", $id);
    for ($i = 0 ; $i < 5 ; $i++) {
        $offset = ["0", "0.2", "0.5", "0.8", "1"][$i];
        $stop = new SVGStop($offset, $colors[$i]);
        $stop->setAttribute("stop-opacity", $opacities[$i]);
        $grad->addChild($stop);
    }
    $defs->addChild($grad);
}

$scale = 2;
list($unitX, $unitY) = [10*$scale, 50*$scale];
list($marginX1 , $marginY1) = [4*$scale, 0*$scale];
list($marginX2 , $marginY2) = [0*$scale, 0*$scale];

$idList = [];
for ($y = 0 ; $y < ($height+$marginY1+$unitY) ; $y+=($unitY+$marginY1+$marginY2)) {
    for ($x = 0 ; $x < $width ; ) {
        for ($i = 0 ; $i < 3 ; $i++) {
            $group = new SVGGroup();
            $color = ["url(#rgrad)","url(#ggrad)","url(#bgrad)"][$i];
            $group->setAttribute('fill', $color);
            $id = "canvasGroup".$x."x".$y;
            $idList []= $id;
            $rect = new SVGRect($x+1, $y+1, $unitX-2, $unitY-2, 3*$scale, 3*$scale);
            $rect->setAttribute("id", $id);
            $group->addChild($rect);
            $doc->addChild($group);
            //
            $x += $unitX + $marginX1;
        }
        $x += $marginX2;
    }
}

foreach ($idList as $id) {
    $u = new SVGUse();
    $u->setAttribute("xmlns:xlink", "http://www.w3.org/1999/xlink");
    $u->setAttribute("xlink:href", "#".$id);
    $u->setAttribute("fill", "url(#grad2)");
    $doc->addChild($u);
}

header('Content-Type: image/svg+xml');
echo $image;
