<?php
function user_status($idPageUser, $idStatus)
{
	$status_info	= JWStatus::GetStatus($idStatus);
	$page_user_info	= JWUser::GetUserInfoById($idPageUser);

	if ( $status_info['idUser']!==$idPageUser )
	{
		$_SESSION['404URL'] = $_SERVER['SCRIPT_URI'];
		header ( "Location: " . JWTemplate::GetConst("UrlError404") );
		exit(0);
	}

	$logined_user_info	= JWUser::GetCurrentUserInfo();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<?php JWTemplate::html_head() ?>

<body class="status" id="show">

<?php JWTemplate::accessibility() ?>

<?php //JWTemplate::header() ?>

<div class="separator"></div>

<style type="text/css">
#content #permalink div.desc {
background:transparent none repeat scroll 0pt 50%;
}
#content div.desc {
background:transparent url(http://static.twitter.com/images/arr2.gif) no-repeat scroll 14px 0px;
margin-top:6px;
padding-top:11px;
}
#content div.desc h1 {
background:#FFFFFF none repeat scroll 0%;
display:block;
font-size:2.12em;
font-weight:bold;
line-height:1.2em;
padding:7px;
}

#content div.desc .meta {
font-size:0.98em;
font-weight:normal;
padding:2px 7px;
text-align:right;
}

#content #permalink h2 {
background:transparent url(http://static.twitter.com/images/arr.gif) no-repeat scroll 335px 0pt;
font-size:2em;
padding:16px 0pt 5px 321px;
}

#content h2.thumb img {
border:1px solid #999999;
vertical-align:middle;
}

h2.thumb, h2.thumb a {
color:#000000;
}

#ad {
padding:0px 0pt 5px 321px;
}
</style>
<div id="container">
	<div id="content">
		<div id="wrapper">


			<div id="permalink">
	
	
    			<div class="desc">

					<!-- google_ad_section_start -->

    				<h1>
    		  			<?php echo htmlspecialchars($status_info['status'])?>
    				</h1>

<?php
	$duration	= JWStatus::GetTimeDesc($status_info['timestamp']);

	$device = $status_info['device'];

	if ( 'sms'==$device )
		$device='手机';
	else
		$device=strtoupper($device);


?>
					<!-- google_ad_section_end -->

    				<p class="meta">
    					<span class="meta">
							<?php echo $duration?>
    				    	来自于 <?php echo $device?>
							<span id="status_actions_<?php echo $status_info['id']?>">
<?php 
if ( JWUser::IsLogined() )	
{
	$id_user_logined 	= JWUser::GetCurrentUserId();
	$is_fav				= JWFavourite::IsFavourite($id_user_logined,$status_info['id']);

	echo JWTemplate::FavouriteAction($status_info['id'],$is_fav);
	if ( $id_user_logined==$idPageUser ) {
		echo JWTemplate::TrashAction($idStatus);
	}
}
?>
							</span>
    					</span>
    				</p>
    			</div>

			<h2 class="thumb">
				<a href="/<?php echo $page_user_info['nameScreen']?>/"><img alt="<?php echo $page_user_info['nameFull']?>" src="<?php echo JWPicture::GetUserIconUrl($page_user_info['id'],'thumb48')?>" /></a>
				<a href="/<?php echo $page_user_info['nameScreen']?>/"><?php echo $page_user_info['nameFull']?></a>
			</h2>

			<div id="ad">
<script type="text/javascript"><!--
google_ad_client = "pub-8383497624729613";
google_ad_width = 234;
google_ad_height = 60;
google_ad_format = "234x60_as";
google_ad_type = "text_image";
//2007-04-30: JiWai.de - Statuses
google_ad_channel = "3074588979";
google_color_border = "99CC00";
google_color_bg = "C3E169";
google_color_link = "669900";
google_color_text = "333333";
google_color_url = "669900";

google_language = 'zh-CN';
//-->
</script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
<script type="text/javascript" id="google_show_ads">
</script>
			</div>

<script type="text/javascript"><!--
/*
setTimeout("show_ad()", 1000);
function show_ad()
{
alert(google_ad_width);
	$('google_show_ads').src="http://pagead2.googlesyndication.com/pagead/show_ads.js";
	ad_element = document.createElement("script")
	ad_element.type = "text/javascript";
	ad_element.language = 'javascript';
	ad_element.src 	= "http://pagead2.googlesyndication.com/pagead/show_ads.js"
	ad_element.id 	= "show_ads";
	document.getElementById('ad').appendChild(ad_element);
}
*/
</script>

		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->

<hr class="separator" />

<?php //JWTemplate::footer() ?>

</body>
</html>

<?php 
}  // end function
?>
