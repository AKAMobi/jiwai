<?php
require_once("../../../jiwai.inc.php");
$callback = null;
$count = 20;
$since_id = null;
$since = null;
$page = 1;
$thumb = 48;
$pathParam = null;
extract( $_REQUEST, EXTR_IF_EXISTS );
$since = ($since) ? $since :  ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : null );
$page = ( $page < 1 ) ? 1 : intval($page);

if( false == preg_match( '/(.*)\.([[:alpha:]]+)$/', trim($pathParam,'/'), $matches ) ){
	JWApi::OutHeader(406,true);
}
@list($idUser, $type) = array($matches[1], $matches[2]);

if( false == in_array($type, array('xml','json','atom','rss'))){
	JWApi::OutHeader(406, true);
}

/**
 * Work around on nameScreen in Chinese, especially those encoded with GB2312
 */
$idUser = mb_convert_encoding($idUser, 'UTF-8', 'GB2312,UTF-8');

if( !$idUser && !($idUser=JWApi::GetAuthedUserId()) ){
	JWApi::RenderAuth( JWApi::AUTH_HTTP );
}

$user = JWUser::GetUserInfo( $idUser );
if( !$user ){
	JWApi::OutHeader(404, true);
}
$idUser = $user['id'];

/**
  * Friend check
  */
if( $idUser != ($idUserAuthed = JWApi::GetAuthedUserId()) && $user['protected']=='Y' ){
	if( !$idUserAuthed ){
		JWApi::RenderAuth( JWApi::AUTH_HTTP );
	}
	if( false == JWFollower::IsFollower($idUserAuthed, $idUser) ){
		JWApi::OutHeader(403, true);
	}
}

$options = array(
		'count' => intval($count),
		'since_id' => intval($since_id),
		'since' => $since,
		'thumb' => $thumb,
		'callback' => $callback,
		'idUser' => $idUser,
		'idConference' => $user['idConference'],
		'page' => $page,
		);

switch($type){
	case 'xml':
		renderXmlReturn($options);
	break;
	case 'json':
		renderJsonReturn($options);
	break;
	case 'atom':
		renderFeedReturn($options, $user, JWFeed::ATOM);
	break;
	case 'rss':
		renderFeedReturn($options, $user, JWFeed::RSS20);
	break;
	default:
		JWApi::OutHeader(406, true);
}

function renderXmlReturn($options){

	$statuses = getUserTimelineStatuses( $options, true );
	
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= '<?xml version="1.0" encoding="UTF-8"?>';
	$xmlString .= JWApi::ArrayToXml($statuses, 0, 'statuses');

	echo $xmlString;
}

function renderJsonReturn($options){
	$statuses = getUserTimelineStatuses( $options, true );
	if( $options['callback'] ){
		echo $options['callback'].'('. json_encode($statuses) .')';
	}else{
		echo json_encode($statuses);
	}
}

function renderFeedReturn($options, $user, $feedType=JWFeed::ATOM){

	$statuses = getUserTimelineStatuses( $options, false );

	$feed = new JWFeed(array(
				'title'	=> '叽歪 / '. $user['nameScreen'],
			       	'url'	=> 'http://JiWai.de/'.$user['nameScreen'] , 
				'desc'	=> $user['nameScreen'].'的叽歪de更新' , 
				'ttl'	=> 40,
				)); 

	foreach ( $statuses as $status ){

		$feed->AddItem(array( 
				'title'	=> JWApi::RemoveInvalidChar($status['status']) , 
				'desc'	=> $status['user']['nameScreen'] . ' - ' . JWApi::RemoveInvalidChar($status['status']) , 
				'date'	=> strtotime( $status['timeCreate'] ), 
				'author'=> $status['user']['nameScreen'] , 
				'guid'	=> "http://JiWai.de/" . $status['user']['nameUrl'] . "/statuses/" . $status['idStatus'] , 
				'url'	=> "http://JiWai.de/" . $status['user']['nameUrl'] . "/statuses/" . $status['idStatus'],
				));
	}
	$feed->OutputFeed($feedType);
	exit(0);
}

/*
 * 	return public timeline as a array
 *	@param	array	options, include: count, since_id, since
 *
 */
function getUserTimelineStatuses($options, $needReBuild=false){
	/* Twitter compatible */

	$count	= intval($options['count']);
	if ( 0>=$count )
		$count = JWStatus::DEFAULT_STATUS_NUM;
	
	$page = intval($options['page']);
	if( 1>=$page ) $page = 1;
	$start = $count * ( $page - 1 );

	//TODO: since_id / since
	$timeSince = ($options['since']==null) ? null : date("Y-m-d H:i:s", strtotime($options['since']) );

	if( $options['idConference'] ) {
		$status_data    = JWStatus::GetStatusIdsFromConferenceUser($options['idUser'], $count, $start );
	}else{
		$status_data    = JWStatus::GetStatusIdsFromUser($options['idUser'], $count, $start, $options['since_id'], $timeSince);
	}
	$status_rows	= JWStatus::GetStatusDbRowsByIds($status_data['status_ids']);
	$user_rows	= JWUser::GetUserDbRowsByIds($status_data['user_ids']);

	$statuses = array();

	foreach ( $status_data['status_ids'] as $status_id ){
		$user_id = intval($status_rows[$status_id]['idUser']);
		
		$statusInfo = ($needReBuild) ?
		       	JWApi::ReBuildStatus( $status_rows[$status_id] ) : $status_rows[$status_id];
		$userInfo   = ($needReBuild) ?
			JWApi::ReBuildUser($user_rows[$user_id]) : $user_rows[$user_id];
		$statusInfo['user'] = $userInfo;
		$statuses[] = $statusInfo;
	}

	return $statuses;
}
?>
