<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();
JWLogin::MustLogined(true);

$logined_user_info 	= JWUser::GetCurrentUserInfo();
$logined_user_id 	= $logined_user_info['id'];
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

/*
 *	除了显示 /wo/favourites/ 之外，还负责显示 /zixia/favourites/
 *	如果是其他用户的 favourites 页(/zixia/friends)，则 $g_user_favourites = true, 并且 $g_page_user_id 是页面用户 id
 *
 */

$logined_user_info 	= JWUser::GetCurrentUserInfo();

$head_options = array();

if ( isset($g_user_favourites) && $g_user_favourites ) {
	$rows				= JWDB_Cache_User::GetDbRowsByIds(array($g_page_user_id));
	$page_user_info		= $rows[$g_page_user_id];
	$head_options['ui_user_id']		= $g_page_user_id;
} else {
	$page_user_info		= $logined_user_info;
}

$status_num		= JWFavourite::GetFavouriteNum($page_user_info['id']);
$pagination		= new JWPagination($status_num, $page);
$status_ids		= JWFavourite::GetFavourite($page_user_info['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );

$status_rows	= JWStatus::GetDbRowsByIds($status_ids);

$user_ids		= array_map( create_function('$row','return $row["idUser"];'), $status_rows );
$user_rows		= JWDB_Cache_User::GetDbRowsByIds($user_ids);


?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head($head_options) ?>
</head>


<body class="normal">


<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>


<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">
<?php 
if ( $page_user_info['id']==$logined_user_info['id'] ) {
	JWTemplate::tab_menu( array( array('name'=>'我的收藏', 'url'=>'javascript:void(0);', 'active'=>true, ) ) );
} else {
	JWTemplate::tab_menu( array( array('name'=>'此人收藏', 'url'=>'javascript:void(0);', 'active'=>true, ) ) );
}
?>
			<div class="tab">

<?php
$n = 0;
if ( isset($status_ids) )
{
	JWTemplate::Timeline($status_ids, $user_rows, $status_rows, array('pagination' => $pagination));
}
?>
			</div><!-- tab -->
		</div><!-- wrapper -->
	</div><!-- content -->
<?php
if ( $g_user_favourites )
{
		$current_user_id = $logined_user_info['id'];
		$user_action_rows = JWSns::GetUserActions($current_user_id , array($page_user_info['id']) );
		$user_action_row = empty($user_action_rows) ? array() : $user_action_rows[$page_user_info['id']];
		$arr_friend_list = JWFollower::GetFollowingIds($page_user_info['id']);
		$arr_count_param = JWSns::GetUserState($page_user_info['id']);
		$idUserVistors = JWSns::GetIdUserVistors( $page_user_info['id'], $current_user_id );
		$arr_menu = array(
			array ('user_notice', array($page_user_info)),
			array ('device_info', array($page_user_info)),
			array ('user_info', array($page_user_info)),
			array ('action', array($user_action_row,$page_user_info['id'])),
			array ('count', array($arr_count_param,$page_user_info)),
			array ('vistors', array($idUserVistors )),
			array ('friend', array($arr_friend_list)),
			array ('listfollowing', array( $page_user_info['nameScreen'], count($arr_friend_list) > 60 ) ),
			array ('rss', array('user', $page_user_info['nameScreen'])),
			);

	if ( false == JWLogin::IsLogined() ) {
		array_push ( $arr_menu, array('register', array(true)) );
	} else {
		array_push ( $arr_menu, array('block', array($current_user_id, $g_page_user_id)) );
	}

	JWTemplate::sidebar( $arr_menu, $g_page_user_id);
}
else
{
	include_once dirname( dirname(__FILE__) ).'/sidebar.php';
}
JWTemplate::container_ending();
?>

</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
