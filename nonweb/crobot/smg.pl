#!/usr/bin/perl -w

use strict;
use Data::Dumper;
use Text::Iconv;

my %channelMap = (
    'SHHAI1'    => '上海电视台新闻频道',
    'SHHAI2'    => '上海电视台第一财经频道',
    'SHHAI3'    => '上海电视台生活时尚频道',
    'SHHAI4'    => '上海电视台电视剧频道',
    'SHHAI5'    => '上海电视台体育频道',
    'SHHAI6'    => '上海电视台纪实频道',
    'SHHAI7'    => '上海电视台娱乐频道',
    'SHHAI8'    => '上海电视台艺术人文频道',
    'SHHAI9'    => '上海电视台外语频道',
    'SHHAI10'   => '上海电视台戏剧频道',
    'SHHAI11'   => '上海电视台哈哈少儿频道',
);

my %userMap = (
    'SHHAI1'    => 'smg1',
    'SHHAI2'    => 'smg2',
    'SHHAI3'    => 'smg3',
    'SHHAI4'    => 'smg4',
    'SHHAI5'    => 'smg5',
    'SHHAI6'    => 'smg6',
    'SHHAI7'    => 'smg7',
    'SHHAI8'    => 'smg8',
    'SHHAI9'    => 'smg9',
    'SHHAI10'   => 'smg10',
    'SHHAI11'   => 'smg11',
);

sub getTVGuideCacheByChannel {
    my $channel = shift;
    my $timestamp = `date +%Y%m%d -d "6 hours ago"`; chomp $timestamp;
    return "/tmp/smg/$channel.$timestamp.cache";
}

sub getTVGuideByChannel {
    my $channel = shift;
    my $url = getTVGuideUrlByChannel($channel);
    my @guide = ();
    my $raw = `wget -U "Googlebot" -q -O - $url`;
    my $today = `date +%Y-%m-%d`; chomp $today;
    my $tomorrow = `date +%Y-%m-%d -d tomorrow`; chomp $tomorrow;

    my $converter = Text::Iconv->new("gbk", "utf-8");
    my $converted = $converter->convert($raw);

    open HTTP, "<", \$converted;
    my ($roi, $across, $hourSoFar, $hourNow) = (0, 0, 0, 0);

    while (<HTTP>) {
        my ($time, $show) = ();
        chomp;
        if (m#<div\s+id="pg">#i) {$roi = 1;}
        next if ($roi eq 0);
        #$_ =~ s#<a\s+href=.*?>##gi;
        #$_ =~ s#<\/a>##gi;
        #$_ =~ s#<img\s+.*?>##gi;
        $_ =~ s#<img[^>]+>##gi;
        $_ =~ s#<a[^>]+>##gi;
        $_ =~ s#<\/a>##gi;
        $_ =~ s#<div\s+style.*?>##gi;
        $_ =~ s#<div[^>]+>##gi;
        $_ =~ s#<\/div>##gi;
        if (m#<li.*?>([^\s]+)\s+([^<>]*?)</li>#i) {
            ($time, $show) = ($1, $2);
            ($hourNow) = split(":", $1);
        } elsif (m#<div\s+id="pgrow">.*?<font.*?>([^<> ]+)<\/font>\s+<div.*?>([^<>]*?)\s+#i) {
            ($time, $show) = ($1, $2);
            ($hourNow) = split(":", $1);
        }
        if (($across eq 0) and ($hourNow < $hourSoFar)) {
            $across = 1;
        }
        next unless defined $time;
        $show =~ s#分集剧情##gi;
        $hourSoFar = $hourNow;
        if ($across eq 1) {
            push(@guide, "$tomorrow $time;$show;0");
        } else {
            push(@guide, "$today $time;$show;0");
        }
    }

    close HTTP;
    return @guide;
}

sub getTVGuideUrlByChannel {
    my $channel = shift;
    die "no channel specified" unless defined $channel;

    ##http://www.tvmao.com/program/SHHAI-SHHAI1-w4.html
    my $retstr = 'http://www.tvmao.com/program/SHHAI-0Channel0-w0indexOfWeek0.html';
    my $indexOfWeek = `date +%u`; chomp $indexOfWeek;
    $indexOfWeek = 7 if ($indexOfWeek eq 0);

    $retstr =~ s/0Channel0/$channel/si;
    $retstr =~ s/0indexOfWeek0/$indexOfWeek/si;

    return $retstr;
}

sub TVGuideFactory {
    my $channel = shift;
    die "no channel specified" unless defined $channel;

    my @tvguide = getTVGuideByChannel($channel);
    return @tvguide;
}

sub writeCache {
    my ($channel, @guide) = @_;

    my $cache = getTVGuideCacheByChannel($channel);

    open CACHE, ">$cache" or warn "$cache: $!";
    for my $entry (@guide) {
        chomp $entry;
        print CACHE $entry, "\n";
    }
    close CACHE
}

sub postTVGuide {
    my ($channel) = @_;
    die "no channel specified" unless defined $channel;

    my ($username, $password) = ($userMap{$channel}, $userMap{$channel} . 'epgdem1ma');

    my $cache = getTVGuideCacheByChannel($channel);

    open CACHE, "<$cache" or warn "$cache: $!";
    my @guide = <CACHE>;
    close CACHE;

    if (!@guide) {
        @guide = TVGuideFactory($channel);
        writeCache($channel, @guide);
    }

    my ($lower, $upper) = (60 * 15, 60 * 30);
    my $len = @guide;
    my %posted = ();
    for my $i (0 .. $len - 1) {
        my $entry = $guide[$i]; chomp $entry;
        my ($time, $show, $f) = split(/;/, $entry);
        $show =~ s#分集剧情##gi;
        my $tsNow = `date +%s`; chomp $tsNow;
        my $tsShow= `date +%s -d "$time"`; chomp $tsShow;
        my $tsDiff = int($tsShow - $tsNow);
        if ($tsDiff > $lower and $tsDiff < $upper and $f eq 0) {
            if (!defined $posted{$time}) {
                print "[INF]$channel $time $show $tsDiff\n";
                `curl -s -u "$username:$password" -Fstatus="$channelMap{$channel} $time $show" http://api.jiwai.de/statuses/update.json`;
                $posted{$time} = $show;
            } else {
                print "[DUP]$channel $time $show $tsDiff\n";
            }
            $guide[$i] = "$time;$show;1";
        }
    }
    writeCache($channel, @guide);
}

sub createAccountByChannel {
    my $channel = shift;
    die "no channel specified" unless defined $channel;

    my ($username, $password) = ($userMap{$channel}, $userMap{$channel} . 'epgdem1ma');

    `curl -s -Fname_screen=$username -Fpass=$password -Femail=$username\@jiwai.de -Fapikey=4107f1349979cc9ed2951fb82b6105d4 http://api.jiwai.de/account/new.json`;
}

for my $channel (keys %channelMap) {
    postTVGuide($channel);
}
