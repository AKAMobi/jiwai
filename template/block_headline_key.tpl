<div class="pagetitle">
	<h1>词汇：{$key}</h1>
</div>
<div class="f_14">共有 <b>{$count}条</b> 符合</div>
<!--{if $guessword}--><div>你是不是要搜索：<span class="f_yel"><a href="/k/{$guessword}/">{$guessword}</a></span></div><!--{/if}-->
<!--${$o=$sovalue['o']=='asc'?'desc':'asc';}-->
<div class="txt_r">
<!--{if $sovalue['f']=='null'}-->		
	<a href="{$sourl}?f=time" class="f_gra">↓↑按时间排序</a>
	<a href="{$sourl}?f=null&o={$o}" class="f_gra bg_pink">↓↑按相关度排序</a>
<!--{else}-->
	<a href="{$sourl}?f=time&o={$o}" class="f_gra bg_pink">↓↑按时间排序</a>
	<a href="{$sourl}?f=null" class="f_gra">↓↑按相关度排序</a>
<!--{/if}-->
</div>
