<?php
require_once("../../../jiwai.inc.php");

define ('GADGET_THEME_ROOT'		, JW_ROOT.'domain/asset/gadget/theme/');
define ('GADGET_THEME_DEFAULT'	, GADGET_THEME_ROOT.'iChat/');

//echo '<pre>'; die(var_dump($_REQUEST));

/*
<script type="text/javascript" 
		src="http://api.jiwai.de/gadget/statuses/1.js
			?count=20
			&selector={owner|friends|friends_newest}
			&theme=iChat" >
</script>
*/
#
# User URL Param
#
$selector	= @$_REQUEST['selector'];
$count		= @$_REQUEST['count'];
$theme		= @$_REQUEST['theme'];
$thumb		= @$_REQUEST['thumb'];


// rewrite param, may incluce the file ext name and user id/name
$pathParam	= $_REQUEST['pathParam'];


switch ($pathParam[0])
{
	case '/':
		// http://api.jiwai.de/gadget/timeline/1.js
		// output user_timeline

		# /1.js
		//if ( preg_match('/^\/(\d+)\.?([^/]*)$/',$pathParam,$matches) )
		if ( preg_match('/^\/(\d+)\.?([^\/]*)$/',$pathParam,$matches) )
		{
			$idUser		= $matches[1];
			$fileExt	= $matches[2];

			gadget($idUser, $selector, $theme, $count, $thumb);
			
		}
		else
		{
			// FIXME
			die ("UNSUPPORTED1");
		}

		break;

	case '.':
		// fall to default
	default:
		// http://api.jiwai.de/gadget/timeline.js
		// output public_timeline
		break;
}


exit(0);


function gadget($idUser, $statusSelector, $themeName, $countMax, $thumbSize)
{
	header ("Content-Type: text/javascript");

	switch (strtolower($statusSelector))
	{
		case 'friends':
			break;
		case 'friends_newest':
			break;
		case 'owner':
			// fall to default.
		default:
			$statusType = 'owner';
			break;
	}


	$theme_dir		= GADGET_THEME_ROOT . $themeName . '/';
	$theme_url		= 'http://asset.jiwai.de/gadget/theme/' . $themeName . '/';

	if ( !file_exists($theme_dir) )
	{
		error_log ("gadget can't find theme [$themeName] @ [$theme_dir]");
		$theme_dir 	= GADGET_THEME_DEFAULT;
	}
	//$theme_mtime	= filemtime($theme_dir);


	$countMax		= intval($countMax);
	if ($countMax<=0 || $countMax>40) 
		$countMax=JWStatus::DEFAULT_STATUS_NUM;


	$thumbSize	= intval($thumbSize);
	if ($thumbSize<=0) $thumbSize=24;
	else if (24!==$thumbSize) $thumbSize=48;



	$owner_content_template			= rawurlencode(file_get_contents("$theme_dir/Outgoing/Content.html"));
	$owner_next_content_template		= rawurlencode(file_get_contents("$theme_dir/Outgoing/NextContent.html"));

	$other_content_template			= rawurlencode(file_get_contents("$theme_dir/Incoming/Content.html"));
	$other_next_content_template	= rawurlencode(file_get_contents("$theme_dir/Incoming/NextContent.html"));


	$css_template		= file_get_contents("$theme_dir/main.css");
	
	$asset_number = 1;
	$count = 0;
	do
	{
		$css_template	= preg_replace("/ url\('(?!http)/i"
											, (" url('http://asset" . $asset_number%6 . ".JiWai.de/gadget/theme/$themeName/")
											, $css_template, 1, $count
									);
		$asset_number++;
	} while (0<$count);

	$css_template = rawurlencode($css_template);


	echo <<<_JS_

var jiwai_de_html_head = document.getElementsByTagName('head')[0];

/***************************** Load CSS Style ***************************************/
var css_template				= unescape('$css_template');

var owner_content_template 		= unescape('$owner_content_template');
var owner_next_content_template 	= unescape('$owner_next_content_template');

var other_content_template 		= unescape('$other_content_template');
var other_next_content_template	= unescape('$other_next_content_template');


//document.write('<style type="text/css">' + css_template + "</style>");

var gadget_css = document.createElement('style');
gadget_css.innerHTML = css_template;

jiwai_de_html_head.appendChild(gadget_css);
/***************************** CSS Style Loaded ***************************************/


jiwai_de_gadget 				= document.getElementById("jiwai_de_gadget")

/*
test_span			= document.createElement('span');
test_span.innerHTML	= "Hello, World! I'm Gadget!";

jiwai_de_gadget.appendChild(test_span);
*/


function relative_time(time_value) 
{
	var parsed_date = Date.parse(time_value);

	var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
	var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);

	if(delta < 60) {
		return '就在刚才'
	} else if(delta < (60*60)) {
		return (parseInt(delta / 60)).toString() + ' 分钟前';
	} else if(delta < (24*60*60)) {
		return (parseInt(delta / 3600)).toString() + ' 小时前';
	}

	return (parseInt(delta / 86400)).toString() + ' 天前';
}

