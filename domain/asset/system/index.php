<?php
$pathParam = null;
extract($_REQUEST,EXTR_IF_EXISTS);

require_once("../../../jiwai.inc.php");
require_once(JW_ROOT . "webroot/user/picture.inc.php");


if ( preg_match('#^user/profile_image/(?P<id_or_name>\w+)/(?P<pic_id>\w+)/(?P<pic_size>\w+)/#',$pathParam,$matches) )
{
	$id_or_name = $matches['id_or_name'];
	$pic_id 	= $matches['pic_id'];
	$pic_size 	= $matches['pic_size'];

	JWPicture::Show($pic_id, $pic_size);
	exit(0);
}
?>