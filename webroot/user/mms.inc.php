<?php
JWTemplate::html_doctype();
$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : null;
$page = isset( $_REQUEST['page'] ) ? intval( $_REQUEST['page'] ) : 1;
$page = ( $page < 1 ) ? 1 : $page;

//$debug = JWDebug::instance();
//$debug->init();

/*
 *	可能会接收到从 index.php 过来的全局变量 
		$g_user_with_friends 
		$g_user_default
		$g_page_user_id
 */

$active = 'mms'; $mmsId = 0 ; 
@list( $active, $mmsId )  = explode( '/', trim( $_REQUEST['pathParam'], '/' ) );
$mmsId = intval( $mmsId );


$page_user_id = $g_page_user_id;

$current_user_id = JWLogin::GetCurrentUserId();
$page_user_info = JWDB_Cache_User::GetDbRowById($page_user_id);

$protected = JWSns::IsProtected( $page_user_info, $current_user_id );

?>
<html xmlns="http://www.w3.org/1999/xhtml">

<?php 

$active_tab = 'archive';

if ( $active == 'mms_friends' )
	$active_tab = 'friends';
/*
 * 使用 JWPagination 时，要注意用户在最上面已经显示了一条了，所以总数应该减一
 *  not minus - 1
 */
switch ( $active_tab )
{
	default:
	case 'archive':
		// 显示用户自己的
		$user_status_num= JWStatus::GetStatusMmsNum($page_user_id) - 1;

		$pagination	= new JWPagination($user_status_num, $page);
		$status_data 	= JWStatus::GetStatusIdsFromUserMms( $page_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos()+1 );
		break;

	case 'replies':
		die("UNSUPPORT");
		break;

	case 'friends':
		// 显示用户和好友的

		//$user_status_num= JWStatus::GetStatusNumFromFriends($page_user_id);
		$user_status_num= JWStatus::GetStatusMmsNumFromFriends($page_user_id) - 1;

		$pagination		= new JWPagination($user_status_num, $page);

		//$status_data 	= JWStatus::GetStatusIdsFromFriends( $page_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos() );
		$status_data 	= JWStatus::GetStatusIdsFromFriendsMms( $page_user_id, $pagination->GetNumPerPage(), $pagination->GetStartPos()+1 );

		break;
}

// use cache $status_rows	= JWStatus::GetDbRowsByIds( $status_data['status_ids']);
$status_rows	= JWDB_Cache_Status::GetDbRowsByIds( $status_data['status_ids']);

//die(var_dump($status_rows));

$status_data['user_ids'][] = $page_user_id;

$user_rows		= JWDB_Cache_User::GetDbRowsByIds	($status_data['user_ids']);

if( $page_user_info['idConference'] ) {
	$head_status_data 	= JWStatus::GetStatusIdsFromConferenceUser( $page_user_id, 1 );
}else{
	$head_status_data 	= JWDB_Cache_Status::GetStatusIdsFromUser( $page_user_id, 1 );
}
$head_status_rows 	= JWDB_Cache_Status::GetDbRowsByIds($head_status_data['status_ids']);
$head_status_id 	= @array_shift($head_status_data['status_ids']); 


/*
 *	设置 html header
 */
$keywords 		= <<<_STR_
$page_user_info[nameScreen]($page_user_info[nameFull]) - $page_user_info[bio] $page_user_info[location] 
_STR_;

$description = "叽歪de $page_user_info[nameFull] ";
if ( false == $protected ) 
{
	$description .= @$head_status_rows[$head_status_id]['status'];

	foreach ( $status_data['status_ids'] as $status_id )
	{
		$description .= ' '.$status_rows[$status_id]['status'];
		if ( mb_strlen($description,'UTF-8') > 140 )
		{
				$description = mb_substr($description,0,140,'UTF-8');
				break;
		}
	}
}

$rss = array ( 	
		// User TimeLine RSS & Atom
		 array(	 
		 	'url' => "http://api.jiwai.de/statuses/user_timeline/$page_user_id.rss",
			'title' => "$page_user_info[nameFull] (RSS)",
			'type' => "rss",
		),
		array(
			'url' => "http://api.jiwai.de/statuses/user_timeline/$page_user_id.atom",
			'title' => "$page_user_info[nameFull] (Atom)",
			'type' => "atom",
		),
		// Friends TimeLine RSS & Atom
		array(
			'url' => "http://api.jiwai.de/statuses/friends_timeline/$page_user_id.rss",
			'title'	=> "$page_user_info[nameFull]和朋友们 (RSS)",
			'type' => "rss",
		),
		array(
			'url' => "http://api.jiwai.de/statuses/friends_timeline/$page_user_id.atom",
			'title'	=> "$page_user_info[nameFull]和朋友们 (Atom)",
			'type' => "atom",
		),
	);

