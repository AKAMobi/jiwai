<!--{include header}-->
<!--{include wo/update}-->

<h2><a href="/wo/">最新叽歪</a>｜<a href="/wo/replies/">叽友回复</a>｜[{$tag_row['name']}]</h2>
<ul>
<!--{foreach $statuses as $status}-->
<li>
	<a href="${buildUrl('/'.$users[$status['idUser']]['nameUrl'].'/')}" rel="contact">${getDisplayName($users[$status['idUser']])}</a>：{$status['status']}
	<span class="stamp">
	${JWStatus::GetTimeDesc($status['timeCreate'])}
	通过
	${JWDevice::GetNameFromType($status['device'], @$status['idPartner'])}
	${($loginedUserInfo&&$loginedUserInfo['id']!=$status['idUser']) ? "<a href=\"/wo/status/destroy/".$status['id']."\">悄悄话</a>" : ''}
	${($loginedUserInfo['id'] && false==JWFavourite::IsFavourite($loginedUserInfo['id'],$status['id'])) ? "<a href=\"/wo/status/favourite/".$status['id']."\">收藏</a>" : "<a href=\"/wo/status/unfavourite/".$status['idUser']."\">取消收藏</a>"}
    <a href="/wo/status/r/{$status['id']}">回复</a>
	${($loginedUserInfo&&$loginedUserInfo['id']!=$status['idUser']) ? "<a href=\"/wo/status/rt/".$status['id']."\">RT</a>" : ''}
	</span>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
