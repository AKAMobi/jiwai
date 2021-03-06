<?php
if (isset($_GET['legacy'])) {
	include 'legacy.php';
	die();
}

$source = isset($_GET['source']) ? (int)$_GET['source'] : 0;

$count_def = 5;
$count_min = 1;
$count_max = 10;
$count = isset($_GET['count']) ? (int)$_GET['count'] : $count_def;
if ($count<$count_min) $count = $count_min;
if ($count>$count_max) $count = $count_max;

$modes = array('line', 'bar', 'block');
$mode = isset($_GET['mode']) ? $_GET['mode'] : 1;
$mode = isset($modes[$mode]) ? $mode : 1;

$width_def = array(320, 400, 180);
$width_min = array(100, 300, 160);
$width_max = array(800, 800, 800);
$width = isset($_GET['width']) ? (int)$_GET['width'] : $width_def[$mode];
if ($width<$width_min[$mode]) $width = $width_min[$mode];
if ($width>$width_max[$mode]) $width = $width_max[$mode];

$height_def = array(18, 400, 180);
$height_min = array(16, 300, 160);
$height_max = array(24, 600, 240);
$height = isset($_GET['height']) ? (int)$_GET['height'] : $height_def[$mode];
if ($height<$height_min[$mode]) $height = $height_min[$mode];
if ($height>$height_max[$mode]) $height = $height_max[$mode];

$colors_def = array('FFFFFF', 'D05D00', '000000', 'd05d00', '', '', 'FFCC54', 'FFB121'); //bg border body from via time gradient inner-border
for($i=0;$i<8;$i++) {
	$v = 'color'.$i;
	$$v = isset($_GET[$v]) ? color_check($_GET[$v]) : $colors_def[$i];
}
//$color1 = $color0;
if (!$color3) $color3 = $color2;
if (!$color4) $color4 = $color3;
if (!$color5) $color5 = $color4;

$margin_def = 8;
$margin_min = 0;
$margin_max = 20;
$margin = isset($_GET['margin']) ? (int)$_GET['margin'] : $margin_def;
if ($margin<$margin_min) $margin = $margin_min;
if ($margin>$margin_max) $margin = $margin_max;

$padding_def = 6;
$padding_min = 0;
$padding_max = 20;
$padding = isset($_GET['padding']) ? (int)$_GET['padding'] : $padding_def;
if ($padding<$padding_min) $padding = $padding_min;
if ($padding>$padding_max) $padding = $padding_max;

$bgimage = isset($_GET['bgimage']) ? $_GET['bgimage'] : '';

////////////////////////////////////////////////////////////////////////////
$font_size = 9;
$icon_size = $mode==1 ? 96 : 48;
$grad_size = $mode==1 ? 100 : 66;
require_once 'data.php';
$data = $mode==1 ? getOwnTimeline(1, 96) : ($source ? getFriendsTimeline($count) : getOwnTimeline($count));
$color00 = $color0;
if ($color6 && $color6!=$color0) $color00 = null;
if ($bgimage) {
	$color00 = null;
	$bg = new JWImageCanvas($bgimage);
	$bgmode = JWImageCanvas::TILE_REPEATX; // | JWImageCanvas::TILE_REPEATY
}

function color_check($s, $default = '000000') {
	return $s=='' ? null : ((eregi('^[0-9a-f]{6}$', $s)) ? $s : $default);
}

//error_reporting(E_ALL ^ E_NOTICE);
header('Last-Modified: '.date(DATE_RFC822));
header('Expires: '.date(DATE_RFC822, time()+3600*24*365*10));
header('Pragma: public');
header("cache-control: max-age=259200");

