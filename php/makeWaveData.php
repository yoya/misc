<?php

function makeWaveData($data, $nChannel, $sampleBits, $sampleRate) {
    $blockSize = $nChannel*($sampleBits/8);
    $bytePerSecs = $blockSize*$sampleRate;
    $formatId = 1; // linear PCM
    $fmtChunk = 'WAVEfmt ';
    $fmtChunk .= pack("V", 16); // fmt chunk length
    $fmtChunk .= pack("v", $formatId);
    $fmtChunk .= pack("vVV", $nChannel, $sampleRate, $bytePerSecs);
    $fmtChunk .= pack("vv", $blockSize, $sampleBits); // align
    // chunk
    $dataChunk = 'data'.pack('V', strlen($data)).$data;
    $riffLength = strlen($fmtChunk)+strlen($dataChunk);
    return 'RIFF'.pack("V", $riffLength).$fmtChunk.$dataChunk;
}

if (realpath($argv[0]) == __FILE__) {
    $sampleRate = 44100; // CD quality
    $nChannel = 2; // 1:monoral, 2:stereo
    $toneA = 440;
    $sampleBits = 16; // 8 or 16
    $period = 1.0; // seconds;
    $theta = 0;
    $theta_delta = $toneA * 2 * M_PI / $sampleRate;
    $amp = 0x1000;
    $totalSamples = $sampleRate * $period;
    $data = '';
    for ($i = 1 ;$i < $sampleRate * $period; $i++) {
        switch ($nChannel) {
        case 1: // mono
            // signed 16-bit array (little endian)
            $v = 0x4000 * sin($theta);
            $data .= pack('s', $v);
            break;
        case 2: // stereo
            $v1 = 0x4000 * sin($theta);
            $v2 = 0x4000 * cos($theta/2);
            $data .= pack('ss', $v1, $v2);
        default: // etc
            for ($c = 0; $c < $nChannel; $c++) {
                $v = 0x4000 * sin($theta);
                $data .= pack('s', $v);
            }
        }
        $theta += $theta_delta;
    }
    echo makeWaveData($data, $nChannel, $sampleBits, $sampleRate);
}
