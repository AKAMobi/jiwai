<p><a href="/${HtmlSpecialChars($userInfo['nameUrl'])}/"><img src="${JWPicture::GetUserIconUrl($userInfo['id'],'thumb48')}" width="48" height="48" border="0" alt="{$userInfo['nameScreen']}"/></a></p>
<!--${$op = friendsop( $loginedUserInfo['id'], $userInfo['id'] ) }-->
${$op ? "关注操作：$op" : ''}
