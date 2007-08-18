<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$user_info		= JWUser::GetCurrentUserInfo();

?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container" class="subpage">
<?php JWTemplate::SettingTab('/wo/openid/'); ?>

<div class="tabbody">
<h2>设置你的OpenId</h2>

<div style="width:500px; margin:30px auto; font-size:14px;">

<?php

$openid_id		= JWOpenid::GetIdByUserId($user_info['idUser']);

if ( isset($_REQUEST['commit']) )
{
	// 用户输入了自己的  openid，需要去验证
	$openid_url = $_REQUEST['user']['openid'];

	if ( JWOpenid::IsPossibleOpenid($openid_url) )
	{
		JWOpenid_Consumer::AuthRedirect($openid_url);
		// if it return, mean $username_or_email is not a valid openid url.
	}
	else
	{
		$error_html = <<<_HTML_
你输入的 OpenID：$openid_url 有误，请查证后重试。
_HTML_;
		JWSession::SetInfo('error', $error_html);
	}
}

JWTemplate::ShowActionResultTips();

?>

<ul>
<li><a href="http://openids.cn/openid-introduction/" target="_blank">什么是 OpenID？</a></li>
<li><a href="http://openids.cn/how-to-use-openid/" target="_blank">OpenID如何使用？</a></li>
</ul>

<?php
if ( isset($_REQUEST['set']) )
{
	// 用户进行设置自己的 openid
	echo <<<_SET_OPENID_
		<form action="/wo/openid/" method="POST">
			<fieldset>
				<label for="user_openid">OpenID 地址：</label>
				<input id="user_openid" name="user[openid]" size="30" type="text" />
				<input name="commit" type="submit" value="保存" />
			</fieldset>
		</form>
_SET_OPENID_;
}
else if ( $openid_id )
{
	// 用户自己的 openid
	$openid_db_row 	= JWOpenid::GetDbRowById($openid_id);
	$openid_url 	= JWOpenid::GetFullUrl($openid_db_row['urlOpenid']);
	echo <<<_USER_OPENID_
		<h3 style="display:inline">你的 OpenID 为：<strong>$openid_url</strong></h3>
		<a href="/wo/openid/destroy/$openid_id">使用叽歪de OpenID ?</a>
_USER_OPENID_;

}
else
{
	
	// 用户使用 jiwai de openid
	echo <<<_JIWAI_OPENID_
		<h3 style="display:inline">你的 OpenID 为：<strong>http://jiwai.de/$user_info[nameScreen]/</strong></h3>
		<a href="?set">绑定你自己的 OpenID ?</a>
_JIWAI_OPENID_;
}
?>

<?php

$trusted_site_ids 		= JWOpenid_TrustSite::GetIdsByUserId($user_info['id']);
$trusted_site_db_rows 	= JWOpenid_TrustSite::GetDbRowsByIds($trusted_site_ids);

if ( count($trusted_site_ids) )
{
	echo <<<_HTML_
<h4>你当前允许在以下网站登录你的 OpenID</h4>
_HTML_;
}

echo "<ul>\n";
foreach ( $trusted_site_ids as $trusted_site_id )
{
	$db_row = $trusted_site_db_rows[$trusted_site_id];
	echo <<<_HTML_
<li><a href="/wo/trustsite/destroy/$db_row[id]">删除</a> <a href="$db_row[urlTrusted]" target="_blank"><strong>$db_row[urlTrusted]</strong></a></li>
_HTML_;
}
echo "</ul>\n";
?>

</div>
</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
