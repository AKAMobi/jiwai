<!--${$shortcut = $shortcut; settype( $shortcut, 'array' )}-->
<!--{if $shortcut }-->
<h2>叽歪直通车</h2>
<!--{/if}-->

<!--{if in_array( 'my', $shortcut ) }-->
<p>1 <a href="${buildUrl('/'.$loginedUserInfo['nameUrl'].'/')}" accesskey="1">我的叽歪</a></p>
<!--{/if}-->

<!--{if in_array( 'followings', $shortcut ) }-->
<p>2 <a href="${buildUrl('/wo/followings/')}" accesskey="2">关注的人</a></p>
<!--{/if}-->

<!--{if in_array( 'favourite', $shortcut ) }-->
<p>3 <a href="${buildUrl('/wo/favourites/')}" accesskey="3">我的收藏</a></p>
<!--{/if}-->

<!--{if in_array( 'public_timeline', $shortcut ) }-->
<p>5 <a href="${buildUrl('/public_timeline/')}" accesskey="5">叽歪广场</a></p>
<!--{/if}-->

<!--{if in_array( 'message', $shortcut ) }-->
<!--${
	$msgCount = JWMessage::GetMessageStatusNum($loginedUserInfo['id'], JWMessage::INBOX, JWMessage::MESSAGE_NOTREAD);
	$msgString = ( $msgCount == 0 ) ? '' : '('.$msgCount.'条)';
}-->
<p>7 <a href="${buildUrl('/wo/message/inbox')}" accesskey="7">悄悄话{$msgString}</a></p>
<!--{/if}-->

<p>8 <a href="${buildUrl('/help/')}" accesskey="8">帮助</a></p>

<!--{if in_array( 'index', $shortcut ) }-->
<p>9 <a href="${buildUrl('/')}" accesskey="9">首页</a></p>
<!--{/if}-->

<!--{if in_array( 'search', $shortcut ) }-->
<p>0 <a href="${buildUrl('/wo/search/')}" accesskey="0">搜索</a></p>
<!--{/if}-->

<!--{if in_array( 'logout', $shortcut ) }-->
<p>x <a href="${buildUrl('/wo/logout/')}">退出</a></p>
<!--{/if}-->
