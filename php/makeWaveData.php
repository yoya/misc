<?php

function makeWaveData($data, $nChannel, $sampleBits, $sampleRate) {
    $blockSize = $nChannel*($sampleBits/8);
    $bytePerSecs = $blockSize*$sampleRate;
    $formatId = 1; // linear PCM
    $fmtChunk = 'WAVEfmt ';
    $fmtChunk .= pack("V", 16); // fmt chunk length
    $fmtChunk .= pack("v", $formatId);
    $fmtChunk .= pack("vVV", $nChannel, $sampleRate, $bytePerSecs);
    $fmtChunk .= pack("vv", $blockSize, $sampleBits);
    // chunk
    $dataChunk = 'data'.pack('V', strlen($data)).$data;
    $riffLength = strlen($fmtChunk)+strlen($dataChunk);
    return 'RIFF'.pack("V", $riffLength).$fmtChunk.$dataChunk;
}
