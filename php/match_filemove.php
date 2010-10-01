<?php

/*******
  ヘッダをパターン指定してフォルダ振り分け
  Maildir の移動に…
  ex) match_filemove [-d] .INBOX/cur "Date:.*Nov 2009" .INBOX.2009.11/cur\n";
 *******/

function usage() {
	echo "Usage: match_filemove [-d] <from_dir> <patterm1> <to_dir1> [<patterm2> <to_dir2> [...]]\n";
}

$dry_run = false;

$args = array_slice($argv, 1);

if ($args[0] == '-d') {
	$dry_run = true;
	array_shift($args);
}


$from_dir = array_shift($args);

if (! is_dir($from_dir)) {
	echo "from_dir is not dir\n";
	usage();
	exit(0);
}

if ((count($args) < 2) || (count($args)%2 != 0)) {
	echo "invalid args count\n";
	usage();
	exit(1);
}

$patterns = array();
$to_dirs  = array();

$pattern2dir = array();

while (count($args) >= 2) {
	list($pattern, $to_dir) = $args;
	if (! is_dir($to_dir)) {
		echo "from_dir or to_dir is not dir\n";
		usage();
		exit(0);
	}
	$pattern2dir[$pattern] = $to_dir;
	array_shift($args);
	array_shift($args);
}



$dirh = opendir($from_dir);

while (($file = readdir($dirh)) !== false) {
	$from_file = "$from_dir/$file";
	if (is_dir($from_file)) {
		continue; // skip
	}
	if ($to_dir = header_match($from_file, $pattern2dir)) {
		echo $line;
		$to_file = "$to_dir/$file";
		echo $to_file."\n";
		if ($dry_run) {
			continue; // skip
		}
		if (rename($from_file, $to_file) === false) {
			echo "ERROR: rename failed: $from_file, $to_file\n";
			exit(1);
		}
	}

}

function header_match($file, $pattern2dir) {
	foreach(file($file) as $line) {
		if ($line[0] == "\n") {
			return false;
		}
		foreach ($pattern2dir as $pattern => $dir) {
			if (preg_match("/$pattern/", $line, $matches)) {
				return $dir;
			}
		}
	}
	return false;
}
