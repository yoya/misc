<?php

list($prog, $filename1, $filename2) = $argv;

function readNumbers($filename) {
    $data1= [];
    foreach (file($filename) as $line) {
        foreach (explode(" ", trim($line)) as $d) {
            $data []= hexdec($d);
        }
    }
    return $data;
}

$data1 = readNumbers($filename1);
$data2 = readNumbers($filename2);

$n = count($data1);

assert($n == count($data2));

for ($i = 0 ; $i < $n ; $i++) {
    // linear interpolate
    $v = ( ($n-$i-1)*$data1[$i] + ($i+1)*$data2[$i] ) / $n;
    printf("%02x ", round($v));
    if (($i % 8) == 7) {
        echo PHP_EOL;
    }
}
