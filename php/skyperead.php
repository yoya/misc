<?php

/*
 * (c) 2013/08/14- yoya@awm.jp
 */

function usage() {
    echo "Usage: php skyperead.php <main.db>".PHP_EOL;
}

$save_dir = 'skypelog';
@mkdir($save_dir); // XXX

date_default_timezone_set('Asia/Tokyo'); // for date function

if ($argc < 2) {
    usage();
    exit (1);
}

$maindb = $argv[1];

try {
    $db = new SQLite3($maindb);
} catch (Exception $e) {
    usage();
    exit (1);
}

/*
 * get my account name
 */

$query = $db->query('SELECT skypename FROM Accounts');
$rows = $query->fetchArray();
$myname = $rows['skypename'];

/*
 * get topics & partner name
 */
$query = $db->query('SELECT timestamp,name,topic,activemembers,adder,friendlyname FROM Chats');

$topicTable = array();
$topicElapsedTimeTable = array();
$topicPrevTopicTable = array();
$topicPrevTimeTable = array();
$privateChatPartner = array();

for ($idx = 0; $rows = $query->fetchArray() ; $idx++) {
    $timestamp = $rows['timestamp'];
    $name = $rows['name'];
    $topic = $rows['topic'];
    $activemembers  = $rows['activemembers'];
    $adder = $rows['adder'];
    $friendlyname  = $rows['friendlyname'];
    if (count(explode(" ", $activemembers)) > 2) {
        if (ctype_space($topic)) continue; // skip
        if (isset($topicTable[$name])) {
            $elapsed = $timestamp - $topicPrevTimeTable[$name];
            if ($elapsed > $topicElapsedTimeTable[$name]) {
                $topicTable[$name] = $topicPrevTopicTable[$name];
                $topicElapsedTimeTable[$name] = $elapsed;
            }
            $topicPrevTopicTable[$name] = $topic;
            $topicPrevTimeTable[$name] = $time;
        } else {
            $topicPrevTopicTable[$name] = $topic;
            $topicTable[$name] = $topic;
            $topicPrevTimeTable[$name] = $timestamp;
            $topicElapsedTimeTable[$name] = 0;
        }
    } else {
        if ($adder !== $myname) {
            $privateChatPartner[$name] = $adder;
        }
    }
}

/*
 * get dispname
 */
$dispnameTable = array();
$query = $db->query('SELECT author,from_dispname FROM Messages');

for ($idx = 0; $rows = $query->fetchArray() ; $idx++) {
    $author = $rows['author'];
    $from_dispname  = $rows['from_dispname'];
    if ($from_dispname) {
        $dispnameTable[$author] = $from_dispname;
    }
}

/*
 * save topicmap
 */

$topicTablePeer = array();
foreach ($topicTable as $name => $topic) {
    $topicTablePeer []= "$name:$topic";
}
$data = implode(PHP_EOL, $topicTablePeer);
file_put_contents("$save_dir/topicmap.txt", $data);

/*
 * get all messages
 */

$query = $db->query('SELECT timestamp,chatname,from_dispname,body_xml FROM Messages');

for ($idx = 0; $rows = $query->fetchArray() ; $idx++) {
    $timestamp = $rows['timestamp'];
    $date = date('Y/m/d h:i:s', $timestamp);
    $chatname = $rows['chatname'];
    $from_dispname  = $rows['from_dispname'];
    $body_xml = $rows['body_xml'];
    
    if (isset($topicTable[$chatname])) {
        $filename = $topicTable[$chatname];
    } else {
        if (isset($privateChatPartner[$chatname])) {
            $adder = $privateChatPartner[$chatname];
        } else {
            $n = preg_match('/\#(.+)\/\$(.+);/', $chatname, $matches);
            if ($n === 1) {
                $adder = $matches[2];
            }
        }
        if (isset($dispnameTable[$adder])) {
            $filename = $dispnameTable[$adder];
        } else {
            $filename = $from_dispname;
//            echo "XXX: $chatname, $filename\n";
        }
    }
    $filename = trim($filename);
    $filename = strtr($filename, " /:", "___");
    $data = "$date $from_dispname:$body_xml".PHP_EOL;
    file_put_contents("$save_dir/$filename.txt", $data, FILE_APPEND);
}

$db->close();
