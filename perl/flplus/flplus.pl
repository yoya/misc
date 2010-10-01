#! /usr/local/bin/perl -w
#
# (c) Upaupa@Shiva.FFXI

use strict;
use LWP::UserAgent;
use HTTP::Cookies;

# user customize

my $agent = 'MSIE/6.0 (Windows NT 5.1)';
my $cookie_file = 'flplus.txt';
my $lang = 'ja';

# constant variable
use constant URL_VIEW_MAIN =>
    'https://secure.playonline.com/polapps/s/polplus.friendlist.view.main';

# global variable

my $user_agent = undef;

# sub routine

sub Login {
    my ($userid, $passwd) = get_pol_account();
    my $url = URL_VIEW_MAIN;
    my %params = ('action' => 'li',
		  'lang' => $lang,
		  'PolId' => $userid,
		  'PolPassword' => $passwd);
    return HttpPost($url, %params);
}

sub HandleList {
    my $url = URL_VIEW_MAIN . '?action=hv&lang=' . $lang;
    return HttpGet($url);
}

sub ChangeHandle {
    my $hnNo = shift;
    my $url = URL_VIEW_MAIN;
    my %params = ('action' => 'hv',
		  'lang' => $lang,
		  'hnNo' => $hnNo);
    return HttpPost($url, %params);
};
sub FriendList {
    my $url = URL_VIEW_MAIN . '?action=fr&lang=' . $lang;
    return HttpGet($url);
};

# utility routine

sub HttpUserAgent {
    if(defined($user_agent)) {
	return $user_agent;
    }
    $user_agent = LWP::UserAgent->new();
    
    push(@{ $user_agent->{'requests_redirectable'} }, 'POST');
    $user_agent->agent($user_agent);
    
    my $cookie_jar = HTTP::Cookies->new(file => $cookie_file, autosave => 1);
    $user_agent->cookie_jar($cookie_jar);
    return $user_agent;
}

sub HttpPost {
    my ($url, %params) = @_;
    my @param_peers = ();
    foreach my $key (keys %params) {
	push @param_peers, "$key=" . $params{$key};
    }
    my $param_peers = join('&', @param_peers);
    my $ua = HttpUserAgent();
    my $req = HTTP::Request->new(POST => $url);
    $req->content_type('application/x-www-form-urlencoded');
    $req->content($param_peers);
    
    my $res = $ua->request($req);
    return $res->as_string;
}

sub HttpGet {
    my $url = shift;
    my $req = HTTP::Request->new(GET => $url);
    my $ua = HttpUserAgent();
    my $res = $ua->request($req);
    return $res->as_string;
}

1;
