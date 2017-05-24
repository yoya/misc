<?php

// arguments parser

$options = getopt("f:p:");

function usage() {
    echo "Usage: php jpegextract.php -f <file> -p <prefix>".PHP_EOL;
    echo "ex) php jpegextract.php -f input.dat -p output".PHP_EOL;
}

if ((isset($options['f']) === false) ||
    (is_readable($options['f']) === false) ||
    (isset($options['p']) === false)) {
    usage();
    exit(1);
}

$file = $options['f'];
$prefix = $options['p'];

// some declare

$jpegSOI = "\xFF\xD8";
$jpegEOI = "\xFF\xD9";

function searchText($fp, $needle) {
    $len = strlen($needle);
    $buff = fread($fp, $len);
    if ($buff === false)  {
        return false;
    }
    $data = $buff;
    while (($buff !== $needle)) {
        if (feof($fp)) {
            return false;
        }
        $c = fread($fp, 1);
        if ($c === false) {
            return false;
        }
        $buff = substr($buff, 1) . $c;
        $data = $data . $c;
    }
    return $data;
}

// main routine

$fp = fopen($file, "rb");
if ($fp === false) {
    usage();
    exit(1);
}

$offset = 0;
for ($i = 0 ; searchText($fp, $jpegSOI) !== false; $i++) {
    $jpegdata = searchText($fp, $jpegEOF);
    if ($jpegdata === false) {
        return ;
    }
    $outputFilename = sprintf("%s%06d.jpg", $prefix, $i);
    echo "$outputFilename\n";
    $fp_out = fopen($outputFilename, "wb");
    fclose($fp_out);
}

echo "OK\n";

fclose($fp);

exit(0);
