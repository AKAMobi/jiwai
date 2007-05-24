<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
?>
<html>

<?php JWTemplate::html_head() ?>


<body class="status" id="public_timeline">


<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container">
	<div id="content">
		<div id="wrapper">


			<span class="ytf" style="font-family: 黑体">
				<h2><a href="http://help.jiwai.de/NewUserGuide" target="_blank">第一次来，不知道如何叽歪？很容易，来这里吧！</a></h2>
			</span>
			<script type="text/javascript">
				JiWai.Yft(".ytf");
			</script>


<?php JWTemplate::ShowActionResultTips(); ?>


			<div class="tab">

<?php JWTemplate::tab_header( array( 'title'	=>	'最新动态 - 大家在做什么？' 
									, 'title2'	=>	'你想叽歪你就说嘛，你不说我怎么知道你想叽歪呢？：-）'
							) )
?>

<?php 
$status_data 	= JWStatus::GetStatusIdsFromPublic();
$status_rows	= JWStatus::GetStatusDbRowsByIds($status_data['status_ids']);
$user_rows		= JWUser::GetUserDbRowsByIds	($status_data['user_ids']);

$options	= array ( 'uniq'=>2 );
JWTemplate::Timeline($status_data['status_ids'], $user_rows, $status_rows, $options) 
?>
  
			</div><!-- tab -->
		</div><!-- wrapper -->
	</div><!-- content -->


<?php 

$featured_options['user_ids']	= JWUser::GetFeaturedUserIds(5);

$newest_options['title']		= '看看新来的';
$newest_options['user_ids']		= JWUser::GetNewestUserIds(5);

$arr_menu = array(	array ('head'			, array('JiWai.de <strong>叽歪广场</strong>'))
					, array ('featured'			, array($featured_options) )
					, array ('featured'			, array($newest_options) )
				);

if ( ! JWLogin::IsLogined() )
	array_push ($arr_menu, array('register', array(true)));

JWTemplate::sidebar($arr_menu, null);
?>
			
		
</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
