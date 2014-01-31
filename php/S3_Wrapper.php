<?php

require_once('AWSSDKforPHP/aws.phar'); // PEAR
use Aws\S3\S3Client;

$client = S3Client::factory(array('key'    => getenv('AWS_KEY'),
                                  'secret' => getenv('AWS_SECRET')));
$client->registerStreamWrapper();
