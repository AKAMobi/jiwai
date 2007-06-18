<?php

$user = array();
extract($_REQUEST, EXTR_IF_EXISTS);


require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

//var_dump($_REQUEST);

$user_info		= JWUser::GetCurrentUserInfo();


$ui = new JWDesign($user_info['idUser']);


//var_dump($file_info);
if ( isset($_REQUEST['commit'] ) )
{
	
//echo "<pre>"; die(var_dump($_REQUEST));
//die(var_dump($user));
	$file_info = @$_FILES['profile_background_image'];
//die(var_dump($file_info));
	
	if ( ! $user['profile_use_background_image'] 
		&& !isset($file_info) )
	{
		// 不使用背景图片
		$user['profile_use_background_image'] = null;
	}
	else if ( isset($file_info) 
			&& 0===$file_info['error'] 
			&& preg_match('/image/',$file_info['type']) 
			)
	{
			
		$user_named_file = '/tmp/' . $file_info['name'];

		if ( move_uploaded_file($file_info['tmp_name'], $user_named_file) )
		{
			$idPicture	= JWPicture::SaveBg($user_info['id'], $user_named_file);
			if ( $idPicture )
			{
				$user['profile_use_background_image'] = $idPicture;
			}
			else
			{
				$contact_url = JWTemplate::GetConst('UrlContactUs');

				$error_html = <<<_HTML_
<li>上传图片失败，请检查图片图件是否损坏，或可尝试另选文件进行上载。如有疑问，请<a href="$contact_url">联系我们</a></li>
_HTML_;
				JWSession::SetInfo('error',$error_html);
			}

			unlink ( $user_named_file );
		}
	}
	else if ( isset($file_info) 
			&& $file_info['error']>0 
			&& 4!==$file_info['error']
			)
	{
		// PHP upload error, except NO FILE(that mean user want to delete).
		switch ( $file_info['error'] )
		{
			case UPLOAD_ERR_INI_SIZE:
				$error_html = <<<_HTML_
<li>头像文件尺寸太大了，请将图片缩小分辨率后重新上载。<li>
_HTML_;
				JWSession::SetInfo('notice',$error_html);
				break;
			default:
				throw new JWException("upload error $file_info[error]");
				break;
		}
	}

/*
  ["profile_background_color"]=>
  ["profile_use_background_image"]=>
  ["profile_background_tile"]=>
  ["profile_text_color"]=>
  ["profile_name_color"]=>
  ["profile_link_color"]=>
  ["profile_sidebar_fill_color"]=>
  ["profile_sidebar_border_color"]=>
*/

//die(var_dump($user));
	$ui->SetBackgroundColor	($user['profile_background_color']);
	$ui->SetUseBackgroundImage($user['profile_use_background_image']);
	$ui->SetBackgroundTile	($user['profile_background_tile']);
	$ui->SetTextColor		($user['profile_text_color']);
	$ui->SetNameColor		($user['profile_name_color']);
	$ui->SetLinkColor		($user['profile_link_color']);
	$ui->SetSidebarFillColor($user['profile_sidebar_fill_color']);
	$ui->SetSidebarBorderColor($user['profile_sidebar_border_color']);

//die(var_dump($ui));
	$ui->Save();

	header('Location: ' . $_SERVER['SCRIPT_URL']);
	exit(0);
}
else
{
	$ui->GetBackgroundColor	($user['profile_background_color']);
	$ui->GetUseBackgroundImage($user['profile_use_background_image']);
	$ui->GetBackgroundTile	($user['profile_background_tile']);
	$ui->GetTextColor		($user['profile_text_color']);
	$ui->GetNameColor		($user['profile_name_color']);
	$ui->GetLinkColor		($user['profile_link_color']);
	$ui->GetSidebarFillColor($user['profile_sidebar_fill_color']);
	$ui->GetSidebarBorderColor($user['profile_sidebar_border_color']);
}


//die(var_dump($user));

?>
<html>


<head>

<?php 
JWTemplate::html_head();


$asset_url_moorainbow_img_path	= JWTemplate::GetAssetUrl('/lib/mooRainbow/images/', false);
$asset_url_moorainbow_js		= JWTemplate::GetAssetUrl('/lib/mooRainbow/mooRainbow.js');
$asset_url_moorainbow_css		= JWTemplate::GetAssetUrl('/lib/mooRainbow/mooRainbow.css');

echo <<<_HTML_
<link href="$asset_url_moorainbow_css" media="screen, projection" rel="Stylesheet" type="text/css" />
<script src="$asset_url_moorainbow_js" type="text/javascript"></script>

_HTML_;

$color_ids = array 
( 
	 'user_profile_background_color'
	,'user_profile_text_color'
	,'user_profile_name_color'
	,'user_profile_link_color'
	,'user_profile_sidebar_fill_color'
	,'user_profile_sidebar_border_color'
);

echo <<<_HTML_
<script type="text/javascript">
//<![CDATA[

