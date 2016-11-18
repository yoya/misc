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

function readUI32($data, $offset) { //BigEndian
    $a = unpack("N", substr($data, $offset, 4));
    return $a[1];
}

// main routine

$data = file_get_contents($file);
$dataLen = strlen($data);

$offset = 0;
for ($i = 0 ; true ; $i++) {
    $startOffset = strpos($data, $pngSignature, $offset);
    if ($startOffset === false) {
        break;
    }
    $offset = $startOffset + strlen($pngSignature);
    $iendFound = false;
    while (($offset + 8) < $dataLen) {
        $len = readUI32($data, $offset);
        $sig = substr($data, $offset + 4 , 4);
        $offset += 4 + 4 + $len + 4; // len + sig + <payload> + crc
        if ($sig === "IEND") {
            $iendFound = true;
            break;
        }
    }
    if (! $iendFound) {
        echo "Incomplete PNG file found (offset:$offset)\n";
        break;
    }
    $outputdata = substr($data, $startOffset, $offset - $startOffset);
    $outputFilename = sprintf("%s%06d.png", $prefix, $i);
    echo "$outputFilename (offset:$startOffset)\n";
    file_put_contents($outputFilename, $outputdata);
    $offset += strlen($outputdata);
}

echo "OK\n";

exit(0);
