<?php
require_once ('../../../jiwai.inc.php');

JWLogin::MustLogined();

//die(var_dump($_SERVER));
//die(var_dump($_REQUEST));

function do_friend_request_deny()
{
	$logined_user_id =JWLogin::GetCurrentUserId();

	$param = $_REQUEST['pathParam'];
	if ( ! preg_match('/^\/(\d+)$/',$param,$match) )
	{
		$error_html =<<<_HTML_
哎呀！系统路径好像不太正确……
_HTML_;
		return array('error_html'=>$error_html);
	}

	$friend_id = JWDB::CheckInt($match[1]);

	$friend_user_name = JWUser::GetUserInfo($friend_id, 'nameScreen');

	$is_succ = JWFriendRequest::Destroy($friend_id, $logined_user_id);

	if ( ! $is_succ )
	{
		$error_html = <<<_HTML_
哎呀！由于系统故障，邀请仍然继续保留，请稍候再试。
_HTML_;
		return array('error_html'=>$error_html);
	}

	$notice_html = <<<_HTML_
您拒绝了添加${friend_user_name}为好友请求。
_HTML_;
	return array('notice_html'=>$notice_html);
}

$info = do_friend_request_deny();

if ( !empty($info['error_html']) )
	JWSession::SetInfo('error',$info['error_html']);

if ( !empty($info['notice_html']) )
	JWSession::SetInfo('notice',$info['notice_html']);


JWTemplate::RedirectBackToLastUrl('/');
exit(0);
?>