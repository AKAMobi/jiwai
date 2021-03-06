<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWLogin::MustLogined(false);
$element = JWElement::Instance();

$current_user_id = JWLogin::GetCurrentUserId();

$reply_message_id = null;
if ( preg_match('/^\/(\d+)$/',@$_REQUEST['pathParam'] ,$matches) ) {
	$reply_message_id = intval($matches[1]);
}

if ( ! $reply_message_id )
	JWTemplate::RedirectTo404NotFound();

$message = JWMessage::GetDbRowById( $reply_message_id );
if ( empty($message) )
	JWTemplate::RedirectTo404NotFound();

if ( $message['idUserReceiver'] != $current_user_id )
	JWTemplate::RedirectTo404NotFound();

$element = JWElement::Instance();
$param_dm = array( 'reply' => $message );
?>
<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="mar_b20">
		<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
		<div class="f">
			<?php $element->block_headline_dm($param_dm);?>
		</div>
		<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
	</div>
</div>

<div id="righter">
        <div class="a"></div><div class="b"></div><div class="c"></div><div class="
d"></div>
        <div id="rightBar" class="f" >
                <?php $element->side_wo_request_in();?>
                <?php $element->side_wo_hi();?>
                <?php $element->side_announcement();?>
                <div class="line mar_b8"></div>
                <?php $element->side_recent_vistor();?>
                <?php $element->side_whom_me_follow(array('url'=>'wo'));?>
                <?php $element->side_block_user();?>
                <?php $element->side_searchuser();?>
        </div>
        <div class="d"></div><div class="c"></div><div class="b"></div><div class="
a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div>

<?php $element->common_footer();?>
<?php $element->html_footer();?>
