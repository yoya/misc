<?php

function usage() {
    echo "usage: macbincut.php [-d] <mbfile> [<mbfile2> [...]]\n";
    echo "    -d: dry-run mode\n";
}

if ($argc < 2) {
    usage();
    exit (1);
}

if ($argv[1] === '-d') {
    $dryrun = true;
    $i = 2;
} else {
    $dryrun = false;
    $i = 1;
}

for ( ; $i < $argc ; $i++) {
    $file = $argv[$i];
    if (is_readable($file) === false) {
        echo "ERROR: $file is unreadable\n";
        continue;
    }
    macbincut($file, $dryrun);
}

function macbincut($file, $dryrun) {
    $data = file_get_contents($file);
    $fileLength = strlen($data);
    if ($fileLength < 0x80) {
        echo "ERROR: fileLength:$fileLength < 0x80\n";
        return false;
    }
    $filenameLength = ord($data[1]);
    $filename = substr($data, 2, $filenameLength);
    if (ctype_print($filename) === false) {
        echo "ERROR: filename:$filename";
        return false;
    }
    if (substr($data, 2 +  $filenameLength, 1) !== "\0") {
        echo "ERROR: filename terminate must be zero\n";
        return false;
    }
    echo "cutting $file to $filename\n";

    if ($dryrun === false) {
        $newfile =  dirname($file).'/'.$filename;
        unlink($file);
        file_put_contents($newfile, substr($data, 0x80));
     }
}