<div class="menu">
<!--{if isAdmin('admin')}-->
<div class="title mb10">用户管理</div>
<ul>
	<li ${$menu_nav=='userquery'?'class="selected"':''}><a href="/userquery.php">用户信息查询</a></li>
	<li ${$menu_nav=='imquery'?'class="selected"':''}><a href="/imquery.php">IM设备查询</a></li>
	<li ${$menu_nav=='usersetting'?'class="selected"':''}><a href="/usersetting.php">修改用户设置</a></li>
	<li ${$menu_nav=='userurl'?'class="selected"':''}><a href="/userurl.php">允许用户修改URL</a></li>
</ul>
<!--{/if}-->

<!--{if isAdmin('admin')}-->
<div class="title mb10">扩展功能</div>
<ul>
	<li ${$menu_nav=='conflist'?'class="selected"':''}><a href="/conflist.php">会议列表</a></li>
	<li ${$menu_nav=='confsetting'?'class="selected"':''}><a href="/confsetting.php">修改会议设置</a></li>
	<li ${$menu_nav=='votesetting'?'class="selected"':''}><a href="/votesetting.php">修改投票设置</a></li>
</ul>
<!--{/if}-->

<div class="title mb10">更新管理</div>
<ul>
	<li ${$menu_nav=='statusdelete'?'class="selected"':''}><a href="/statusdelete.php">修改某条更新</a></li>
</ul>

<!--{if isAdmin('admin')}-->
<div class="title mb10">区块管理</div>
<ul>
	<li ${$menu_nav=='sidebar'?'class="selected"':''}><a href="/sidebar.php">首页公告区块</a></li>
</ul>
<!--{/if}-->

<div class="title mb10">投诉管理</div>
<ul>
	<li ${$menu_nav=='feed_complain'?'class="selected"':''}><a href="/feed_complain.php">投诉用户</a></li>
	<li ${$menu_nav=='feed_message'?'class="selected"':''}><a href="/feed_message.php">消息不通畅</a></li>
	<li ${$menu_nav=='badboy'?'class="selected"':''}><a href="/badboy.php">上报问题用户</a></li>
</ul>

<div class="title mb10">人工审核</div>
<ul>
	<li ${$menu_nav=='filterwords'?'class="selected"':''}><a href="/filterwords.php">禁忌词设置</a></li>
	<li ${$menu_nav=='statusexam'?'class="selected"':''}><a href="/statusexam.php">待审核JiWai更新</a></li>
</ul>

<!--{if isAdmin('admin')}-->
<div class="title mb10">系统运营</div>
<ul>
	<li ${$menu_nav=='statuscreate'?'class="selected"':''}><a href="/statuscreate.php">用户更新量汇总</a></li>
	<li ${$menu_nav=='userregistered'?'class="selected"':''}><a href="/userregistered.php">注册用户量汇总</a></li>
	<li ${$menu_nav=='confgroupsms'?'class="selected"':''}><a href="/confgroupsms.php">群发会议短信</a></li>
	<li ${$menu_nav=='mobilebind'?'class="selected"':''}><a href="/mobilebind.php">手机绑定列表</a></li>
</ul>
<!--{/if}-->

<hr style="height:1px;"/>
<ul><li><a href="/logout.php">退出</a></li></ul>
</div>
