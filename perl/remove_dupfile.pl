#! /usr/bin/perl -w

use strict;
use warnings;
use Digest::SHA1;

sub get_filedigest {
    my ($filename) = @_;
    open(FILE, $filename);
    my $ctx = Digest::SHA1->new;
    $ctx->addfile(*FILE);
    my $digest = $ctx->hexdigest;
    close(FILE);
    return $digest;
}

sub get_filesize {
    my ($filename) = @_;
    my @s = stat($filename);
    return $s[7];
}

my $dir = opendir DIR,'.';
foreach my $f (sort readdir(DIR)) {
    if ($f =~ /(.+)\.([\d])$/) {
	my $orig_file = $1;
	if (-f $orig_file) {
	    my $orig_file_size = get_filesize($orig_file);
	    my $f_size = get_filesize($f);
	    if ($orig_file_size > $f_size) {
		print "unlink $f \n";
		unlink $f;
	    } elsif ($orig_file_size < $f_size) {
		print "rename $f $orig_file\n";
		rename $f, $orig_file;
	    } else {
		my $orig_file_digest = get_filedigest($orig_file);
		my $f_digest = get_filedigest($f);
		if ($orig_file_digest eq $f_digest) {
		    print "unlink $f (same digest)\n";
		    unlink $f;
		}
	    }
	} else {
	    print "rename $f $orig_file\n";
	    rename $f, $orig_file;
	}
    }
}
