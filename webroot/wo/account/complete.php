<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

if ( JWLogin::IsLogined() )
{
	JWLogin::Logout();
}

$address 	= @$_REQUEST['address'];
$nameScreen	= @$_REQUEST['nameScreen'];

if ( !empty($nameScreen) )
{
	if ( IsAddressBelongsToName($address,$nameScreen) )
	{
		$user_row = JWUser::GetUserInfo($nameScreen);
		
		if ( JWUser::IsWebUser($user_row['idUser']) )
		{
			$notice_html = <<<_HTML_
你以前曾来过这里！为什么不登录呢？
_HTML_;
			JWSession::SetInfo('notice',$notice_html);
			header("Location: /wo/login");
			exit(0);
		}
		else
		{
			// IM / SMS 用户第一次来，设置好登录状态后，送到用户信息修改页面
			JWLogin::Login($user_row['idUser'], false);
			header('Location: /wo/account/settings');
			exit(0);
		}
	}
}

/*
 *	错误信息下面处理
 */
?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>

<body class="account" id="create">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container" class="subpage">

<h2>除了手机短信、IM聊天软件以外，你还可以在网页上JiWai&hellip;</h2>


<?php
if ( isset($nameScreen) )
{
	// 用户输入了用户设备但是没有找到？
	echo <<<_HTML_
<div class="yft">
哎呀！我们没能够找到你！<br>
是否帐号名没有输入正确？<br>
<br>
你可以通过短信或IM发送"WHOAMI"或"WOSHISHUI"(我是谁)给IM机器人（wo@jiwai.de）或短信特服号（移动99118816，联通93188816），查询正确的帐号名。
</div>

<script type="text/javascript">
JiWai.Yft('.yft');
</script>

_HTML_;
}
?>


<form action="/wo/account/complete" method="post" name="f">
<fieldset>
<table width="550" cellspacing="15" cellpadding="0" border="0">
	<tr>
		<td width="70" align="right" />手机号</td>
		<td width="150" /><input id="address" name="address" type="text" /></td>
        <td class="note" valign="top" />请填写你使用JiWai时用的手机号码或聊天软件帐号(邮件地址)</td>
	</tr>
	<tr>
		<td width="70" align="right"/>帐号名</td>
		<td width="150" /><input id="screen_name" name="nameScreen" type="text" /></td>
        <td class="note" valign="top" />忘记啦？发送　woshishu　到Gtalk机器人（wo@jiwai.de）查询</td>
	</tr>
</table>
</fieldset>
        <input id="commit" type="submit" class="submitbutton" style="margin-left:270px;width:120px" value="继续" />
</form>

<script type="text/javascript">
//<![CDATA[
$('address').focus();
//]]>
</script>



</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>

<?php
function IsAddressBelongsToName($address,$name)
{
	if ( empty($address) || empty($name) )
		return false;

	if ( preg_match('/^\d/',$name) )
		return false;

	$user_row	 	= JWUser::GetUserInfo($name);

	if ( empty($user_row) )
		return false;

	$device_row		= JWDevice::GetDeviceRowByUserId($user_row['idUser']);

	if ( empty($device_row) )
		return false;

	$ims = array_keys($device_row);

	foreach ( $ims as $im )
	{
		if ( $address==$device_row[$im]['address'] )
			return true;
	}

	return false;
}
?>
