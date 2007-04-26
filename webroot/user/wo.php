<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
$debug = JWDebug::instance();
$debug->init();

$logined_user_info	= JWUser::GetCurrentUserInfo();
$page_user_info 	= JWUser::GetUserInfoById($idUserPage);
?>
<html>

<?php JWTemplate::html_head() ?>

<body class="normal">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container">
	<!-- div id="flaginfo">zixia</div -->
<!-- google_ad_section_start -->
	<div id="content">
		<div id="wrapper">


<?php 
if ( array_key_exists('nameScreen',$_REQUEST) ){
	$nameScreen = $_REQUEST['nameScreen'];
	$aStatusList = JWStatus::GetStatusListUser($idUserPage);
}else{
	$aStatusList = null;
}

	if ( isset($aStatusList) )
		JWTemplate::status_head(array_shift($aStatusList)); 
?>

<?php JWTemplate::tab_menu() ?>

			<div class="tab">

<?php JWTemplate::tab_header( array() ) ?>

<?php JWTemplate::timeline($aStatusList, array('icon'=>false)) ?>
  
<?php JWTemplate::pagination() ?>

<?php JWTemplate::rss() ?>
			</div><!-- tab -->

  			<script type="text/javascript">
//<![CDATA[  
/*new PeriodicalExecuter(function() { new Ajax.Request('/account/refresh?last_check=' + $('timeline').getElementsByTagName('tr')[0].id.split("_")[1], 
    {
      asynchronous:true, 
      evalScripts:true,
      onLoading: function(request) { Effect.Appear('timeline_refresh', {duration:0.3 }); },
      onComplete: function(request) { Element.hide('timeline_refresh'); }
    })}, 120);
*/
  //]]>
			</script>

		</div><!-- wrapper -->
	</div><!-- content -->

<?php 

$arr_action_param	= array ();

if ( JWFriend::IsFriend($logined_user_info['id'], $page_user_info['id']) )
{
	if ( $logined_user_info['id']!==$page_user_info['id'] )
		$arr_action_param['destroy']	= true;

/* TODO: follow function
	if ( JWFriend::IsFollower($page_user_info['id'], $logined_user_info['id'] ) )
		$arr_action_param['leave']	= true;
	else
		$arr_action_param['follow']	= true;
*/
}
else if ( $logined_user_info['id']!==$page_user_info['id'] )
{
 	// not friend, and not myself
	$arr_action_param['create']		= true;
}

$arr_friend_list	= JWFriend::GetFriend($page_user_info['id']);

$arr_count_param	= JWUser::GetState($page_user_info['id']);

$arr_menu 			= array(	array ('user_notice'	, array($page_user_info))
								, array ('user_info'	, array($page_user_info))
								, array ('count'		, array($arr_count_param))
								, array ('action'	, array($arr_action_param,$page_user_info['id']))
								, array ('friend'	, array($arr_friend_list))
							);

if ( ! JWUser::IsLogined() )
	array_push ( $arr_menu, 
					array('register', null)
				);


JWTemplate::sidebar( $arr_menu, $idUserPage);
?>

</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