$options = array(
		'title' => "$page_user_info[nameScreen] / $page_user_info[nameFull]",
		'keywords' => htmlspecialchars($keywords),
		'description' => htmlspecialchars($description),
		'author' => htmlspecialchars($keywords),
		'rss' => $rss,
		'refresh_time' => '60',
		'refresh_url' => '',
		'ui_user_id' => $page_user_id,
		'openid_server'	=> "http://jiwai.de/wo/openid/server",
		'openid_delegate' => "http://jiwai.de/$page_user_info[nameScreen]/",
	);

?>
<head>
<?php 
JWTemplate::html_head($options); 
?>
</head>


<body class="normal">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container">
	<!-- div id="flaginfo">zixia</div -->
<!-- google_ad_section_start -->
	<div id="content">
		<div id="wrapper">

<?php
JWTemplate::ShowActionResultTips();

//die(var_dump($page_user_id));
JWTemplate::StatusHead( $page_user_info, @$head_status_rows[$head_status_id] );
if ( isset($head_status_id) 
	&& isset($head_status_rows[$head_status_id])
	&& $head_status_rows[$head_status_id]['isMms']=='N')
{
	$pagination->SetTotalNum( 1, true );
}
?>

<?php 
$menu_list = array (
		 'friends' => array(
				'active' => false,
				'name' => "和朋友们",
				'url' => "/$page_user_info[nameScreen]/mms_friends/",
		),
		'archive' => array(
				'active' => false,
				'name' => "以前的",
				'url' => "/$page_user_info[nameScreen]/mms/",
		),
	);

$menu_list[$active_tab]['active'] = true;
//die(var_dump($menu_list));


if ( false == $protected )
	JWTemplate::tab_menu($menu_list); 
?>

			<div class="tab">

<?php 
if ( !isset($g_user_with_friends) )
	$g_user_with_friends = false;


// 只有用户不设置保护，或者设置了保护是好友来看的时候，才显示内容
if ( false == $protected ) {
	JWTemplate::Timeline( $status_data['status_ids'] ,$user_rows ,$status_rows,
				array(
					'icon'	=> $g_user_with_friends,
					'protected'=> $protected, 
                                    	'pagination' => $pagination,
					'isMms' => true, 
				 )
	);
}
?>
  
<?php 
/*
if ( $show_protected_content )
	JWTemplate::pagination($pagination, (null===$q) ? array() : array('q'=>$q) );
if ( $show_protected_content )
	JWTemplate::rss( $g_user_with_friends ? 'friends' : 'user' ,$page_user_id) 
*/
?>

			</div><!-- tab -->

		</div><!-- wrapper -->
	</div><!-- content -->

<?php 


//$arr_action_param	= JWSns::GetUserAction($logined_user_info['id'],$page_user_info['id']);

$user_action_rows	= JWSns::GetUserActions($current_user_id , array($page_user_info['id']) );

if ( empty($user_action_rows) )
	$user_action_row	= array();
else
	$user_action_row	= $user_action_rows[$page_user_info['id']];


$arr_friend_list	= JWFollower::GetFollowingIds($page_user_info['id']);
$arr_count_param	= JWSns::GetUserState($page_user_info['id']);

$arr_menu = array(
		array (
			'user_notice', 
			array($page_user_info),
		),
		array (
			'user_info',
			array($page_user_info),
		), 
		array(
			'action',
			array($user_action_row,$page_user_info['id'])
		), 
		array(
			'count', 
			array($arr_count_param,$page_user_info),
		), 
		array (
			'separator', 
			array(),
		),
		array (
			'friend',
			array($arr_friend_list),
		),
		array (
			'rss',
			array( 'user', $page_user_info['nameScreen'],),
		),
	);

if ( ! JWLogin::IsLogined() )
	array_push ( $arr_menu, 
					array('register', array(true) )
				);


JWTemplate::sidebar( $arr_menu, $page_user_id);
JWTemplate::container_ending();
?>

</div><!-- #container -->


<?php JWTemplate::footer() ?>

</body>
</html>
