<?php
$pageTitle = "我关注的人";

$followingsNum = JWFollower::GetFollowingNum( $loginedUserInfo['id'] );
$pagination = new JWPagination( $followingsNum, $page, 10 );
$followingIds  = JWFollower::GetFollowingIds( $loginedUserInfo['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );
$followingRows = JWDB_Cache_User::GetDbRowsByIds($followingIds);

$actionOps = actionop( $loginedUserInfo['id'], $followingIds );

$pageString = paginate( $pagination, '/wo/followings/' );

$shortcut = array( 'my', 'index', 'logout', 'public_timeline', 'search', 'favourite', 'message', 'followings', 'replies' );
JWRender::Display( 'wo/followings', array(
                'followings' => $followingRows,
                'actionOps' => $actionOps,
                'loginedUserInfo' => $loginedUserInfo,
                'pageString' => $pageString,
                'shortcut' => $shortcut,
            ));

?>
