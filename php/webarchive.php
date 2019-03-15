<?php

function usage() {
    echo "Usage: php webarchive.php <target_url> # http:// https:// only ".PHP_EOL;
    echo "ex) php webarchive.php http://app.awm.jp".PHP_EOL;
}

if ($argc != 2)  {
    usage();
    exit (1);
}

$targetURL = $argv[1];

$permit_protocols = ["http://", "https://"];
$permit_protocol = null;
foreach ($permit_protocols as $proto) {
    if (strncmp($targetURL, $proto, strlen($proto)) === 0){
        $permit_protocol = $proto;
        break;
    }
}

if (is_null($permit_protocol)) {
    usage();
    exit (1);    
}

$calenderURL = "https://web.archive.org/__wb/sparkline?output=json&url=" . urlencode($targetURL);
//$pagedir = "web";
$pagedir = substr($targetURL, strlen($permit_protocol));
$pagedir = urlencode($pagedir);
$calenderFile = "$pagedir/sparkline.json";

if (! is_dir($pagedir)) {
    if (! mkdir($pagedir, 0755)) {
        echo "Can't directory $pagedir".PHP_EOL;
        exit (1);
    }
}

if (! is_file($calenderFile)) {
    echo $calenderURL.PHP_EOL;
    $calenderJSON = file_get_contents($calenderURL);
    file_put_contents($calenderFile, $calenderJSON);
}
echo $calenderFile.PHP_EOL;
$calenderJSON = file_get_contents($calenderFile);
$calenderInfo = json_decode($calenderJSON);

$first_ts = $calenderInfo->first_ts;
$last_ts = $calenderInfo->last_ts;
echo "Range: {$first_ts} => {$last_ts}".PHP_EOL;

$years = (array) $calenderInfo->years;
ksort($years);
foreach ($years as $year => $months) {
    foreach ($months as $month => $count) {
        if ($count <= 0) { continue; }  // skip
        $month++; // one origin
        $ret = fetch_month_archive($year, $month, $count);
        if (! $ret) {
            var_dump($ret);
            echo "failed: fetch_month_archive".PHP_EOL;
            exit (1);
        }
    }
}

function fetch_month_archive($year, $month, $count) {
    echo "Month:{$month}: count:{$count}".PHP_EOL;
    return fetch_month_archive_rec($year, $month, 1, 31, $count);
}

// Binary search
function fetch_month_archive_rec($year, $month, $day_start, $day_end, $count) {
    if ($day_start >= $day_end) {
        echo "day_start:$day_start >= day_end:$day_end";
        return 0;
    }
    echo "DayRange:$day_start-$day_end count:{$count}".PHP_EOL;
    $day = round(($day_start + $day_end) / 2);
    $foundURL = fetch_redirect($year, $month, $day);
    if ($n = preg_match('/web\/(\d{14})/', $foundURL, $matches) === 0) {
        return false;
    }
    $datekey = $matches[1];
    if ($n = preg_match('/web\/(\d{4})(\d{2})(\d{2})/', $foundURL, $matches) === 0) {
        return false;
    }
    list($dummy, $foundYear, $foundMonth, $foundDay) = $matches;
    if (($year !== $foundYear) || ($month !== $foundMonth)) {
        echo "outrange found: $year=>$foundYear, $month=>$foundMonth".PHP_EOL;
    }
    if (($foundDay < $day_start) || ($day_end < $foundDay)) {
        echo "outrange found: $foundDay < $day_start || $day_end < $foundDay".PHP_EOL;
        return 0;
    }
    fetch_page($foundURL, $datekey);
    $count--;
    if ($count == 0) {
        return true;
    }
    $leftDay = min($day, $foundDay);
    $rightDay = max($day, $foundDay);
    // start_dat - $leftDay - $rightDay - $end_day
    $left_found = fetch_month_archive_rec($year, $month, $day_start, $leftDay-1, $count);
    if (is_bool($left_found)) {
        return $left_found;
    }
    $count -= $left_found;
    $right_found = fetch_month_archive_rec($year, $month, $rightDay+1, $day_end, $count);
    $count -= $right_found;
    if (is_bool($right_found)) {
        return $right_found;
    }
    return 1 + $left_found + $right_found;
}

function fetch_redirect($year, $month, $day) {
    global $targetURL, $pagedir;
    $ymd = sprintf("%02d%02d%02d", $year, $month, $day);
    $datekey = $ymd."000000";
    $pagefile = "$pagedir/$datekey";
    if (is_file($pagefile)) {
        echo "File: $pagefile".PHP_EOL;
        return file_get_contents($pagefile);
    }
    $url = "https://web.archive.org/web/$datekey/$targetURL";
    echo "URL: $url".PHP_EOL;
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // mute curl print
    curl_setopt($ch, CURLOPT_HEADER, true);
    $ret = curl_exec($ch);
    if (! $ret) {
        echo "Error: $url".PHP_EOL;
        return false;
    }
    $info = curl_getinfo ($ch);
    curl_close($ch);
    $redirect_url = $info["redirect_url"];
    file_put_contents($pagefile, $redirect_url);
    return $redirect_url;
}

function fetch_page($url, $datekey) {
    global $pagedir;
    echo "Url: $url".PHP_EOL;
    $pagefile = "$pagedir/$datekey";
    if (is_file($pagefile)) {
        echo "File: $pagefile".PHP_EOL;
        return file_get_contents($pagefile);
    }
    echo $url.PHP_EOL;
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // mute curl print
    curl_setopt($ch, CURLOPT_HEADER, false);
    $html = curl_exec($ch);
    if (! $html) {
        echo "Error: $url".PHP_EOL;
        return false;
    }
    curl_close($ch);
    file_put_contents($pagefile, $html);
    return $html;
}