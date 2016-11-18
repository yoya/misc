<?php

// arguments parser

$options = getopt("f:p:");

function usage() {
    echo "Usage: php pngextract.php -f <file> -p <prefix>".PHP_EOL;
    echo "ex) php pngextract.php -f input.dat -p output".PHP_EOL;
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

$pngSignature = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";

function searchText($fp, $needle) {
    $len = strlen($needle);
    $buff = fread($fp, $len);
    if ($buff === false)  {
        return false;
    }
    while (($buff !== $needle)) {
        if (feof($fp)) {
            return false;
        }
        $c = fread($fp, 1);
        if ($c === false) {
            return false;
        }
        $buff = substr($buff, 1) . $c;
    }
    return true;
}

function readUI32($str) { //BigEndian
    $a = unpack("N", $str);
    return $a[1];
}

// main routine

$fp = fopen($file, "rb");
if ($fp === false) {
    usage();
    exit(1);
}

$offset = 0;
for ($i = 0 ; searchText($fp, $pngSignature); $i++) {
    $outputFilename = sprintf("%s%06d.png", $prefix, $i);
    echo "$outputFilename\n";
    $fp_out = fopen($outputFilename, "wb");
    fwrite($fp_out, $pngSignature);
    $iendFound = false;
    while (($len_name = fread($fp, 8)) !== false) {
        fwrite($fp_out, $len_name);
        $len = readUI32(substr($len_name, 0, 4));
        $name = substr($len_name, 4, 4);
        $payload_crc = fread($fp, $len + 4);
        if ($payload_crc === false) {
            break;
        }
        fwrite($fp_out, $payload_crc);
        if ($name === "IEND") {
            $iendFound = true;
            break;
        }
    }
    if (! $iendFound) {
        echo "Incomplete PNG file found (offset:$offset)\n";
        break;
    }
    fclose($fp_out);
}

echo "OK\n";

fclose($fp);

exit(0);
