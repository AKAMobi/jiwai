<!--${
	$user = JWUser::GetUserInfo($g_page_user_id);
	$iconurl = JWTemplate::GetAssetUrl('/images/img.gif');
	if ( !$thread_id )
		$thread_id = JWStatus::GetHeadStatusId($g_page_user_id);
	if ( $thread_id )
		$one = JWDB_Cache_Status::GetDbRowById( $thread_id );
	if ( $status_id ) 
		$rstatus = JWDB_Cache_Status::GetDbRowById( $status_id );

	$ruser = $rstatus ? JWUser::GetUserInfo($rstatus['idUser']) : $user;
	$ouser = $one ? JWUser::GetUserInfo($one['idUser']) : $user;

	if ( $one)
	{
		$is_protected = JWSns::IsProtectedStatus($one, $g_current_user_id);
		$can_delete = JWStatus::IsUserCanDelStatus($g_current_user_id, $thread_id);
		$is_favourited = JWFavourite::IsFavourite($g_current_user_id, $thread_id); 

		if ( $one['statusType'] == 'MMS' ) 
		{
			$photo_url = JWPicture::GetUrlById($user['idPicture']);
		}
		else if ( !empty($one['idPicture']) ) 
		{
			$photo_url = JWPicture::GetUrlById($one['idPicture']);
		}
		else 
		{
			$photo_url = JWTemplate::GetAssetUrl('/images/org-nobody-48-48.gif');
		}

		if( !$is_protected)
		{
			$scale = $noupdater ? 'picture' : 'middle';
			$plugin_result = JWPlugins::GetPluginResult($one, $scale);
			$formated_one = JWStatus::FormatStatus($one);
		}
		else
		{
			$plugin_result = "";
			$formated_one = array(
				'status' => '我只和我关注的人分享我的叽歪。',
				'replyto' => NULL,
				);
		}
		$replyto = $formated_one['replyto'] 
			? $formated_one['replyto'] : $user['nameUrl'];
		if ($one['idThread']) {
			$rone = JWDB_Cache_Status::GetDbRowById($one['idThread']);
			$ouser = $rone ? JWUser::GetUserInfo($rone['idUser']) : $user;
		}
		$replyurl = $ouser['nameUrl'];
		$replynum = $one['idThread'] ? 0 : JWDB_Cache_Status::GetCountReply($thread_id);
		$replyid = ($one['idThread'] ? $one['idThread'].'/':'') . $thread_id;
		$through = JWDevice::GetNameFromType($one['device'],$one['idPartner']) . @$_INI['type']['S_'.$one['statusType']];
	}
	else
	{
		$formated_one = array(
				'status'=>'目前为止没有叽歪过。'
				);
	}

	$count = JWSns::GetUserState($user['id']);
	$countv = JWVisitUser::GetCount($user['id']);
	$countv = intval($countv);
	$avatar = JWPicture::GetUrlById($user['idPicture'], 'thumb96');
}-->
<script>
window.jiwai_init_hook_threadreply = function() {
	$('jw_status').value = '@{$ruser['nameScreen']} ';
	$('jw_ruid').value = '{$ruser['id']} ';
	$('jw_rsid').value = '${$status_id ? $status_id : $thread_id}';
}
</script>
<div class="${$single?'single':'top'}_block">
	<div class="usermsg">
		<div class="lt">
			<div class="hd mar_b8">
				<a href="/{$user['nameUrl']}/"><img src="{$avatar}" title="{$user['nameScreen']}" /></a>
			</div>
			<!--{if !$nofollower && ($g_current_user_id != $g_page_user_id)}-->
			<!--${$action = JWSns::GetUserAction($g_current_user_id, $g_page_user_id);}-->
			<div class="mar_b8" >
				<!--{if !$action||$action['follow']}-->
				<div class="button sbtn">
					<div class="at"></div><div class="bt"></div>
					<div class="tt">
						<a href="/wo/action/follow/{$g_page_user_id}" onclick="return JWAction.follow({$g_page_user_id});">关注此人</a>
					</div>
					<div class="bt"></div><div class="at"></div>
				</div>
				<!--{else}-->
				<div class="bg_dark">
					已关注 &gt;&gt; <a href="/wo/action/leave/{$g_page_user_id}" onclick="return JWAction.redirect(this);" class="bg_dark">取消</a>
				</div>
				<!--{/if}-->
			</div>
			<!--{/if}-->
		</div>
		<div class="msg phot_mar">
			<h1>{$user['nameScreen']}</h1>
			<!--{if $one['statusType']=='MMS'}-->
			<div class="f_14 mar_b20">
				<h3>彩信消息</h3>
			</div>
			<div class="f_gra mar_b20">
				<div class="rt" >&gt;&gt;<a href="/{$user['nameUrl']}/mms/"> 查看此人的所有照片</a>&nbsp;</div>
				<div class="dark" >时间:{$one['timeCreate']}</div>
			</div>
			<div class="mar_b8">
				<div class="bg_black">{$plugin_result['html']}</div>
			</div>
			<div class="mar_b8">
				<div class="f_gra mar_b20">{$formated_one['status']}</div>
			</div>
			<div class="mar_b50">
				<!--{if !$is_protected}-->
				<div class="rt lightbg"><a href="/{$replyurl}/thread/{$replyid}" class="thread_item" onclick="return JWAction.replyStatus('{$user['nameScreen']}','{$one['idUser']}','{$one['id']}');" rel="{$one['id']}:{$user['nameScreen']}"><span class="ico_rebak"><img src="{$iconurl}" width="16" height="12" /></span>${$replynum ? $replynum.'条':''}回复</a><!--{if $g_current_user_id}-->&nbsp; &nbsp;<a href="/wo/favourites/${$is_favourited?"create":"create"}/{$one['id']}" onclick="return JWAction.toggleStar({$one['id']});" id="status_star_{$one['id']}" title="${$is_favourited?"取消收藏":"收藏它"}"><span id="ico_star_{$one['id']}"  class="ico_fav${$is_favourited?'d':''}"><img src="{$iconurl}" width="16" height="12" /></span>${$is_favourited?"取消收藏":"收藏"}</a><!--{/if}--><!--{if $can_delete}-->&nbsp; &nbsp;<a href="/wo/status/destroy/{$one['id']}" class="c_note" onclick="return JWAction.doTrash({$one['id']})"><span class="ico_trash"><img src="{$iconurl}" width="16" height="12" /></span>删除</a><!--{/if}--><!--{if JWUser::IsAdmin($g_current_user_id)}-->&nbsp; &nbsp;<a href="/wo/status/transfer/{$one['id']}" class="c_note" onclick="return confirm('真的要转移这条叽歪吗？');">转移</a><!--{/if}--></div>
				<!--{else}-->
					<br/>
				<!--{/if}-->
			</div>
			<!--{elseif $one}-->
			<div class="line mar_b8"></div>
			<div class="f_14 mar_b8">{$formated_one['status']}<!--{if isset($plugin_result['html'])}--><div class="bg_black">{$plugin_result['html']}</div><!--{/if}--></div>
			<div class="f_gra">
				<!--{if !$is_protected}-->
				<div class="rt lightbg"><a href="/{$replyurl}/thread/{$replyid}" class="thread_item" onclick="return JWAction.replyStatus('{$user['nameScreen']}','{$one['idUser']}','{$one['id']}');" rel="{$one['id']}:{$user['nameScreen']}"><span class="ico_rebak"><img src="{$iconurl}" width="16" height="12" /></span>${$replynum ? $replynum.'条':''}回复</a><!--{if $g_current_user_id}-->&nbsp; &nbsp;<a href="/wo/favourites/${$is_favourited?"create":"create"}/{$one['id']}" onclick="return JWAction.toggleStar({$one['id']});" id="status_star_{$one['id']}" title="${$is_favourited?"取消收藏":"收藏它"}"><span id="ico_star_{$one['id']}"  class="ico_fav${$is_favourited?'d':''}"><img src="{$iconurl}" width="16" height="12" /></span>${$is_favourited?"取消收藏":"收藏"}</a><!--{/if}--><!--{if $can_delete}-->&nbsp; &nbsp;<a href="/wo/status/destroy/{$one['id']}" class="c_note" onclick="return JWAction.doTrash({$one['id']})"><span class="ico_trash"><img src="{$iconurl}" width="16" height="12" /></span>删除</a><!--{/if}--><!--{if JWUser::IsAdmin($g_current_user_id)}-->&nbsp; &nbsp;<a href="/wo/status/transfer/{$one['id']}" class="c_note" onclick="return confirm('真的要转移这条叽歪吗？');">转移</a><!--{/if}--></div>
				<div class="dark"><a href="/{$user['nameUrl']}/" title="{$user['nameFull']}">{$user['nameScreen']}</a>&nbsp;<a href="/{$user['nameUrl']}/statuses/{$thread_id}" class="f_gra" title="{$one['timeCreate']}">${JWStatus::GetTimeDesc($one['timeCreate'])}</a>&nbsp;通过&nbsp;{$through}</div>
				<!--{else}-->
					<br/>
				<!--{/if}-->
			</div>
			<!--{/if}-->
		</div>
		<div class="clear"></div>
	</div>
	<!--{if !$noupdater}-->	
	<!--{if $rstatus || $rstatus['idUserReplyTo']==$g_current_user_id}-->
	${$element->block_statuses_one(array('status'=>$rstatus))}
	<!--{/if}-->
	<div id="formDiv">
		<form name="" action="/wo/status/update" id="updaterForm" method="post" onsubmit="$('jw_status').style.backgroundColor='#eee';">
			<div class="pagetitle">
				<h1>添加回复：</h1>
			</div>
			<!--{include block_updater}-->
		</form>
	</div>
	<!--{/if}-->
</div>
<br/>
