<?php

function usage() {
    echo "php zlibcompress.php [-u] <zlib file>\n";
}

$uncompress = false;

if ($argc < 2) {
    usage();
    exit (1);
}

if ($argv[1] === "-u")  {
    $uncompress = true;
    if ($argc < 3) {
        $filename = "php://stdin";
    } else {
        $filename = $argv[2];
    }
} else {
    if ($argc < 2) {
        $filename = "php://stdin";
    } else {
        $filename = $argv[1];
    }
}
$data = file_get_contents($filename);

if ($uncompress) {
    echo gzuncompress($data);
} else {
    echo gzcompress($data);
}

exit (0);
