<!--${$notice = JWSession::GetInfo('error') . JWSession::GetInfo('notice');}-->
<!--{if $notice || $notice=$forcenotice }-->
<div id="tipnote">
	<div class="yel mar_b8">
		<div class="f">
			<div class="pad_t8 tipnote" onclick="JiWai.KillNote('tipnote')">{$notice}</div>
		</div>
		<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
	</div>
</div>
<!--{/if}-->