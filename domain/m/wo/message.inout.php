<?php
$pageTitle = ($action=='sent') ? "我发出去的悄悄话" : "我接受到的悄悄话" ;
$boxType = ($action=='sent') ? JWMessage::SENT : JWMessage::INBOX ;


$messageNum = JWMessage::GetMessageNum( $loginedUserInfo['id'], $boxType );
$pagination = new JWPagination( $messageNum, $page , 10 );

$messageInfo = JWMessage::GetMessageIdsFromUser( $loginedUserInfo['id'], $boxType, $pagination->GetNumPerPage(), $pagination->GetStartPos()); 

$messageIds = $messageInfo['message_ids'];
$userIds    = $messageInfo['user_ids'];

$messageRows    = JWMessage::GetMessageDbRowsByIds( $messageIds );
$userRows       = JWUser::GetUserDbRowsByIds( $userIds);

$url = ($action=='sent') ? '/wo/message/sent' : '/wo/message/inbox' ;
$pageString = paginate( $pagination, $url );
$shortcut = array('logout', 'public_timeline', 'my', 'friends', 'index' );
$tpl = ($action=='sent') ? 'wo/message_sent' : 'wo/message_inbox' ;
JWRender::Display( $tpl, array(
            'messages' => $messageRows,
            'users' => $userRows,
            'loginedUserInfo' => $loginedUserInfo,
            'pageString' => $pageString,
            'shortcut' => $shortcut,
        ));

?>