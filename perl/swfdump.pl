#! /usr/bin/perl

require 'SWF.pm';

use strict;
use warnings;
use LWP::Simple;
use SWF;


if (@ARGV != 1) {
    print "Usage: swfdump.pl <swffile>\n";
    exit (1);
}

my ($swffile) = @ARGV;
my $swfdata = get('file://'.$swffile);

my $swf = new SWF();
$swf->input($swfdata);

$swf->dump();





