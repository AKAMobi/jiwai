<?php
if(!defined('TPL_COMPILED_DIR')) define('TPL_COMPILED_DIR',dirname(__FILE__).'/compiled');
if(!defined('TPL_TEMPLATE_DIR')) define('TPL_TEMPLATE_DIR',dirname(__FILE__).'/template');
require_once('../../../jiwai.inc.php');
require_once('./function.php');

function backToGet($string=null){
	setTips( $string );
	Header('Location: statusexam');
	exit;
}

if( $_POST ){
	$delete = $allow = $cb = null;
	extract( $_POST, EXTR_IF_EXISTS );
	if ( empty($cb) ) {
		backToGet();
	}
		
	if ( $delete ){
		JWStatusQuarantine::DeleteByIds( $cb ); 	
		backToGet("删除成功～");
	}
	
	if ( $allow ){
		JWStatusQuarantine::AllowByIds( $cb ); 	
		backToGet("审核通过～");
	}
}

$statusQuarantine = JWStatusQuarantine::GetStatusQuarantine(JWStatusQuarantine::DEAL_NONE, 20);
$dictFilter = JWFilterConfig::GetDictFilter();

$render = new JWHtmlRender();
$render->display("statusexam", array(
			'menu_nav' => 'statusexam',
			'statusQuarantine' => $statusQuarantine,
			'dictFilter' => &$dictFilter,
			));
?>