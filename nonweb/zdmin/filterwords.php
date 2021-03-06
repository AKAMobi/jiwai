<?php
require_once( dirname(__FILE__) . '/function.php');

$w = null;
extract($_POST, EXTR_IF_EXISTS);

$dictFileName = JWFilterConfig::GetDictFilename();

if( $w ) {
	$w = mb_convert_encoding($w, "GB2312", "UTF-8");
	file_put_contents($dictFileName, $w );
	setTips("新的辞典文件已经成功保存！");
	Header("Location: filterwords.php");
	exit;
}

$fr = file_get_contents( $dictFileName );
$fr = mb_convert_encoding($fr, "UTF-8", "GB2312");

$render = new JWHtmlRender();
$render->display("filterwords", array(
			'fresult' => $fr,
			'menu_nav' => 'filterwords',
			));
?>
