<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de BindOther Class
 */
class JWBindOther {

	const AUTH_TWITTER = 'http://twitter.com/account/verify_credentials.xml';

	const POST_TWITTER = 'http://twitter.com/statuses/update.json';

	static public function Create( $idUser, $loginName='name', $loginPass='123456', $service='twitter' ) {

		$idUser = JWDB::CheckInt( $idUser );
		$service = strtolower( $service );

		switch( $service ) {
			case 'twitter':
				$flag = self::CheckTwitter( $loginName, $loginPass );
			break;
			default:
				$flag = false;
		}

		if( $flag ) {
			$idExist = JWDB::ExistTableRow('BindOther', array( 
				'idUser' => $idUser, 
				'service' => $service,
			));

			$uArray = array(
				'idUser' => $idUser, 
				'service' => $service,
				'loginName' => $loginName,
				'loginPass' => $loginPass,
				'enabled' => 'Y',
			);
			if( $idExist ) {
				JWDB::UpdateTableRow( 'BindOther', $idExist, $uArray );
				return $idExist;
			}else{
				$uArray['timeCreate'] = date('Y-m-d H:i:s');
				return JWDB::SaveTableRow( 'BindOther', $uArray );
			}
		}

		return false;
	}

	static public function Disable( $idBindOrder ) {
		$idBindOrder = JWDB::CheckInt( $idBindOrder );
		$uArray = array(
			'enabled' => 'N',
		);
		return JWDB::UpdateTableRow('BindOther', $idBindOrder, $uArray);
	}

	static public function GetBindOther( $idUser ) 
	{
		$idUser = JWDB::CheckInt( $idUser );
		$sql = "SELECT * FROM BindOther WHERE idUser=$idUser";
		$r = JWDB::GetQueryResult( $sql, true );
		if( empty($r) ) 
			return array();

		$rtn = array();
		foreach( $r as $one ) {
			$rtn[ $one['service'] ] = $one;
		}

		return $rtn;
	}

	static public function CheckTwitter( $loginName='name', $loginPass='123456' ) 
	{
		$authCode = Base64_Encode( "$loginName:$loginPass" );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::AUTH_TWITTER);  
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Authorization: Basic $authCode" ) );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0.010); 
		curl_exec($ch);
		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close($ch);

		return ( $httpCode == 200 );
	}

	static public function PostStatus( $bindOther=array(), $message=null ) 
	{
		if( empty( $bindOther ) || false == isset($bindOther['service']) )
			return true;

		if( $bindOther['enabled'] == 'N' )
			return true;

		$service = $bindOther['service'];
		$loginPass = $bindOther['loginPass'];
		$loginName = $bindOther['loginName'];

		switch( $service ) {
			case 'twitter':
				self::PostTwitter( $loginName, $loginPass, $message );
			break;
		}
		return true;
	}

	static public function PostTwitter( $loginName='name', $loginPass='123456', $message=null ) 
	{
		$authCode = Base64_Encode( "$loginName:$loginPass" );
		$postData = 'status='.urlEncode( $message );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::POST_TWITTER);  
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Authorization: Basic $authCode" ) );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0.010); 
		curl_exec($ch);
		curl_close($ch);
	}
}
?>