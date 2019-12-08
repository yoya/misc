<?php

/*
  Copyright (c) 2019/12/07 yoya@awm.jp
  HEIF container filter to apple like
  1) ftyp major brand must be "heic"
  2) constraint base_offset to 0
*/

function usage($msg) {
    echo $msg.PHP_EOL;
    echo "usage: php heif2apple.php <heifin> <heifout>".PHP_EOL;
}

if ($argc != 3) { usage("too few arguments: argc:$argc"); exit(1); }
$fp_in = fopen($argv[1], "rb");
$fp_out = fopen($argv[2], "wb");

if (!$fp_in) { usage("can't open file") ; exit(1); }
if (!$fp_out) { usage("can't create file") ; exit(1); }

$filter = new HEIF_Filter();
$filter->heif2apple($fp_in, $fp_out);

fclose($fp_in);
fclose($fp_out);

exit (0);

class HEIF_Filter {
    function heif2apple($in, $out, $boxLen = null) {
        while (is_null($boxLen) || (0 < $boxLen)) {
            if (! ($tmp = fread($in, 4))) {
                return ;
            }
            fwrite($out, $tmp);
            $len = unpack("N", $tmp)[1];
            $name = fread($in, 4);
            fwrite($out, $name);
            echo "len:$len name:$name".PHP_EOL;
            switch ($name) {
            case "meta":
                fwrite($out, fread($in, 4));  // version & flags
                $this->heif2apple($in, $out, $len);
                break;
            case "ftyp":
                $data = fread($in, $len - 8);
                fwrite($out, "heic");
                fwrite($out, substr($data, 4));
                break;
            case "iloc":
                $ver_flags = fread($in, 4);
                fwrite($out, $ver_flags);
                $version = ord($ver_flags[0]);
                echo "iloc box version:$version".PHP_EOL;
                $tmp = fread($in, 2);
                fwrite($out, $tmp);
                $offsetSize     = ord($tmp[0]) >> 4;
                $lengthSize     = ord($tmp[0]) & 0xF;
                $baseOffsetSize = ord($tmp[1]) >> 4;
                $indexSize      = ord($tmp[1]) & 0xF;
                $tmp = fread($in, 2);
                fwrite($out, $tmp);
                $itemCount = unpack("n", $tmp)[1];
                for ($i = 0; $i < $itemCount; $i++) {
                    fwrite($out, fread($in, 2));  // itemID
                    if ($version > 0) {
                        fwrite($out, fread($in, 2));  // constructionMethod
                    }
                    fwrite($out, fread($in, 2));  // dataReferenceIndex
                    $baseOffset = 0;
                    for ($boIdx = 0; $boIdx < $baseOffsetSize; $boIdx++) {
                        $baseOffset = $baseOffset*0x100 + ord(fread($in, 1));
                        fwrite($out, "\0");
                    }
                    echo "baseOffset:$baseOffset => 0".PHP_EOL;
                    $tmp = fread($in, 2);
                    fwrite($out, $tmp);
                    $extentCount = unpack("n", $tmp)[1];
                    if ($extentCount > 1000) {
                        throw new Exception("extentCount:$extentCount > 1000");
                    }
                    for ($eIdx = 0; $eIdx < $extentCount; $eIdx++) {
                        $extentOffset = 0;
                        for ($osIdx = 0; $osIdx < $offsetSize; $osIdx++) {
                            $extentOffset = $extentOffset*0x100 + ord(fread($in, 1));
                        }
                        echo "extentOffset:$extentOffset";
                        $extentOffset += $baseOffset;
                        echo " => $extentOffset".PHP_EOL;
                        if (2**(8*$offsetSize) <= $extentOffset) {
                            throw new Exception("2**(8*offsetSize:$offsetSize) <= extentOffset:$extentOffset");
                        }
                        for ($osIdx = 0; $osIdx < $offsetSize; $osIdx++) {
                            $shift = ($offsetSize - 1 - $osIdx) * 8;
                            $tmp = ($extentOffset >> $shift) & 0xFF;
                            fwrite($out, chr($tmp));
                        }
                        if ($version > 0) {
                            if ($indexSize > 0) {  // extentIndex
                                fwrite($out, fread($in, $indexSize));
                            }
                        }
                        fwrite($out, fread($in, $lengthSize));  // extentLength
                    }
                }
                break;
            default:
                if ($len === 1) {
                    while(!feof($in)) {
                        $data = fread($in, 0x1000000);
                        fwrite($out, $data);
                    }
                } else {
                    $data = fread($in, $len - 8);
                    fwrite($out, $data);
                }
                break;
            }
            $boxLen - $len;
        }
    }
}

