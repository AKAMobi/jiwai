<?php 
require_once( dirname(__FILE__) . '/../../jiwai.inc.php');
$element = JWElement::Instance();

$param_tab = array( 
		'tabtitle' => "[{$tag_name}]话题",
		);
$param_main = array(
		'tag_ids' => array($tag_row['id']),
		);
$param_head = array(
		'tag' => $tag_row,
		'tipnote' => '这里是 [帮助留言板]',
		);
$param_side = array(
		'tag' => $tag_row,
		);
?>

<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter">
	<div class="mar_b20">
		<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
		<?php $element->block_headline_tag($param_head);?>
		<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
	</div>

	<div>
		<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
		<div class="f">
			<?php $element->block_tab($param_tab);?>
			<?php $element->block_statuses_tag($param_main);?>
			<?php $element->block_rsslink();?>
		</div>
		<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
	</div>
</div>
<div id="righter">

<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_help($param_side);?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div>

<div class="clear"></div>
</div>

<?php $element->common_footer();?>
<?php $element->html_footer();?>
