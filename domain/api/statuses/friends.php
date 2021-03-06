<?php
require_once("../../../jiwai.inc.php");

$pathParam = null;
extract($_REQUEST, EXTR_IF_EXISTS);

$pathParam = trim( $pathParam, '/' );
if( ! $pathParam ) {
	JWApi::OutHeader(400,true);
}

$authed = false;
$bound = strrpos($pathParam, '.');
if ($bound === false) {
	JWApi::OutHeader(406, true);
} else {
    $id = substr($pathParam, 0, $bound);
    $type = substr($pathParam, $bound + 1);
}
if( !in_array( $type, array('json','xml') )){
	JWApi::OutHeader(406, true);
}
if( !$id ) {
	$idUser = JWApi::GetAuthedUserId();
	if( !$idUser ){
		JWApi::RenderAuth(JWApi::AUTH_HTTP);
	}
	$authed = true;
}else{
	$_cUser = JWUser::GetUserInfo( $id );
	if( !$_cUser ){
		JWApi::OutHeader(404, true);
	}
	$idUser = $_cUser['id'];
}

switch( $type ){
	case 'json':
		renderJsonStatuses($idUser);
	break;
	case 'xml':
		renderXmlStatuses($idUser);
	break;
	default:
		JWApi::OutHeader(406, true);
}

function renderJsonStatuses($idUser){
    ob_start();
    ob_start("ob_gzhandler");
	$friendsWithStatus = getFriendsWithStatus( $idUser );
	echo json_encode( $friendsWithStatus );
    ob_end_flush();
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
}

function renderXmlStatuses($idUser){
    ob_start();
    ob_start("ob_gzhandler");
	$friendsWithStatus = getFriendsWithStatus( $idUser );
	$xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xmlString .= JWApi::ArrayToXml( $friendsWithStatus, 1, "users" );
	echo $xmlString;
    ob_end_flush();
    header('Content-Length: '.ob_get_length());
    ob_end_flush();
}

function getFriendsWithStatus($idUser)
{
	$friendIds = JWFollower::GetFollowingIds($idUser);
	$friends = JWDB_Cache_User::GetDbRowsByIds( $friendIds );
	$statusIds = array();
	foreach( $friendIds as $f )
	{
		$_rs = JWStatus::GetStatusIdsFromUser( $f, 1 );
		if( false == empty( $_rs ) && false==empty($_rs['status_ids']) ) 
		{
			$statusIds[$f] = $_rs['status_ids'][0];
		}
	}
	$statuses = JWStatus::GetDbRowsByIds( array_values($statusIds) );
	
	$friendsWithStatuses = array();
	foreach($friendIds as $f )
	{
		$user_row = $friends[$f];
		
		/* friend not publish any status */
		if ( false==isset( $statusIds[$f] ) )
			continue;

		$status_row = $statuses[ $statusIds[$f] ];
		$user_row['idPicture'] = ($status_row['idPicture'] && 'MMS' != $status_row['statusType']) 
			? $status_row['idPicture'] : $user_row['idPicture'];

		$statusInfo = JWApi::ReBuildStatus( $status_row );

		$userInfo = JWApi::ReBuildUser( $user_row );
		$userInfo['status'] = $statusInfo;

		$friendsWithStatuses[] = $userInfo;
	}
	return $friendsWithStatuses;
}
?>
