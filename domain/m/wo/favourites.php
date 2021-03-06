<?php
require_once( '../config.inc.php' );

JWLogin::MustLogined();

$loginedUserInfo = JWUser::GetCurrentUserInfo();
$loginedIdUser 	= $loginedUserInfo['id'];
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;


$statusNum = JWFavourite::GetFavouriteNum($loginedUserInfo['id']);

$pagination = new JWPagination($statusNum, $page, 10);
$statusIds = JWFavourite::GetFavourite($loginedUserInfo['id'],$pagination->GetNumPerPage(),$pagination->GetStartPos());

$statusRows = JWDB_Cache_Status::GetDbRowsByIds($statusIds);
$userIds = array_map( create_function('$row','return $row["idUser"];'), $statusRows );
$userRows = JWDB_Cache_User::GetDbRowsByIds( $userIds );

krsort( $statusRows );

$statuses = array();
foreach( $statusRows as $k=>$s){
    $fs = JWStatus::FormatStatus( $s, false, false, true );
    $s['status'] = $fs['status'];
   // $s['status']  = preg_replace('/^@\s*([\w\._\-]+)/e',"buildReplyUrl('$1')", htmlSpecialChars($s['status']) );
    $statuses[ $k ] = $s;
}

$followingsNum = JWFollower::GetFollowingNum( $loginedUserInfo['id'] );
$followersNum = JWFollower::GetFollowerNum( $loginedUserInfo['id'] );

$shortcut = array( 'public_timeline', 'logout', 'my', 'search', 'message' , 'followings', 'index', 'replies');
$pageString = paginate( $pagination, '/wo/favourites/' );
JWRender::Display( 'wo/favourites', array(
    'loginedUserInfo' => $loginedUserInfo,
    'users' => $userRows,
    'statuses' => $statuses,
    'followingsNum' => $followingsNum,
    'followersNum' => $followersNum,
    'shortcut' => $shortcut,
    'pageString' => $pageString,
));
?>
