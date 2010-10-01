<?php

// main routine

require_once dirname(__FILE__).'/YSwf.php';

$options = getopt("f:d");

if (! isset($options['f'])) {
    echo "Usage: php swfstrip.php -f <swf_file> [-d]".PHP_EOL;
    exit(1);
}
$swf = new YSwf();
$swfdata = file_get_contents($options['f']);
$swf->input($swfdata);
// $swf->set_image_checksum($swfdata);
$swf->set_shape_checksum($swfdata);
if (isset($options['d'])) {
    $swf->unique_shape_by_checksum(array('debug' => true));
} else {
    $swf->unique_shape_by_checksum(array());
    echo $swf->output();
}
