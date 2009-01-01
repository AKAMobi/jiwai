<!--${
	$user = JWUser::GetUserInfo($g_page_user_id);
	$iconurl = JWTemplate::GetAssetUrl('/images/img.gif');
	if ( !$thread_id )
		$thread_id = JWStatus::GetHeadStatusId($g_page_user_id);
	if ( $thread_id )
		$one = JWDB_Cache_Status::GetDbRowById( $thread_id );

	if ( $one)
	{
		$user = JWUser::GetUserInfo($one['idUser']);
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
			$plugin_result = JWPlugins::GetPluginResult( $one );
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
	$avatar = JWPicture::GetUrlById($user['idPicture'], 'thumb96');
}-->
<div class="f">
	<div class="usermsg">
		<div class="lt">
			<div class="hd mar_b8">
				<a href="/{$user['nameUrl']}/"><img src="{$avatar}" title="{$user['nameScreen']}" /></a>
			</div>
			<!--{if true}-->
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
			<ul id="update_count" class="bgall">
				<!--{if $action['on']}-->
				<li><span class="f_gra">&gt;&gt;</span> <a href="/wo/action/on/{$user['id']}" onclick="return JWAction.redirect(this);" class="f_gra">接受更新通知</a></li>
				<!--{elseif $action&&!$action['follow']&&!$action['on']}-->
				<li><span class="f_gra">&gt;&gt;</span> <a href="/wo/action/off/{$user['id']}" onclick="return JWAction.redirect(this);" class="f_gra">取消更新通知</a></li>
				<!--{/if}-->

				<li><span class="ico_mail"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="18" height="12" /></span><a href="/wo/direct_messages/create/{$user['id']}" onclick="return JWAction.redirect(this);">发送悄悄话</a></li>
				<li><span class="ico_nao"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="18" height="12" /></span><a href="/wo/action/nudge/{$user['id']}" onclick="return JWAction.redirect(this);">挠挠此人</a></li>

				<!--{if $action['block']===false}-->
				<li><span class="ico_stop"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="18" height="12" /></span><a href="/wo/block/u/{$user['id']}">解除阻止</a></li>
				<!--{else}-->
				<li><span class="ico_stop"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="18" height="12" /></span><a href="/wo/block/b/{$user['id']}" onclick="return JWAction.redirect(this);">阻止此人</a></li>
				<!--{/if}-->
			</ul>
			<!--{/if}-->
		</div>
		<div class="msg phot_mar">
			<div class="rt txt_r"><a href="/wo/design/save/{$user['id']}" class="dark">使用此人配色</a></div>
			<h1>{$user['nameScreen']}</h1>
			<!--{if $one}-->
			<div class="f_14 mar_b8">{$formated_one['status']}<!--{if isset($plugin_result['html'])}--><div class="bg_black">{$plugin_result['html']}</div><!--{/if}--></div>
			<div class="f_gra">
				<div class="dark"><a href="/{$user['nameUrl']}/" title="{$user['nameFull']}">{$user['nameScreen']}</a>&nbsp;<a href="/{$user['nameUrl']}/statuses/{$thread_id}" class="f_gra" title="{$one['timeCreate']}">${JWStatus::GetTimeDesc($one['timeCreate'])}</a>&nbsp;通过&nbsp;{$through}</div>
				<div class="rt lightbg"><a href="/{$replyto}/thread/{$replyid}" class="thread_item" rel="{$one['id']}:{$user['nameScreen']}"><span class="ico_rebak"><img src="{$iconurl}" width="16" height="12" /></span>${$replynum ? $replynum.'条':''}回复</a><!--{if $g_current_user_id}-->&nbsp; &nbsp;<a href="/wo/favourites/${$is_favourited?"create":"create"}/{$one['id']}" onclick="return JWAction.toggleStar({$one['id']});" id="status_star_{$one['id']}" title="${$is_favourited?"取消收藏":"收藏它"}"><span id="ico_star_{$one['id']}" class="ico_fav${$is_favourited?'d':''}"><img src="{$iconurl}" width="16" height="12" /></span>${$is_favourited?"取消收藏":"收藏"}</a><!--{/if}--><!--{if $can_delete}-->&nbsp; &nbsp;<a href="/wo/status/destroy/{$one['id']}" class="c_note" onclick="return JWAction.doTrash({$one['id']})"><span class="ico_trash"><img src="{$iconurl}" width="16" height="12" /></span>删除</a><!--{/if}--></div>
			</div>
			<!--{/if}-->
		</div>
		<div class="clear"></div>
	</div>
</div>