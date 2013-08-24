<?php

  /*
   * 2013/08/24- yoya@awm.jp
   */

$target_params = array('mode', 'act'); // Ethna

function usage() {
    echo "Usage: php logtimematching.php php.log access.log\n";
  }

if ($argc != 3) {
    usage();
}

list($progname, $phplog, $accesslog) = $argv;

$phptimeCount = array();

$phptimeAllCount = 0;

//$timeMax = 0;
//$timeMin = PHP_INT_MAX; // XXX

foreach (file($phplog) as $line) {
    if (preg_match('/\[([^\]]+)\] (.+)/', trim($line), $matches) > 0) {
        $time = strtotime($matches[1]);
        if (isset($phptimeCount[$time])) {
            $phptimeCount[$time]++;
        } else {
            $phptimeCount[$time] = 1;
        }
        $timestr = date('Y/m/d H:i:s', $time);
//        echo $timestr.PHP_EOL;
        $phptimeAllCount++;
//        $timeMax = ($time>$timeMax)?$time:$timeMax;
//        $timeMin = ($timeMin<$time)?$timeMin:$time;
    }
}

$queryMatchCount = array();
$queryUnmatchCount = array();
foreach (file($accesslog) as $line) {
    if (preg_match('/\[([^\]]+)\] \"GET \/\?([^"]+)\" (.+)/', trim($line), $matches) > 0) {
        $time = strtotime($matches[1]);
//        if (($time < $timeMin) || ($timeMax < $time)) {
//            continue;
//        }
        $query = $matches[2];
        parse_str($query, $params);
        $param_peerlist = array();
        $canonquery = '';
        foreach ($target_params as $key) {
            if (isset($params[$key])) {
                $param_peerlist []= $key."=".$params[$key];
            }
        }
        $canonquery = "/?".implode('&', $param_peerlist);
//        echo $canonquery.PHP_EOL;
        if (isset($phptimeCount[$time])) {
            if (isset($queryMatchCount[$canonquery])) {
                $queryMatchCount[$canonquery] ++;
            } else {
                $queryMatchCount[$canonquery] = 1;
            }
        } else {
            if (isset($queryUnmatchCount[$canonquery])) {
                $queryUnmatchCount[$canonquery] ++;
            } else {
                $queryUnmatchCount[$canonquery] = 1;
            }
        }
    }
}

$queryRatioTable = array();
foreach ($queryMatchCount as $query => $count) {
    if (isset($queryUnmatchCount[$query])) {
        $unmatchCount = $queryUnmatchCount[$query];
    } else {
        $unmatchCount = 0;
    }
    $ratio =  $count / ($count + $unmatchCount);
    $queryRatioTable[$query] = $ratio * $count; // count !!!
}

arsort($queryRatioTable, SORT_NUMERIC);


foreach ($queryRatioTable as $query => $ratio) {
    $count = $queryMatchCount[$query];
    echo "$ratio($count/$phptimeAllCount): $query".PHP_EOL;
}
