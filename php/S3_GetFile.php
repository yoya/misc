<?php

// ref) http://docs.aws.amazon.com/aws-sdk-php/guide/latest/service-s3.html
// ref) s3://bucket/file scheme > http://aws.amazon.com/jp/cli/

require_once('AWSSDKforPHP/aws.phar'); // PEAR

use Aws\S3\S3Client;

function S3_GetFile($url) {
    if (preg_match('/^s3:\/\/([^\/]+)\/(.+)/', $url, $matches) !== 1) {
        throw new Exception("Invalid S3 File URL:$url");
    }
    list($line, $bucket, $key) = $matches;

    $client = S3Client::factory(array('key'    => getenv('AWS_KEY'),
                                      'secret' => getenv('AWS_SECRET')));
    $result = $client->getObject(array('Bucket' => $bucket, 'Key' => $key));

    return (string) $result['Body'];
}
