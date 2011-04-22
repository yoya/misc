package SWF;

require 'BitIO.pm';
use strict;
use warnings;
use BitIO;

sub new {
    my $class = shift;
    my $self = { tags => [] };
    return bless $self, $class; 
}

sub input {
    my ($self, $swfdata) = @_;
    my $bitio = new BitIO();
    $bitio->input($swfdata);
    my $signature = $bitio->getData(3);
    if ($signature ne 'FWS') {
        print STDERR "SWF::input: signature: $signature\n";
        exit(1); # XXX
    }
    # swf header
    $self->{signature} = $signature;
    my $version = 
    $self->{version} = $bitio->getByte();
    $self->{file_length} = $bitio->getBytesLE(4);
    # swf movie header
    $self->{frame_size} = $bitio->getRectangle();
    $self->{frame_rate} = $bitio->getBytesLE(2);
    $self->{frame_count} = $bitio->getBytesLE(2);
    # tag list
    while (1) {
        my $tag_and_length = $bitio->getBytesLE(2);
        my $code = $tag_and_length >> 6;
        my $length = $tag_and_length & 0x3f;
        if ($length == 0x3f) {
            $length = $bitio->getBytesLE(4);
        }
        my $content = $bitio->getData($length);
        my $tag = { code => $code, length => $length, content => $content};
        push @{$self->{tags}}, $tag;
        if ($code == 0) {
            last; # End Tag
        }
    }
}

sub stringRectangle {
    my $rect = shift;
    return "Xmin:".($rect->{Xmin}/20)." Xmax:".($rect->{Xmax}/20)." Ymin:".($rect->{Ymin}/20)." Ymax:".($rect->{Ymax}/20);
}


sub getTagName {
    my $tag_code = shift;
    my %tagName = (
        0 => 'End',
        1 => 'ShowFrame',
        2 => 'DefineShape',
        6 => 'DefineBitsJPEG',
        8 => 'JPEGTables',
        9 => 'SetBackgroundColor',
        12 => 'DoAction',
        26 => 'PlaceObject2',
        );
    if (exists $tagName{$tag_code}) {
        return $tagName{$tag_code};
    }
    return 'unknown';
}

sub dump {
    my ($self) = @_;
    # header
    print "signature: ".$self->{signature}."\n";
    print "version: ".$self->{version}."\n";
    print "file_length: ".$self->{file_length}."\n";
    # movie header
    print "frame_size: ".stringRectangle($self->{frame_size})."\n";
    print "frame_rate: ".$self->{frame_rate}/0x100."\n";
    print "frame_count: ".$self->{frame_count}."\n";
    # tag list
    foreach my $tag (@{$self->{tags}}) {
        my $tag_code = $tag->{code};
        my $tagName = getTagName($tag_code);
        print "tag code:$tag_code($tagName) length:".$tag->{length}."\n";
    }
}
