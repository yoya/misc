<?php

if ($argc !== 3) {
  echo "php md5incomplete.php swfed-0.60.tar.gz 13899229c30408082573e94f44a735b3\n";
  exit (1);
}

list($progname, $filename, $file_md5) = $argv;

$filedata = file_get_contents($filename);
$filelength = strlen($filedata);

echo "minus\n";
for ($i = $filelength ; $i > 0 ; $i--) {
    if (md5(substr($filedata, 0, $i)) === $file_md5) {
        echo "$i/$filelength\n";
        exit (0);
    }
}

echo "plus\n";
for ($i = 1 ; $i <= 256 ; $i++) {
    for ($c = 0 ; $c < 256 ; $c++) {
        if (md5(str_pad($filedata, $i, chr($c)))  === $file_md5) {
            echo "$filelength+$i/256 code=$c\n";
            exit (0);
        }
    }
}

echo "Not found\n";
