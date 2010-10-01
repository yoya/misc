#! /usr/local/bin/perl -w
#
# (c) Upaupa@Shiva.FFXI

use strict;
require 'config.pl';
require 'flplus.pl';

# main routine

MAIN: {
    Login();
    ChangeHandle(0);
    print FriendList();
    exit 0;
}