window.addEvent('domready', function() 
{

_HTML_;

foreach ( $color_ids as $color_id )
{
	$k = preg_replace('/^user_/','',$color_id);

	echo <<<_HTML_

	var default_bg_color = new Color('$user[$k]');
	var default_fg_color = default_bg_color.invert();

	$('$color_id').setStyle('background-color'	, default_bg_color);
	$('$color_id').setStyle('color'				, default_fg_color);


	var ${color_id}_r = new MooRainbow('$color_id', 
	{
		 id: '${color_id}_moo_id'
		//,startColor: [58, 142, 246]
		,startColor: default_bg_color
		,imgPath: '$asset_url_moorainbow_img_path'
		,wheel: true
		,onChange: function(color) 
		{
			$('$color_id').setStyle('background-color'	, color.hex);
			$('$color_id').setStyle('color'				, (new Color(color.hex)).invert());
			$('$color_id').value = color.hex;
		}
/*
		,onComplete: function(color)
		{
			$('$color_id').setStyle('background-color', color.hex);
			$('$color_id').value = color.hex;
		}
*/
	});

_HTML_;
}

echo <<<_HTML_

});

//]]>
</script>

_HTML_;

?>

</head>


<body class="account" id="profile_settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


			<h2> <?php echo $user_info['nameScreen']?> </h2>

<?php JWTemplate::UserSettingNav('profile'); ?>


<?php

if ( empty($error_html) )
	$error_html	= JWSession::GetInfo('error');

if ( empty($notice_html) )
{
	$notice_html	= JWSession::GetInfo('notice');
}

if ( !empty($error_html) )
{
		echo <<<_HTML_
			<div class="notice">头像未能上传：<ul> $error_html </ul></div>
_HTML_;
}

?>



<h3>设计您自己de叽歪档案</h3>
<p>
	下面是您当前叽歪档案的设计方案，<br />
	您可以随时修改、预览、保存设计方案，也可以非常容易的将其缺省值。
</p>

<form action="/wo/account/profile_settings" enctype="multipart/form-data" method="post"><fieldset>
<table cellspacing="0">
	<tr>
		<th><label for="user_profile_background_color">背景颜色：</label></th>

		<td><input id="user_profile_background_color" name="user[profile_background_color]" size="30" type="text" value="<?php echo $user['profile_background_color']?>" /></td>
	</tr>
	<tr>
		<th><label for="user_profile_background_image">背景图片：</label></th>
		<td>
			<input <?php 
						$picture_name 	= '无';
						$picture_id		= 0;
						if ( $user['profile_use_background_image'] )
						{
							$pic_db_row = JWPicture::GetDbRowById($user['profile_use_background_image']);
							
							if ( !empty($pic_db_row) )
							{
								echo ' checked="checked" ';
								$picture_name 	= $pic_db_row['fileName'] . '.' . $pic_db_row['fileExt'];
								$picture_id		= $pic_db_row['idPicture'];
							}
						}
					?>
			 id="user_profile_use_background_image" name="user[profile_use_background_image]" type="checkbox" value="<?php echo $picture_id?>" /><label for="user_profile_use_background_image">当前背景图片：<small>(<?php echo $picture_name?>)</small></label>
			&nbsp;&nbsp;

			<input id="user_profile_background_tile" <?php
														if ( $user['profile_background_tile'] )
															echo ' checked="checked" ';
													?>
					name="user[profile_background_tile]" type="checkbox" value="1" />
			<label for="user_profile_background_tile">平铺</label><br />
			<input id="user_profile_background_image" name="profile_background_image" size="30" type="file" />
		</td>
	</tr>
	<tr>
		<th></th>
		<td>
			
		</td>

	</tr>

	<tr>
		<th><label for="user_profile_text_color">文字颜色：</label></th>
		<td><input id="user_profile_text_color" name="user[profile_text_color]" size="30" type="text"
				 value="<?php echo $user['profile_text_color']?>" /></td>
	</tr>
	<tr>
		<th><label for="user_profile_name_color">名字颜色：</label></th>

		<td><input id="user_profile_name_color" name="user[profile_name_color]" size="30" type="text" 
				value="<?php echo $user['profile_text_color']?>" /></td>
	</tr>
	<tr>
		<th><label for="user_profile_link_color">链接颜色：</label></th>
		<td><input id="user_profile_link_color" name="user[profile_link_color]" size="30" type="text" 
				value="<?php echo $user['profile_link_color']?>" /></td>
	</tr>

	<tr>
		<th><label for="user_profile_sidebar_fill_color">侧栏填充色：</label></th>

		<td><input id="user_profile_sidebar_fill_color" name="user[profile_sidebar_fill_color]" size="30" type="text" 
				value="<?php echo $user['profile_sidebar_fill_color']?>" /></td>
	</tr>
	<tr>
		<th><label for="user_profile_sidebar_border_color">侧栏边框色：</label></th>
		<td><input id="user_profile_sidebar_border_color" name="user[profile_sidebar_border_color]" size="30" type="text" 
				value="<?php echo $user['profile_sidebar_border_color']?>" /></td>
	</tr>
	
  <input id="siv" name="siv" type="hidden" value="4fb7e754a2db9aa5b100da3b9c9e6de6" />
  
	<tr><th></th><td><input name="commit" type="submit" value="保存" /></td></tr>

	<tr><th></th><td><a href="/wo/account/restore_profile" onclick="return confirm('请确认您希望恢复叽歪de缺省设计方案？');">恢复叽歪de缺省配色方案</a></td></tr>
</table>
</fieldset>
</form>



		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>