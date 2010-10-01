<?php

// main routine

require_once dirname(__FILE__).'/YSwf.php';

$options = getopt("hf:");

if (! isset($options['f'])) {
    echo "Usage: php swfdump.php -f <swf_file> [-h] ".PHP_EOL;
    exit(1);
}

$swffile = $options['f'];
if ($swffile === '-') {
    $swffile = 'php://stdin';
}

$swf = new YSwf();
$swfdata = file_get_contents($swffile);
$swf->input($swfdata);
$swf->set_image_checksum($swfdata);
$swf->set_shape_checksum($swfdata);
$opts = array();
if (isset($options['h'])) {
    $opts['hexdump'] = true;
}

$swf->dump($opts);
