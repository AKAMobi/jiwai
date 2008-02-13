 <?php
 require_once('../../../jiwai.inc.php');
 JWTemplate::html_doctype();
 
 JWLogin::MustLogined();
 
 
 $user_info      = JWUser::GetCurrentUserInfo();
 
 $user_setting   = JWUser::GetNotification($user_info['id']);
 
 //echo "<pre>";(var_dump($user_setting));
 if ( isset($_REQUEST['commit_x']) )
 {
     $user_new_setting   = $_REQUEST['user'];
 
	if ( ! JWUser::SetNotification($user_info['id'], $user_new_setting) )
	{
		JWSession::SetInfo('error', '通知设置由于系统故障未能保存成功，请稍后再试。');
	}
	else
	{
		JWSession::SetInfo('notice', '通知设置保存成功！');
	}

	JWTemplate::RedirectToUrl();
}

?>
<html>

<head>
<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); 
?>
 </head>
 
 
 <body class="account" id="settings">
 
 <?php JWTemplate::accessibility() ?>
 
 <?php JWTemplate::header('/wo/account/settings') ?>
 <?php JWTemplate::ShowActionResultTipsMain() ?>
 
 <div id="container">
 <p class="top">设置</p>
 <div id="wtMainBlock">
 <div class="leftdiv">
 <ul class="leftmenu">
 <li><a href="/wo/account/settings">基本资料</a></li>
 <li><a href="/wo/privacy/">保护设置</a></li>
 <li><a href="/wo/devices/sms">绑定设置</a></li>
 <li><a href="/wo/notification/email" class="now">系统通知</a></li>
 <li><a href="/wo/account/profile_settings">个性化界面</a></li>
 <li><a href="/wo/openid/">Open ID</a></li>
 </ul>
 </div><!-- leftdiv -->
 <div class="rightdiv">
 <div class="lookfriend">
 <form id="f" action="" method="post" name="f">
 <input type="hidden" name="commit_x" value="1"/>
<p class="right14"><a href="/wo/notification/email">Email</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/notification/im" class="now">手机/聊天软件</a></p>
        <div class="binding">
         <p>
             <input <?php if ( 'Y'==$user_setting['auto_nudge_me'] ) echo ' checked="checked" ';?> 
                     id="user_auto_nudge_me" name="user[auto_nudge_me]" type="checkbox" value="Y" />
             <label for="user_auto_nudge_me">如果我在24小时内没有更新，请让叽歪小弟提醒我</label>
         </p>
         <p>
             <input <?php if ( 'Y'==$user_setting['is_receive_offline'] ) echo ' checked="checked" ';?> 
                     id="is_receive_offline" name="user[is_receive_offline]" type="checkbox" value="Y" />
             <label for="is_receive_offline">当我的聊天软件处于离线状态时也给我发送通知</label>
         </p>
	   <p class="checkboxText">我们支持qq的隐身，msn的脱机</p>		 
	   <p class="sysUpdate"><input type="submit" id="save" name="save" class="submitbutton" value="保存" /></p>
	   </div>
	   <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
  </form>
</div><!-- lookfriend -->
<div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
</div>
<!-- rightdiv -->
</div><!-- #wtMainBlock -->
<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>