function jiwai_de_get_message_html(status)
{
	return status.text 
			+ " <a href='http://jiwai.de/" + status.user.screen_name + "/' target='_blank'><small>" 
			+ relative_time(status.created_at)
			+ "</small></a>";
}

function jiwai_de_get_picture_html(status)
{
	return "<a href='http://JiWai.de/" + status.user.screen_name + "/' target='_blank'>"
			+ "<img class='icon' border='0' src='" + status.user.profile_image_url  + "' />";
			+ "</a>";
}
  
function jiwai_de_callback(statuses)
{

	if ( 0>=statuses.length )
		return;

	var statuses_html = "\\n";

	for ( n=0; n<statuses.length; n++ )
	{
		var status_html; // 每个 status
		var status_next_html; // 同一个用户的 next status
		var message_html // 所有 status 的 html;

		// 这条更新是用户自己的
		if ( $idUser==statuses[n].user.id )
		{
			status_html		= owner_content_template.replace(/%sender%/i	, statuses[n].user.name);
			status_html		= status_html.replace(/%userIconPath%/i		, jiwai_de_get_picture_html(statuses[n]));
			status_html		= status_html.replace(/%message%/i			, jiwai_de_get_message_html(statuses[n]));

			// 检查下一个(n+1) statuses 中的用户，是不是和当前用户(n)是同一人。如果是，则合并。
			while ( (n+1)<statuses.length && statuses[n+1].user.id==statuses[n].user.id )
			{
				status_next_html	= owner_next_content_template.replace(/%message%/i, jiwai_de_get_message_html(statuses[n+1]));
				status_html			= status_html.replace(/<div id='insert'><\\/div>/i, status_next_html);
				n++;
			}

			statuses_html 	+= status_html;
		}
		// 这条更新是用户的好友的
		else
		{
			status_html	= other_content_template.replace(/%sender%/i, statuses[n].user.name);
			status_html	= status_html.replace(/%userIconPath%/i 	, jiwai_de_get_picture_html(statuses[n]));
			status_html	= status_html.replace(/%message%/i			, jiwai_de_get_message_html(statuses[n]));

			// 检查下一个(n+1) statuses 中的用户，是不是和当前用户(n)是同一人。如果是，则合并。
			while ( (n+1)<statuses.length && statuses[n+1].user.id==statuses[n].user.id )
			{
				status_next_html	= other_next_content_template.replace(/%message%/i, jiwai_de_get_message_html(statuses[n+1]));
				status_html			= status_html.replace(/<div id='insert'><\\/div>/i, status_next_html);
				n++;
			}

			statuses_html += status_html;
		}
	}

	statuses_html += "<!-- [$idUser] [$statusType] [$themeName] [$countMax] -->";


	statuses_div 			= document.createElement('div');
	statuses_div.innerHTML 	= statuses_html;
	
	jiwai_de_gadget.appendChild(statuses_div);

}


// 加载 JSON 格式的用户数据，并传入回调，耶！
gadget_data_js 		= document.createElement("script");
gadget_data_js.src	= 'http://api.jiwai.de/statuses/public_timeline.json?callback=jiwai_de_callback';

jiwai_de_html_head.appendChild(gadget_data_js);
_JS_;

}
?>
