<?php

$filename = $argv[1];

$fp = fopen($filename, "rb");

$text = "";
$prev_c = null;

$cr = false;

$text .= ">>".PHP_EOL;

while (($c = fgetc($fp)) !== false) {
    if ($c === "\n") {
        $text .= " ";
    } else if ($c === " ") {
        if ($prev_c === " ") {
            // do nothing
        } else {
            if ($cr) {
                $text .= PHP_EOL."<<".PHP_EOL.PHP_EOL.">>".PHP_EOL;
                $cr = false;
            } else {
                $text .= " ";
            }
        }
    } else if ($c === ".") {
        $text .= $c;
        $cr = true;
    } else {
        if ($cr) {
            $text .= PHP_EOL."<<".PHP_EOL.PHP_EOL.">>".PHP_EOL;
            $cr = false;
        }
        $cr = false;
        $text .= $c;
    }
    $prev_c = $c;
}

echo $text;
