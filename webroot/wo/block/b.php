<?php
require_once( '../../../jiwai.inc.php' );
JWLogin::MustLogined();

$pathParam = trim( $_REQUEST['pathParam'], '/' );
$userInfo = JWUser::GetUserInfo( $pathParam );


if( false == empty($userInfo) ){
	$logined_user_id = JWLogin::GetCurrentUserId();
	JWSns::Block( $logined_user_id, $userInfo['id'] );
	JWSession::SetInfo('notice', "阻止 $userInfo[nameScreen] 成功。");
}

JWTemplate::RedirectBackToLastUrl();
?>