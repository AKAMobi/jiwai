<?php
require_once ('../../../jiwai.inc.php');
JWLogin::MustLogined(false);

$logined_user_id=JWLogin::GetCurrentUserId();

if ( is_int($logined_user_id) )
{
	$param = $_REQUEST['pathParam'];

	if ( preg_match('/\/(\d+)$/',$param,$match) ){
		$trust_site_id = $match[1];

		if ( ! JWOpenID_TrustSite::IsUserOwnId($logined_user_id, $trust_site_id) )
		{
			JWTemplate::RedirectTo404NotFound();
		}

		$trust_site_db_row = JWOpenID_TrustSite::GetDbRowById($trust_site_id);

		$trust_site_url = $trust_site_db_row['urlTrusted'];

		if ( JWOpenID_TrustSite::Destroy($trust_site_id) )
		{
			$notice_html = "{$trust_site_url} 删除成功。";
		}
		else
		{
			$error_html = "哎呀！由于系统故障，删除失败了…… 请稍后再试。";
		}
	}

	if ( !empty($error_html) )
		JWSession::SetInfo('error',$error_html);

	if ( !empty($notice_html) )
		JWSession::SetInfo('notice',$notice_html);
}


if ( array_key_exists('HTTP_REFERER',$_SERVER) )
	$redirect_url = $_SERVER['HTTP_REFERER'];
else
	$redirect_url = '/';

header ("Location: $redirect_url");
exit(0);
?>