switch($mode) {
	case 0:
		$v = new JWImageCanvas(strlen(serialize($data))*10, 20, $color00); //guess the width
		$v->setFont($font_size);
		foreach ($data as $d) {
			if ($source) $v->text($d['from'].': ', $color3);
			$v->text($d['body'].' ', $color2);
			$v->text($d['time'].' ', $color5);
			$v->text($d['via'].'   ', $color4);
		}
		$a = $v->getWrittenArea();
		$w = $a[0] + $width - $font_size;
		$h = $a[1];
		$y = ($height - $h) / 2;
		$x = $width - $font_size;
		//$v->outputGIF(); die();
		$gif = new JWGIFAnimation();
		$m = new JWImageCanvas($width, $height);
		while ($x + $w > $font_size + $width) {
			if ($bgimage) $m->tileImage($bg, $bgmode); else //CPU costy
			$m->clear($color0);
			if ($color6 && $color6!=$color0) $m->gradient($color6, $color0);
			//imagerectangle($m->image, 0, 0, $width-1, $height-1, $m->color($color1));
			$cw = $w;
			if ($x<1) {
				$dx = 1;
				$sx = 1-$x;
				$cw -= 1-$x;
			} else {
				$dx = $x;
				$sx = 0;
			}
			if ($dx+$cw>=$width-1) $cw = $width - 1 - $dx;
			imagecopymerge($m->image, $v->image, $dx, $y, $sx, 0, $cw, $h, 100);
			if ($color1) $m->border($color1);
			$gif->addFrame($m->image);
			$x -= $font_size*1;
		}
		//echo strlen($gif->finish());die();
		header('Content-type: image/gif');
		//imagegif($m); die();
		echo $gif->finish();
		break;
	case 1:
		$d = $data[0];
		$w = $width - $margin - 2 - $icon_size - 2 - $padding - $margin;
		$h = round(strlen(serialize($data))*8/($w-$icon_size)*20);
		if ($h>1024) $h=1024;
		$v = new JWImageCanvas($w, $h, $color00);
		$v->text($d['body'].' ', $color2);
		$v->text($d['time'].' ', $color5);
		$v->text($d['via'], $color4);
		$a = $v->getWrittenArea();
		$h = $a[1];
		$b = new JWImageCanvas('../../images/gadget-banner.png');
		$m = new JWImageCanvas($width, max($h+$padding+32, 2+$icon_size+2)+$margin*4+$b->height, $color0);
		if ($color6 && $color6!=$color0) $m->gradient($color6, $color0, $grad_size);
		if ($bgimage) $m->tileImage($bg, $bgmode);
		$m->setTextArea($margin+2+$icon_size+2+$padding, $margin*2, -$margin);
		$m->setFont(14);//, '../fonts/msyh.ttf');
		$m->text($d['from'].' ', $color2);
		$m->newLine();
		$m->moveCursor(0, 2);
		$m->setFont(9);//, '../fonts/msyh.ttf');
		$m->text($url, $color2);
		//$m->newLine();
		$a = $m->getWrittenArea();
		$a = $a[1];
		$m->putImage($b, $m->width - $b->width - $margin, $m->height - $b->height - $margin);
		$m->putImage($v, $margin+2+$icon_size+2+$padding, $a+$margin*3, $w, $h);
		$m->rect($margin, $margin, $icon_size+4, $icon_size+4, $color1);
		$m->putImage(new JWImageCanvas($d['icon']), $margin+2, $margin+2);
		if ($color1) $m->border($color1);
		$m->outputPNG();
		break;
	case 2:
		if ($source) {/*
			$w = $width - $margin - $margin;
			$v = new JWImageCanvas($w, round(strlen(serialize($data))*8/($w-$icon_size)*40), $color00); //guess the height
			$v->setFont($font_size);
			$v->setTextArea(2 + $icon_size + 2 +$padding, 0);
			$lines = 0;
			$h = -999;
			foreach ($data as $d) {
				if ($lines++) {
					$v->newLine();
					$v->moveCursor(0, $padding);
				}
				$a = $v->getWrittenArea();
				$last = $h;
				$h = $a[1] - $v->getFontHeight();
				if (($h-$last) < 2 + $icon_size + 2 + $padding) {
					$v->moveCursor(0, 2 + $icon_size + 2 + $padding - ($h-$last));
					$a = $v->getWrittenArea();
					$h = $a[1] - $v->getFontHeight();
				}
				$a = $v->getWrittenArea();
				$v->rect(0, $a[1] - $v->getFontHeight(), $icon_size+4, $icon_size+4, $color1);
				$v->putImage(new JWImageCanvas($d['icon']), 2, 2 + $a[1] - $v->getFontHeight());
				$v->text($d['from'].': ', $color3);
				$v->text($d['body'].' ', $color2);
				$v->text($d['time'].' ', $color5);
				$v->text($d['via'], $color4);
			}
			$a = $v->getWrittenArea();
			$last = $h;
			$h = $a[1] - $v->getFontHeight();
			if (($h-$last) < 2 + $icon_size + 2 + $padding) {
				$v->moveCursor(0, 2 + $icon_size + 2 + $padding - ($h-$last));
				$a = $v->getWrittenArea();
				$h = $a[1] - $v->getFontHeight();
			}
			//$v->outputGIF(); die();
			$m = new JWImageCanvas($width, $h+$margin+$margin, $color0);
			if ($color6 && $color6!=$color0) $m->gradient($color6, $color0, $grad_size);
			if ($bgimage) $m->tileImage($bg, $bgmode);
			$m->putImage($v, $margin, $margin);
			if ($color2) $m->border($color2);
			$m->outputPNG();*/
		} else {
			$w = $width - $margin - $margin;
			$h = round(strlen(serialize($data))*8/($w-$margin*2)*16);
			if ($h>4096) $h = 4096;
			$v = new JWImageCanvas($w, $h, $color00); //guess the height
			$v->setTextArea($padding, 0, $w - $padding * 2);
			$v->setFont($font_size);
			$lines = 0;
			foreach ($data as $d) {
				if ($lines++) {
					$v->newLine();
					$v->moveCursor(0, $padding);
				}
				$wa = $v->textCursorTop;
				$v->moveCursor(0, $padding);
				$v->text($d['body'].' ', $color2);
				$v->text($d['time'].' ', $color5);
				$v->text($d['via'], $color4);
				$v->moveCursor(0, $padding);
				$wb = $v->getWrittenArea();
				$wb = $wb[1];
				$v->rect(0, $wa, $w, $wb-$wa, $color7);
			}
			$a = $v->getWrittenArea();
			$h = $a[1];
			//$v->outputPNG(); die();
			$b = new JWImageCanvas('../../images/gadget-banner.png');
			$m = new JWImageCanvas($width, $margin+$h+$padding*2+2+$icon_size+2+$margin*2 + $b->height, $color0);
			if ($color6 && $color6!=$color0) $m->gradient($color6, $color0, $grad_size);
			if ($bgimage) $m->tileImage($bg, $bgmode);
			$m->rect($margin, $margin, $icon_size+2, $icon_size+2, $color1);
			$m->putImage(new JWImageCanvas($d['icon']), $margin+1, $margin+1);
			$m->setTextArea($margin+2+$icon_size+2+$padding, $margin+4, -$margin);
			$m->setFont(12);//, '../fonts/msyh.ttf');
			$m->text($d['from'], $color2);
			$m->newLine();
			//$m->moveCursor(0, 4);
			$m->setFont(9);//, '../fonts/msyh.ttf');
			$m->text($url, $color2);
			$a = $m->getWrittenArea();
			$hh = $a[1] + $margin*2;
			$a = $margin+2+$icon_size+2+$padding;
			if ($hh<$a) $hh = $a;
			$m->hline($hh, $color1);
			$m->putImage($b, ($m->width - $b->width)/2, $m->height-$b->height-$margin);
			$m->putImage($v, $margin, $hh + $margin, $w, $h, true);
			if ($color1) $m->border($color1);
			$m->outputPNG();
		}
		break;
}
JWDB::Close();
?>
