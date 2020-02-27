<?php

$hist = [];

foreach (file($argv[1]) as $line) {
    foreach (explode(" ", $line) as $word) {
        if ((ctype_print($word))) {
            if (isset($hist[$word])) {
                $hist[$word]++;
            } else {
                $hist[$word] = 1;
            }
        }
    }
}

//arsort($hist);
ksort($hist);
foreach ($hist as $word => $count) {
    echo "$word => $count\n";
}
