<!--{include header}-->

<h2>{$userInfo['nameScreen']}的${JWFriend::GetFriendNum($userInfo['id'])}位好友｜<a href="/{$userInfo['nameScreen']}/followers/">{$userInfo['nameScreen']}的${JWFollower::GetFollowerNum($userInfo['id'])}位粉丝</a></h2>
<ul>
<!--{foreach $friends as $friend}-->
<li>
    <img src="${JWPicture::GetUserIconUrl($friend['id'],'thumb48')}" alt="{$friend['nameScreen']}" alt="{$friend['nameScreen']}" />
    <a href="/{$friend['nameScreen']}/">{$friend['nameScreen']}</a>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->