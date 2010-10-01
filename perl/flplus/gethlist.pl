#! /usr/local/bin/perl -w
#
# (c) Upaupa@Shiva.FFXI

use strict;
require 'config.pl';
require 'flplus.pl';

MAIN: {
    Login();
    print HandleList();
    exit 0;
}

