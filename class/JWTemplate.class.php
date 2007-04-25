<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Template Class
 */
class JWTemplate {
	/**
	 * Instance of this singleton
	 *
	 * @var JWTemplate
	 */
	static private $msInstance;

	/**
	 * Const vars, init when first be used
	 *
	 * @var array
	 */
	static private $msJWConst = null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWTemplate
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
		//throw new JWException("JWTemplate no need construct, use static method pls.");
	}

	static public function html_head()
	{
?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


	<title>叽歪de / 这一刻，你在做什么？</title>

	<link rel="start" href="http://JiWai.de/" title="叽歪de我" />

	<link href="http://asset.jiwai.de/css/jiwai-screen.css?1173735600" media="screen, projection" rel="Stylesheet" type="text/css" />

	<meta name="ICBM" content="40.4000, 116.3000" />
	<meta name="DC.title" content="叽歪de" />

	<meta name="keywords" content="叽歪de, 叽歪的, 唧歪de, 唧歪的, 叽叽歪歪, 唧唧歪歪, tiny blog, blog, im nick, nick, log, 记录, 写下" />

	<meta name="description" content="叽歪de - 记录、并与朋友分享你每天的点滴。" />

	<meta name="author" content="JiWai.de, 叽歪de, 叽歪" />

	<meta name="copyright" content="copyright 2007 http://jiwai.de" />

	<meta name="robots" content="all" />

	<link rel="shortcut icon" href="http://asset.jiwai.de/img/favicon.gif?1173735600" type="image/gif" />
	
	<link rel="alternate" title="叽歪de - [RSS]" href="http://feeds.feedburner.com/jiwai" />

	<script type="text/javascript" src="http://asset.jiwai.de/js/minmax.js"></script>
	<script type="text/javascript" src="http://asset.jiwai.de/js/mootools.v1.00.js"></script>

</head>

<?php

	}


	static public function accessibility()
	{
?>


<ul id="accessibility">
	<li>
		<a href="#navigation" accesskey="2">跳转到导航目录</a>
	</li>
	<li>
		<a href="#side">跳转到功能目录</a>
	</li>
</ul>


<?php
	}


	static public function header()
	{
		$nameScreen = JWUser::GetCurrentUserInfo('nameScreen');
?>

<div id="header">
	<div id="navigation">
		<h2><a class="header" href="/">叽歪de</a></h2>
<?php if ( strlen($nameScreen) ){ ?>
		<ul>
			<li class="first"><a href="/wo/">叽歪一下</a></li>
			<li><a href="/<?php echo $nameScreen ?>">叽歪de我</a></li>
			<li><a href="<?php echo self::GetConst('UrlPublicTimeline')?>">叽歪广场</a></li>
			<li><a href="/wo/invitation/invite">邀请</a></li>
			<li><a href="/wo/account/badge">挂件/饰件/Badges/widget</a></li>
			<li><a href="/wo/account/setting">设置</a></li>
			<li><a href="/help/">帮助</a></li>
			<li><a href="/wo/logout">退出</a></li>
		</ul>
<?php } ?>
	</div>
</div>

<?php
	}


	static public function footer()
	{
?>

<div id="footer">
	<h3>Footer</h3>
	<ul>
		<li class="first">&copy; 2007 叽歪de</li>

		<li><a href="/help/aboutus">关于我们</a></li>
		<li><a href="/help/contact">联系我们</a></li>
		<li><a href="http://blog.jiwai.de">Blog</a></li>
		<li><a href="/help/api">API</a></li>
		<li><a href="/help">帮助</a></li>
		<li><a href="/tos">使用协议</a></li>

	</ul>
</div>

<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-287835-11";
_uOsr[20]="baidu"; _uOkw[20]="wd";
_uOsr[21]="3721"; _uOkw[21]="name";
_uOsr[22]="baidu"; _uOkw[22]="word";
_uOsr[23]="qq.com"; _uOkw[23]="w";
_uOsr[24]="iask"; _uOkw[24]="k";
_uOsr[25]="sogou"; _uOkw[25]="query";
_uOsr[26]="qihoo"; _uOkw[26]="kw";
_uOsr[27]="daqi"; _uOkw[27]="content";
_uOsr[28]="soso.com"; _uOkw[28]="w";
urchinTracker();
</script>
<?php
	}


	static public function updater()
	{
?>
			<form action="/wo/status/update" id="doingForm" method="post" onsubmit="new Ajax.Request('/status/update', {asynchronous:true, evalScripts:true, onComplete:function(request){$('status').value = ''; updateStatusTextCharCounter($('status').value);$('submit').disabled = false;$('submit_loading').style.display='none';Effect.Appear('chars_left_notice', {duration:0.5});}, onLoading:function(request){$('submit').disabled = true;Effect.Appear('submit_loading', {duration:0.3});$('chars_left_notice').style.display='none';}, parameters:Form.serialize(this)}); return false;">
				<fieldset>
					<div class="bar odd">
						<h3>
							<label for="status">
								这一刻，你在做什么？
							</label>
						</h3>
						<span id="chars_left_notice">
							还可输入: <strong id="status-field-char-counter"></strong>个字。
						</span>
						<span style="display:none" id="submit_loading">
							<script type="text/javascript">
//<![CDATA[
document.write('<img alt="Updating" src="http://asset.jiwai.de/img/updating.gif" title="Updating" />')
//]]>

							</script>
						</span>
					</div>
					<div class="jiwai_icon_vtab">
						<div>
							
							<textarea  class="jiwai_icon_vtab_inner" id="status" name="status" onkeypress="return (event.which == 8) || (this.value.length &lt; getStatusTextCharLengthMax(this.value));" onkeyup="updateStatusTextCharCounter(this.value)" rows="3" cols="15"></textarea>
						</div>
					</div>
					<div class="submit">
						<input id="submit" name="commit" type="submit" class="buttonSubmit" value="叽歪一下" />
					</div>
				</fieldset>
			</form>

<script type="text/javascript">
$('submit').onmouseover = function(){
	this.className += "Hovered"; 
}

$('submit').onmouseout = function(){
	this.className = this.className.replace(/Hovered/g, "");
}

</script>


					<!--div style="width:100%; border:1px solid #f00; text-align:left">
						<textarea id='test' name='test' rows="3" style="width:100%"></textarea>
					</div-->
			<script type="text/javascript">
//<![CDATA[
$('status').focus()
//]]>
			</script>
			<script type="text/javascript">
//<![CDATA[

	// FIXME: IE???
	function getStatusTextCharLengthMax(value)
	{
		return 140;
	  if (value.match(/[^\u00-\u7F]/))
	  {
		// chinese msg;
		return 70;
	  }
	  // ascii msg
	  return 140;
	}
// onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\u4E00-\u9FA5]/g,''))">

	function updateStatusTextCharCounter(value) {
	  len_max = getStatusTextCharLengthMax(value);

	  if (len_max - value.length >= 0) {
		  $('status-field-char-counter').innerHTML = len_max - value.length;
		} else {
		  $('status-field-char-counter').innerHTML = 0;
		}
	};

//]]>
			</script>
			<script type="text/javascript">
//<![CDATA[
$('status-field-char-counter').innerHTML = getStatusTextCharLengthMax($('status').value) - $('status').value.length;
//]]>
			</script>

<?php
	}


	static public function tab_menu()
	{
?>

			<ul class="tabMenu">
				<li><a href="/wo/account/archive">历史</a></li>
				<li class="active"><a href="/wo/">最近</a></li>
			</ul>

<?php
	}


	static public function tab_header( $vars=null )
	{
		if ( !array_key_exists('title',$vars) )	
			$vars['title'] = '<a href="/wo">你</a>和<a href="/friends">朋友们</a>都在做什么?';

		if ( !array_key_exists('title2',$vars) )	
			$vars['title2'] = '每分钟更新一次。';
?>

				<table class="layout_table" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td class="bg_tab_top_left">
						</td>
						<td class="bg_tab_top_mid">
							<h2><?php echo $vars['title']; ?>
</h2>
						</td>
						<td class="bg_tab_top_right">
						</td>
					</tr>
					<tr class="odd">
						<td class="bg_tab_top2_left">
						</td>
						<td colspan="2" class="bg_tab_top2_right">
							<span style="display:none" id="timeline_refresh">

    							<script type="text/javascript">
//<![CDATA[
document.write('<img alt="更新中" src="http://assets.jiwai.de/images/icon_throbber.gif" title="Updating" />')
//]]>
								</script>
  							</span>

							<?php echo $vars['title2']; ?>

						</td>
					</tr>

				</table>

<?php
	}


	static public function status_head($aStatus=null)
	{
		if ( empty($aStatus) )
			return;

		$idStatus 	= $aStatus['idStatus'];
		$idUser 	= $aStatus['idUser'];
		$nameScreen = $aStatus['nameScreen'];
		$nameFull	= $aStatus['nameFull'];
		$photoInfo	= $aStatus['photoInfo'];
		$status		= $aStatus['status'];
		$timestamp	= $aStatus['timestamp'];
		$device		= $aStatus['device'];

		$duration	= JWStatus::get_time_desc($timestamp);


		$photo_url	= self::GetConst('UrlStrangerPicture');

		if ( !empty($photoInfo) )
		{
			$arr_photo_info = JWUser::GetPictureInfo($photoInfo);

			preg_match('/^(\d+)\|(.+)\.([^.]+)$/',$photoInfo,$matches);
			$photo_url 	= "/$nameScreen/picture/thumb48." . $matches[3] . '?' . $matches[1];
			$photo_name		= htmlspecialchars($matches[2]);
		}
	

?>

			<h2 class="thumb">
				<a href="/wo/account/profile_image/<?php echo $nameScreen?>"><img alt="<?php echo $nameFull?>" border="0" src="<?php echo $photo_url?>" valign="middle" /></a>
		
				<?php echo $nameScreen ?>

				<small>

				</small>
  
			</h2>

			<div class="desc">
	  			<p><?php echo htmlspecialchars($status)?></p>
	  			<p class="meta">
  					<a href="http://jiwai.de/zixia/statuses/<?php echo $idStatus?>"><?php echo $duration?></a>
  					来自 <?php echo $device?>
					<span id="status_actions_<?php echo $idStatus?>">
	<a href="#" onclick="new Ajax.Request('/wo/favourings/create/<?php echo $idStatus?>', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_<?php echo $idStatus?>').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_empty" border="0" id="status_star_<?php echo $idStatus?>" src="http://asset.jiwai.de/img/icon_star_empty.gif" /></a>
	
	<a href="/wo/status/destroy/<?php echo $idStatus?>" onclick="if (confirm('Sure you want to delete this update? There is NO undo!')) { var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = 'POST'; f.action = this.href;var m = document.createElement('input'); m.setAttribute('type', 'hidden'); m.setAttribute('name', '_method'); m.setAttribute('value', 'delete'); f.appendChild(m);f.submit(); };return false;" title="Delete this update?"><img alt="Icon_trash" border="0" src="http://asset.jiwai.de/img/icon_trash.gif" /></a>
					</span>
  				</p>
			</div>

<?php
	}


	static public function timeline($aStatusList=null, $show_icon=true)
	{
		if ( empty($aStatusList) )
			return;

		$idCurrentUser = JWUser::GetCurrentUserInfo('id');
?>

				<table class="doing" id="timeline" cellspacing="0" cellpadding="0">    
<?php
		$n=0;
		foreach ( $aStatusList as $aStatus ){
//die(var_dump($aStatusList));
			$idStatus 	= $aStatus['idStatus'];
			$idUser 	= $aStatus['idUser'];
			$nameScreen = $aStatus['nameScreen'];
			$nameFull	= $aStatus['nameFull'];
			$photoInfo	= $aStatus['photoInfo'];
			$status		= $aStatus['status'];
			$timestamp	= $aStatus['timestamp'];
			$device		= $aStatus['device'];
			
			$duration	= JWStatus::get_time_desc($timestamp);


			$photo_url	= self::GetConst('UrlStrangerPicture');

			if ( !empty($photoInfo) )
			{
				$arr_photo_info = JWUser::GetPictureInfo($photoInfo);

				preg_match('/^(\d+)\|(.+)\.([^.]+)$/',$photoInfo,$matches);
				$photo_url 	= "/$nameScreen/picture/thumb48." . $matches[3] . '?' . $matches[1];
				$photo_name		= htmlspecialchars($matches[2]);
			}
	

			if ( 'sms'==$device )
				$device='手机';
			else
				$device=strtoupper($device);

			$reply_to	= null;
			if ( preg_match('/^@([\d\w._\-]+)\s/',$status,$matches) )
			{
				$reply_to = $matches[1];
			}


			/* 
			 * if status contains URL, we split status str to 4 parts: url_before, url_domain, url_path, url_after.
			 *
			 * URL is ascii. UTF8 ascii is 1 byte so we use 0x00-0xff to match ascii.
			 * 		":" is 0x3a
			 *		"/" is 0x2F
			 *		' ' is 0x20
			 *
			 */
			if ( preg_match(	'/'
										. '^(.*?)'
										. 'http:\/\/'
											. '([' . '\x00-\x1F' ./*' '*/ '\x21-\x2E' ./*'/'*/ '\x30-\x39' ./*':'*/ '\x3B-\x79' . ']+)'
											. '([' . '\x00-\x1F' ./*' '*/ '\x21-\x79' . ']*)'
										. '(.*)$/'
								, $status
								, $matches 
							) )
			{
				//var_dump($matches);
				$head_str		= htmlspecialchars($matches[1]);
				$url_domain		= htmlspecialchars($matches[2]);
				$url_path		= htmlspecialchars($matches[3]);
				$tail_str		= htmlspecialchars($matches[4]);

				$url_str		= <<<_HTML_
<a href="javascript: urchinTracker('/wo/outlink/$url_domain$url_path'); document.location.href='http://$url_domain$url_path';">http://$url_domain/...</a>

_HTML_;
				$status 		= $head_str . $url_str . $tail_str;
			}
			else
			{
				$status = htmlspecialchars($status);
			}
?>
					<tr class="<?php echo $n++%2?'even':'odd';?>" id="status_<?php echo $idStatus;?>">
<?php if ( $show_icon ){ ?>
						<td class="thumb">
							<a href="/<?php echo $nameScreen;?>"><img alt="<?php echo $nameFull;?>" 
									src="<?php echo $photo_url?>" /></a>
						</td>
<?php } ?>
						<td>	
<?php if ( $show_icon ){ ?>
							<strong>
								<a href="/<?php echo $nameScreen?>" 
										title="<?php echo $nameFull?>"><?php echo $nameScreen?></a>
							</strong>
<?php } ?>

							<?php echo $status?>
			
							<span class="meta">
								<a href="/<?php echo $nameScreen?>/statuses/<?php echo $idStatus?>"><?php echo $duration?></a>
								来自于 <?php echo $device?> 
								<?php if (!empty($reply_to) ) echo " <a href='/$reply_to/'>给 ${reply_to} 的回复</a> " ?>

								<span id="status_actions_<?php echo $idStatus?>">

									<a href="#" onclick="new Ajax.Request('/wo/favourings/create/<?php echo $idStatus?>', {asynchronous:true, evalScripts:true, onLoading:function(request){$('status_star_<?php echo $idStatus?>').src='/images/icon_throbber.gif'}}); return false;"><img alt="Icon_star_empty" id="status_star_<?php echo $idStatus?>" src="http://asset.jiwai.de/img/icon_star_empty.gif" /></a>

								</span>

							</span>

						</td>
					</tr>
<?php
				}
?>
				</table>

<?php
	}


	static public function pagination()
	{
?>

				<div class="pagination">
  					<ul>
  						<li class="nextpage">
	   	 					<a href="/home?page=2">前一页 &#187;</a>
  						</li>
  					</ul>
				</div>


<?php
	}

	static function sidebar_login()
	{
?>

		<form action="/wo/login" class="signin" method="post" name="f">
			<fieldset>
				<div>
					<label for="username_or_email">用户名 / Email</label>
					<input id="email" name="username_or_email" type="text" />
    			</div>

    			<div>
    				<label for="password">密码</label>
    				<input id="pass" name="password" type="password" />
    			</div>

    			<input id="remember_me" name="remember_me" type="checkbox" value="1" checked/> <label for="remember_me">记住我</label>
    			<small><a href="/account/resend_password">忘记?</a></small>
    			<input id="submit" name="commit" type="submit" value="登录" />
    		</fieldset>
		</form>

		<script type="text/javascript">
//<![CDATA[
document.getElementById('email').focus()
//]]>
		</script>

<?php
	}

	static function sidebar_register()
	{
?>

		<div class="notify">
  			想拥有一个叽歪de帐号吗？<br />
  			<a href="/wo/account/create" class="join">免费注册！</a><br />
  			10秒搞定！
  		</div>

<?php
	}

	static function sidebar_featured()
	{
?>

  		<ul class="featured">
			<li><strong>叽歪de推荐</strong></li>

			<li>
				<a href="/dev"><img alt="JiWai.de Developer" height="24" src="/dev/picture" width="24" /></a>
				<a href="/dev">JiWai.de Developer</a>
			</li>

			<li>
				<a href="/jessica"><img alt="jessica" height="24" src="/jessica/picture" width="24" /></a>
				<a href="/jessica">Jessica</a>
			</li>

			<li>
				<a href="/maxinyu"><img alt="Ma Xinyu" height="24" src="/maxinyu/picture" width="24" /></a>
				<a href="/maxinyu">daodao</a>
			</li>

			<li>
				<a href="/zixia"><img alt="Li Zhuohuan" height="24" src="/zixia/picture" width="24" /></a>
				<a href="/zixia">Li Zhuohuan</a>
			</li>

			<li>
				<a href="/nullgate"><img alt="Nullgate" height="24" src="/nullgate/picture" width="24" /></a>
				<a href="/nullgate">Nullgate</a>
			</li>

			<li>
				<a href="/zany"><img alt="zany zeng" height="24" src="/zany/picture" width="24" /></a>
				<a href="/zany">Zany Zeng</a>
			</li>
		</ul>

<?php
	}

	static public function sidebar( $aList=array(), $idUser=null )
	{
		if ( empty($idUser) ){
			$idUser = JWUser::GetCurrentUserId();
		}

		$aUserInfo = JWUser::GetUserInfoById($idUser);
?>

    <div id="sidebar" class="static">
<?php
		foreach ( $aList as $partFunc ){
			$partFunc = 'sidebar_' .$partFunc;

			if ( method_exists('JWTemplate',$partFunc) ){
				call_user_func( array('JWTemplate',$partFunc), $aUserInfo );
			}else{
				throw new JWException("unsupport sidebar function $partFunc");
			}
		}

/*
	now supported:
		self::sidebar_login_notice();
		self::sidebar_timeline_notice();
		self::sidebar_login();
		self::sidebar_register();
		self::sidebar_featured();
		self::sidebar_status()
		self::sidebar_count()
		self::sidebar_jwvia()
		self::sidebar_search()
		self::sidebar_friend()
		self::sidebar_active()
*/


?>
	</div><!-- sidebar -->

<?php
	}

	static function sidebar_active($aUserInfo)
	{
?>
		<a href="/wo/device/">启用聊天软件！</a>
		<a href="/wo/device/">启用手机短信！</a>
<?php
	}


	static function sidebar_user_info($aUserInfo)
	{
?>
		<ul class="about">
			<li>姓名: <?php echo htmlspecialchars($aUserInfo['nameFull'])?></li>
<?php
			if ( !empty($aUserInfo['bio']) )
				echo "<li>简介: " . htmlspecialchars($aUserInfo['bio']) . "</li>\n";
			if ( !empty($aUserInfo['location']) )
				echo "<li>位置: " . htmlspecialchars($aUserInfo['location']) . "</li>\n";
			if ( !empty($aUserInfo['url']) )
			{
				if ( !preg_match('/^\w+:\/\//',$aUserInfo['url']) )
					$aUserInfo['url'] = 'http://' . $aUserInfo['url'];

				echo "<li>网站:  <a href='" . htmlspecialchars($aUserInfo['url'])
											. "' rel='" . htmlspecialchars($aUserInfo['nameFull']) 
											. "'>" . htmlspecialchars($aUserInfo['url']) . "</a></li>\n";
			}
?>
		</ul>
<?php
	}


	static function sidebar_user_notice($aUserInfo)
	{
		$nameScreen = $aUserInfo['nameScreen'];
?>
		<div class="msg">
			关于 <strong><?php echo $nameScreen?></strong>
		</div>
<?php
	}


	static function sidebar_timeline_notice()
	{
?>
		<div class="msg">
			JiWai.de <strong>叽歪广场</strong>
		</div>
<?php
	}


	static function sidebar_login_notice()
	{
?>
		<div class="msg">
			<h3>请登录！</h3>
		</div>
<?php
	}

		
	static function sidebar_count( $aUserInfo )
	{
		$user_stat_number = array(	'pm'			=> 0
									, 'fav'			=> 0
									, 'friend'		=> 0
									, 'follower'	=> 0
									, 'status'		=> 0
								);
?>

		<ul>
			<li id="message_count"><a href="/direct_messages">站内PM: <?php echo $user_stat_number['pm']?></a></li>
			<li id="favourite_count"><a href="/favorites">收藏夹: <?php echo $user_stat_number['fav']?></a></li>
			<li id="friend_count"><a href="/friends">叽歪友: <?php echo $user_stat_number['friend']?></a></li>
			<li id="follower_count"><a href="/followers">关注者: <?php echo $user_stat_number['follower']?></a></li>
			<li id="update_count">总共叽歪了 <?php echo $user_stat_number['status']?> 次</li>
		</ul>

<?php
	}


	static function sidebar_status( $aUserInfo )
	{
?>

		<div class="msg">
			欢迎回来，
			<strong><a href="/<?php echo $aUserInfo['nameScreen'];?>"><?php echo $aUserInfo['nameFull'];?></a></strong>
		</div>

		<ul>
			<li>当前：<em id="currently">打了2个小时的羽毛球，与DIV/CSS作战中……</em></li>
		</ul>

<?php
	}


	static function sidebar_jwvia( $aUserInfo )
	{
?>
		<ul>
			<li>
				<form action="/account/update_send_via" id="send_via_form" method="post" onsubmit="/*new Ajax.Request('/account/update_send_via', {asynchronous:true, evalScripts:true, parameters:Form.serialize(this)});*/ return false;">
					<fieldset>
						<h4>发送通知信息到:</h4>

						<input checked="checked" id="current_user_send_via_im" name="current_user[send_via]" onclick="$('send_via_form').onsubmit()" type="radio" value="im" />
						<label for="current_user_send_via_im">聊天软件</label>
						
						<input id="current_user_send_via_sms" name="current_user[send_via]" onclick="$('send_via_form').onsubmit()" type="radio" value="sms" />
						<label for="current_user_send_via_sms">手机</label>

						<input id="current_user_send_via_none" name="current_user[send_via]" onclick="$('send_via_form').onsubmit()" type="radio" value="none" />
						<label for="current_user_send_via_none">网页</label>
					</fieldset>
				</form>	
			</li>
		</ul>
<?php
	}


	static function sidebar_friend( $aUserInfo )
	{
?>
  		<div id="friend">
			<a href="/zixia" rel="contact" title="李卓桓"><img alt="zixia_ssh" height="24" src="/zixia/picture/thumb24?1171954960" width="24" /></a>
  		</div>
<?php
	}

	static function sidebar_search()
	{
?>
		<form action="/users/search" method="post" onsubmit="new Ajax.Updater('friend', '/users/search', {asynchronous:true, evalScripts:true, parameters:'name_number_or_email=' + $('user_search').value}); return false;">
			<fieldset class="user_search">
    			<input id="user_search" name="name_number_or_email" onclick="this.value=&quot;&quot;" type="text" value="输入id，email，手机号进行搜索" />
    			<input src="http://asset.jiwai.de/img/icon_search.gif" type="image" />
			</fieldset>
		</form>

		<script type="text/javascript">
//<![CDATA[
/*new Form.Element.Observer('user_search', 0.5, function(element, value) {new Ajax.Updater('friends', '/users/search', {asynchronous:true, evalScripts:true, parameters:'name_number_or_email=' + value})})*/
//]]>
		</script>

<?php
	}


	static public function rss ()
	{
?>
				<span class="statuses_options">
   			 		<a class="rss" href="/statuses/friends_timeline/762460.rss">RSS</a>
				</span>

<?php
	}


	static public function GetConst($constName)
	{
		self::Instance();

		if ( empty(self::$msJWConst) )
		{
			self::$msJWConst = array (	'UrlContactUs'			=>	'/wo/help/contact_us'
										,'UrlPublicTimeline'	=>	'/dajia/'
										,'UrlError404'			=>	'/wo/error/404'
										,'UrlError500'			=>	'/wo/error/500'

										,'UrlStrangerPicture'	=>	'http://asset.jiwai.de/img/stranger.gif'
								);
		}

		return @self::$msJWConst[$constName];
	}


	static public function UserSettingNav($activeMenu='account')
	{
		$arr_menu = array ( 'account'		=> array ( '/wo/account/setting', '帐号' )
							, 'password'	=> array ( '/wo/account/password', '密码')
							, 'device'		=> array ( '/wo/device/', '聊天软件/手机短信')
							, 'notification'=> array ( '/wo/account/notification', '通知')
							, 'picture'		=> array ( '/wo/account/picture', '头像')
							, 'uidesign'	=> array ( '/wo/account/uidesign', '界面')
						);
		echo '	<h4 id="settingsNav">';
		$first = true;
		foreach ( $arr_menu as $name=>$setting )
		{
			if ( $first )	$first = false;
			else 			echo ' | ';


			if ( $activeMenu === $name )
				echo " $setting[1] ";
			else
				echo " <a href=\"$setting[0]\">$setting[1]</a> ";
		}
		echo "\t</h4>\n";
	}
}
?>
