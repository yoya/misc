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
    "ygrad" => [[0, 0, 0, 1], ["#210", "#DD0", "#FF0", "#DD0", "#210"],
                $opacities1],
    "bgrad" => [[0, 0, 0, 1], ["#004", "#11D", "#22F", "#11D", "#004"],
                $opacities1],
    "ggrad" => [[0, 0, 0, 1], ["#010", "#0D0", "#0F0", "#0D0", "#010"],
                $opacities1],
    "wgrad" => [[0, 0, 0, 1], ["#214", "#DDD", "#FFF", "#DDD", "#214"],
                $opacities1],
    "grad2" => [[0, 0, 1, 0], ["#000", "#000", "#000", "#000", "#000"],
                $opacities2],
];

$scale = 1;

$cellSize = [100, 100];

$gridTable = [
    [
        ["grad" => "rgrad", "scale" => [1/3, 1.0], "ratio" => [0.3, 0.9],
         "round" =>[0.1, 0.1] ]
    ], [
        ["grad" => "ggrad", "scale" => [1/3, 1.0], "ratio" => [0.3, 0.9],
         "round" =>[0.1, 0.1] ]
    ], [
        ["grad" => "bgrad", "scale" => [1/3, 1.0], "ratio" => [0.3, 0.9],
        "round" =>[0.1, 0.1] ]
    ], [
        ["grad" => "wgrad", "scale" => [1.0, 1.0], "ratio" => [0.9, 0.9],
         "round" =>[0.1, 0.2] ]
    ]
];


foreach ($gradTable as $id => $gradEntry) {
    list($posi, $colors, $opacities) = $gradEntry;
    list($x1, $y1, $x2, $y2) = $posi;
    $grad = new SVGLinearGradient($x1, $y1, $x2, $y2);
    $grad->setAttribute("id", $id);
    for ($i = 0 ; $i < 5 ; $i++) {
        $offset = ["0", "0.1", "0.5", "0.9", "1"][$i];
        $stop = new SVGStop($offset, $colors[$i]);
        $stop->setAttribute("stop-opacity", $opacities[$i]);
        $grad->addChild($stop);
    }
    $defs->addChild($grad);
}

$scale = 2;

list($unitX, $unitY) = [0, 0];
list($marginX1 , $marginY1) = [0, 0];
list($marginX2 , $marginY2) = [0, 0];

$idList = [];
for ($y = 0, $yi = 0; $y < ($height + $cellSize[1]) ; $yi++) {
    for ($x = 0, $xi = 0 ; $x < ($width + $cellSize[0]) ; $xi++) {
        $xi_mod = $xi % count($gridTable);
        $gridTableEntry = $gridTable[$xi_mod];
        $yi_mod = $yi % count($gridTableEntry);
        $cell = $gridTable[$xi_mod][$yi_mod];
        //
        $color = "url(#".$cell["grad"].")";
        $x1 = $x;
        $y1 = $y;
        $x2 = $x + $cellSize[0] * $cell["scale"][0];
        $y2 = $y + $cellSize[1] * $cell["scale"][1];
        if (isset($cell["ratio"])) {
            $ratio = $cell["ratio"];
            $xc = ($x1 + $x2) / 2;
            $yc = ($y1 + $y2) / 2;
            $xh = $x2 - $xc;
            $yh = $y2 - $yc;
            $x1 = $xc - $cellSize[0]/2*$ratio[0];
            $y1 = $yc - $cellSize[1]/2*$ratio[1];
            $x2 = $xc + $cellSize[0]/2*$ratio[0];
            $y2 = $yc + $cellSize[1]/2*$ratio[1];
        }
        $xr = $cellSize[0] * $cell["round"][0];
        $yr = $cellSize[0] * $cell["round"][1];
        //
        $group = new SVGGroup();
        $group->setAttribute('fill', $color);
        $id = "canvasGroup".$x."x".$y;
        $idList []= $id;
        $rect = new SVGRect($x1, $y1, $x2-$x1 , $y2-$y1, $xr, $yr);
        $rect->setAttribute("id", $id);
        $group->addChild($rect);
        $doc->addChild($group);
        $x += $cellSize[0] * $cell["scale"][0];
    }
    $y += $cellSize[1] * $cell["scale"][1];
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
