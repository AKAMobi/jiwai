<?php
// enable gzip
ini_set('zlib.output_compression', 1);
ini_set('zlib.output_handler', 'On');

if(!defined('TPL_COMPILED_DIR')) 
	define('TPL_COMPILED_DIR', dirname(__FILE__).'/compiled' );
if(!defined('TPL_TEMPLATE_DIR')) 
	define('TPL_TEMPLATE_DIR', dirname(__FILE__).'/template' );

header('Content-Type: text/html;charset=UTF-8');
require_once( dirname(__FILE__).'/../../jiwai.inc.php' );

function buildUrl($url){
	if( $url == '/' && JWLogin::IsLogined() ){
		$url = '/wo/';
	}
	return $url;
	$sessionId = session_id();
	$relayUrl = 'http://m.jiwai.de' . $url;
	if( strpos( $relayUrl, '?' ) > 0 ) {
		$relayUrl .= "&PHPSESSID=$sessionId";
	} else {
		$relayUrl .= "?PHPSESSID=$sessionId";
	}
	return $relayUrl;
}

function getDisplayName($userInfo){
	return $userInfo['nameScreen'];
	return $userInfo['nameScreen'] == $userInfo['nameFull'] ?
			$userInfo['nameScreen'] :
			htmlSpecialChars( $userInfo['nameFull'] ) . '('.$userInfo['nameScreen'].')';
}

function buildReplyUrl($nameScreen){ 
	$user = JWUser::GetUserInfo( $nameScreen );
	if( false == empty( $user ) ) {
		$url = buildUrl( "/$user[nameUrl]/" );
		return "@<a href=\"$url\">".$nameScreen."</a> ";
	}
	return "@$nameScreen";
}

function get_session_var($name, $use_once=true)
{
	$ret_val = null;
	if ( isset($_SESSION[$name]) )
	{
		$ret_val = $_SESSION[$name];
		if ( true == $use_once ) 
		{
			unset( $_SESSION[$name] );
		}
	}

	return $ret_val;
}

function paginate($pagination, $url){
	$pageString= '<p align="right">';
	if( $pagination->isShowNewer() ){
		$pageString.= '4 <a href="' . JWPagination::BuildPageUrl($url, $pagination->GetNewerPageNo()).'" accesskey="4">上页</a>';
	}
	if( $pagination->isShowOlder() && $pagination->isShowNewer() ){
		$pageString.= '｜';
	}
	if( $pagination->isShowOlder()){
		$pageString.= '<a href="' . JWPagination::BuildPageUrl($url, $pagination->GetOlderPageNo()).'" accesskey="6">下页</a> 6';
	}
	$pageString.= '</p>';
	return $pageString;
}

function redirect($url = null){
	if ( null == $url ){
		if( isset( $_SERVER['HTTP_REFERER'] ) )
			$url = $_SERVER['HTTP_REFERER'];
		else
			$url = '/';
	}
	Header("Location: $url");
	exit;
}

function redirect_to_404()
{
	#JWSession::SetInfo('notice', '哎呀，叽歪没有找到这个页面！');
	JWTemplate::RedirectToUrl('/');
}

function actionop($idUser, $idOthers, $forFollow=false){

	$isArray = is_array( $idOthers ) ;

	settype( $idOthers, 'array' );
	$actions = JWSns::GetUserActions( $idUser, $idOthers );
	$ops = array();

	foreach( $actions as $idOther=>$one ){
		$actionString = null;
		foreach( $one as $action=>$v ){ 
			if( $v == false ) continue;
		
			/*
			 * for /wo/followers/
			 */
			if( $forFollow == true && $action=='remove' ) {
				$actionString = null;
				break;
			}

			switch( $action ) {
				case 'nudge':
					$actionString .= "<a href=\"/wo/followings/nudge/$idOther\">挠挠</a> | ";
					break;
				case 'd':
					$actionString .= "<a href=\"/wo/message/create/$idOther\">悄悄话</a> | ";
					break;
				case 'on':
					$actionString .= "<a href=\"/wo/followings/on/$idOther\">接收通知</a> | ";
					break;
				case 'off':
					$actionString .= "<a href=\"/wo/followings/off/$idOther\">取消通知</a> | ";
					break;
				case 'follow':
					$actionString .= "<a href=\"/wo/followings/follow/$idOther\">打开关注</a> | ";
					break;
				case 'leave':
					$actionString .= "<a href=\"/wo/followings/leave/$idOther\">取消关注</a> | ";
					break;
			}
		}
		if ( $idOther == $idUser ) {
			$actionString .= "<a href=\"/wo/avatar\">头像</a> | ";
		}
		$actionString = trim( $actionString, ' | ');
		$ops[ $idOther ] = $actionString;
	}

	return ( $isArray === false ) ? ( isset($ops[ $idOthers[0] ]) ? $ops[ $idOthers[0] ] : array() ) : $ops;
}

function pageTitle(){
	global $pageTitle;
	if( $pageTitle )
		return $pageTitle;
	return "这一刻，你在做什么？"; 
}
?>
