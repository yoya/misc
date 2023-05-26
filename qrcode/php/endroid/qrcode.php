<?php

// declare(strict_types=1);
 
require_once('../vendor/autoload.php');

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;



$result = Builder::create()
    ->writer(new PngWriter())
    ->writerOptions([])
        //    ->data('httpCustom QR code contents')
    ->data('https://pwiki.awm.jp/~yoya/')
    ->encoding(new Encoding('UTF-8'))
    ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
    ->size(300)
    ->margin(10)
    ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
    ->logoPath('../../img/26040-64.png')
    ->labelText('https://pwiki.awm.jp/~yoya/')
    ->labelFont(new NotoSans(18))
    ->labelAlignment(new LabelAlignmentCenter())
    ->validateResult(false)  // khanamiryan/qrcode-detector-decoder
    ->build();

$qrcode = $result->getDataUri();

if(in_array($_POST['output_type'], ['png', 'jpg', 'gif'])){
    $qrcode = '<img src="'.$qrcode.'" />';
}
elseif($_POST['output_type'] === 'text'){
    $qrcode = '<pre style="font-size: 75%; line-height: 1;">'.$qrcode.'</pre>';
}
elseif($_POST['output_type'] === 'json'){
    $qrcode = '<pre style="font-size: 75%; overflow-x: auto;">'.$qrcode.'</pre>';
}

send_response(['qrcode' => $qrcode]);

function send_response(array $response){
	header('Content-type: application/json;charset=utf-8;');
	echo json_encode($response);
	exit;
}

