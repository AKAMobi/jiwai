<!--${
	$user = JWUser::GetUserInfo( $g_current_user_id );
	$mcount = JWDB_Cache_Message::GetNewMessageNum($g_current_user_id);
}-->
<div id="header"> 
	<h2 id="logo"><a class="header" href="/"><img src="${JWTemplate::GetAssetUrl('/images/img.gif')}" alt="叽歪de" /></a></h2>
	<div class="hdnav">
		<div id="nav" class="wht">
			<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
			<ul class="f" >
				<li><a href="/" class="sel">首页</a></li>
				<li><a href="/{$user['nameUrl']}/" >我的叽歪</a></li>
				<li><a href="/g/" >随便逛逛</a> <a href="/t/元旦惊喜/" class="act">元旦惊喜</a></li>
				<li><a href="/wo/invite/" >找朋友</a></li>
				<li><a href="/wo/gadget/" >窗可贴</a></li>
				<li class="rt">
					<!--{if $mcount}-->
					<a href="/wo/direct_messages/" title="你有{$mcount}条新消息" ><img src="${JWTemplate::GetAssetUrl('/images/new_ico.gif')}" width="18" height="12" /></a> 
					<!--{/if}--><a href="/wo/account/profile">{$user['nameScreen']}的设置</a>&nbsp; &nbsp;<a href="/t/帮助留言板/">帮助</a>&nbsp; &nbsp;<a href="/wo/logout">退出</a>
				</li>
			</ul>
			<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
		</div>
	</div>
</div>