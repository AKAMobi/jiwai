<?php
$userInfo = JWUser::GetUserInfo('help');
$pageTitle = "叽歪de帮助留言版";

$statusNum = JWStatus::GetStatusNumFromSelfNReplies( $userInfo['id'] );
$pagination = new JWPagination( $statusNum, $page, 10 );
$statusData = JWStatus::GetStatusIdsFromSelfNReplies( $userInfo['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );

$statusRows    =  JWDB_Cache_Status::GetDbRowsByIds( $statusData['status_ids']);
$userRows      =  JWDB_Cache_User::GetDbRowsByIds( $statusData['user_ids'] );

$tpl = 'user/help';

krsort( $statusRows );

$statuses = array();
foreach( $statusRows as $k=>$s ){
    $fs = JWStatus::FormatStatus( $s, false, false, true );
    $s['status'] = $fs['status'];
    //$s['status']  = preg_replace('/^@\s*([\w\._\-]+)/e',"buildReplyUrl('$1')", htmlSpecialChars($s['status']) );
    $statuses[ $k ] = $s;
}

$shortcut = array('public_timeline', 'index');
if( JWLogin::isLogined() ) {
    array_push( $shortcut, 'logout','my','favourite', 'search', 'followings','message','replies');
}else{
    array_push( $shortcut, 'register' );
}

$url = "/".urlencode($userInfo['nameUrl'])."/" . ( $statusTab=='with_friends' ? 'with_friends/' : '' );
$pageString = paginate( $pagination, $url );
JWRender::Display( $tpl , array(
                    'userInfo' => $userInfo,
                    'statuses' => $statuses,
                    'users' => $userRows,
                    'shortcut' => $shortcut,
                    'loginedUserInfo' => $loginedUserInfo,
                    'pageString' => $pageString,
                ));
?>
