<?php
/**
 * @package		JiWai.de
 * @copyright		AKA Inc.
 * @author	 	shwdai@gmail.com 
 *
 */
class JWApi{
	/**
	 * Instance of JWApi
	 */
	static private $msInstance;

	const AUTH_HTTP = 1;
	const AUTH_BASIC = 1;
	const AUTH_OAUTH = 2;
	
	/**
	  * HttpCode
	  */
	static private $mHttpCode = array(
		"201" => "Created",
		"202" => "Accepted",
		"203" => "Non-Authoritative Information",
		"204" => "No Content",
		"205" => "Reset Content",
		"206" => "Partial Content",
		"300" => "Multiple Choices",
		"301" => "Moved Permanently",
		"302" => "Found",
		"303" => "See Other",
		"304" => "Not Modified",
		"305" => "Use Proxy",
		"306" => "(Unused)",
		"307" => "Temporary Redirect",
		"400" => "Bad Request",
		"401" => "Unauthorized",
		"402" => "Payment Required",
		"403" => "Forbidden",
		"404" => "Not Found",
		"405" => "Method Not Allowed",
		"406" => "Not Acceptable",
		"407" => "Proxy Authentication Required",
		"408" => "Request Timeout",
		"409" => "Conflict",
		"410" => "Gone",
		"411" => "Length Required",
		"412" => "Precondition Failed",
		"413" => "Request Entity Too Large",
		"414" => "Request-URI Too Long",
		"415" => "Unsupported Media Type",
		"416" => "Requested Range Not Satisfiable",
		"417" => "Expectation Failed",
		"500" => "Internal Server Error",
		"501" => "Not Implemented",
		"502" => "Bad Gateway",
		"503" => "Service Unavailable",
		"504" => "Gateway Timeout",
		"505" => "HTTP Version Not Supported",
		);	
	
	/**
	  * Get Authed UserId for API
	  */
	static function GetAuthedUserId()
	{
		if( JWLogin::IsLogined() )
		{
			return intval( $_SESSION['idUser'] );
		}
		if( isset( $_SERVER['PHP_AUTH_USER'] ) )
		{
            /* work around for WidSets */
			$_SERVER['PHP_AUTH_USER'] = urldecode($_SERVER['PHP_AUTH_USER']);
			$username_or_email = mb_convert_encoding($_SERVER['PHP_AUTH_USER'],'UTF-8','UTF-8,GB2312');
			$password = $_SERVER['PHP_AUTH_PW'];
			return JWUser::GetUserFromPassword( $username_or_email, $password );
		}
		if (isset($_REQUEST['oauth_consumer_key'])) {
			$p = $_REQUEST['pathParam'];
			try {
				self::$OAuthServer = JWOAuth::Server();
				$req = OAuthRequest::from_request();
				$t = self::$OAuthServer->verify_request($req);
				$_REQUEST['pathParam'] = $p;
				$_GET['pathParam'] = $p;
				self::$OAuthConsumer = $t[0];
				return $t[1]->idUser;
			} catch (OAuthException $e) {
				header('HTTP/1.1 401 Unauthorized');
				$_REQUEST['pathParam'] = $p;
				$_GET['pathParam'] = $p;
				//error_log('OAuth failed');
				return null;
			}
		}
		return null;
	}
	static $OAuthServer = null;
	static $OAuthConsumer = null;
	
	/**
	  * Offer an auth method for API
	  * @authType, given Auth Type, defined by self constants
	  *		now only support http auth.
	  */
	static function RenderAuth($authType=self::AUTH_HTTP){
		switch($authType){
			case self::AUTH_HTTP:
				self::RenderAuthHttp();
			break;
		}
		return;
	}
	
	/**
	  * Output HTTP Basic Auth Header for API Authentication
	  */
	static function RenderAuthHttp(){
		header('WWW-Authenticate: Basic realm="JiWai API"');
		//header('WWW-Authenticate: OAuth realm="JiWai API"');
		header('HTTP/1.0 401 Unauthorized');
		exit;
	}
	
	/**
	  * Rebuild User Array by given user db row.
	  */
	function ReBuildUser(&$user)
	{

		$user_info = array();

		$user_info['id'] = $user['id'];
		$user_info['name'] = $user['nameFull'];
		$user_info['screen_name'] = $user['nameScreen'];
		$user_info['profile_url'] = 'http://jiwai.de/'.$user['nameUrl'].'/';
		$user_info['description'] = $user['bio'];
		$user_info['location'] = JWLocation::GetLocationName( $user['location'] );
		$user_info['url'] = $user['url'];
		$user_info['protected'] = $user['protected']=='Y' ? true : false;

		$user_info['profile_image_url'] = JWPicture::GetUrlById( $user['idPicture'],'thumb48s');
        /* work around for Widsets, wanghw's fault */
		if ( isset($user['idMmsPicture']) )
            $user_info['mms_image_url'] = JWPicture::GetUrlById( $user['idMmsPicture'],'thumb96');

		return $user_info;
	}

	/**
	  * Rebuild Status Array by given status db row.
	  */
	  
	static function ReBuildStatus(&$status)
	{
		$out_info = array();
		
		if ( empty($status) )
			return $out_info;

		$out_info['created_at'] = date("D M d H:i:s O Y",strtotime($status['timeCreate']));
		$out_info['text'] = $status['status'];
		$out_info['id'] = $status['idStatus'];

		if( isset( $status['idPicture'] ) && $status['statusType'] == 'MMS' ) {
			$out_info['mms_image_url'] = JWPicture::GetUrlById( $status['idPicture'],'middle');
		}

		$current_user_id = self::GetAuthedUserId();
		if ( $current_user_id )
		{
			$out_info['favorited'] = JWFavourite::IsFavourite($current_user_id,$status['id']) ? true:false;
		}

		if( isset( $status['favourite_id'] ) )
		{
			$out_info['favourite_id'] = $status['favourite_id'];
		}
		if( isset( $status['device'] ) )
		{
			$out_info['device'] = $status['device'];
			if ( $status['device']=='api' && @$status['idPartner'] ) {
				$out_info['device'] = strip_tags(JWDevice::GetNameFromType($status['device'], $status['idPartner']));
			}
		}
		return $out_info;
	}

	/**
	  * Rebuild Message output, compatiable with twitter
	  */
	static function ReBuildMessage(&$message){

		$m_info = array();

		$m_info['id'] = isset($message['id']) ? $message['id'] : $message['idMessage'];
		$m_info['text'] = $message['message'];
		$m_info['sender_id'] = $message['idUserSender'];
		$m_info['recipient_id'] = $message['idUserReceiver'];
		$m_info['created_at'] = date("D M d H:i:s O Y", strtotime($message['timeCreate']));

		$senderUser = JWUser::GetUserInfo( $message['idUserSender'] );
		$receiverUser = JWUser::GetUserInfo( $message['idUserReceiver'] );

		$m_info['sender_screen_name'] = $senderUser['nameScreen'];
		$m_info['recipient_screen_name'] = $receiverUser['nameScreen'];
		$m_info['sender_profile_url'] = "http://jiwai.de/{$senderUser['nameUrl']}/";
		$m_info['recipient_profile_url'] = "http://jiwai.de/{$receiverUser['nameUrl']}/";
		
		return $m_info;
	}

	static function ArrayToXml($array, $level=1, $topTagName=''){
		$xml = '';
		if( $topTagName ){
			$xml .= str_repeat("\t",$level);
			$xml .= "<$topTagName>\n";
			$level += 1;
		}
		foreach ($array as $key=>$value) {
			if( is_numeric($key) ){
				$key = self::_GetXmlSubTagName($topTagName,$key);
			}
			$key = strtolower($key);

			if($value===false) $value='false';
			if($value===true) $value='true';

			if (is_array($value)) { // 大于一层的 assoc array
				//Add by seek 2007-06-14 4:45
				$subTagName = self::_GetXmlSubTagName($key);
				if( null != $subTagName ){
					$_subXml = null;
					foreach($value as $sv){
						$_subXml .= self::ArrayToXml($value, $level+1, $subTagName);
					}
				}else{
					$_subXml = self::ArrayToXml($value, $level+1, $key);
				}
				$xml .= $_subXml;
			} else { // 一层的 assoc array
				$value = self::RemoveInvalidChar( $value );
				if (htmlspecialchars($value)!=$value) {
					$xml .= str_repeat("\t",$level)
					."<$key><![CDATA[$value]]></$key>\n";
				} else {
					$xml .= str_repeat("\t",$level).
					"<$key>$value</$key>\n";
				}
			}
		}
		if( $topTagName ){
			$xml .= str_repeat("\t",$level-1);
			$xml .= "</$topTagName>\n";
		}
		return $xml;
	}
	
	/**
	  * Private function, just for build xml.
	  */
	static private function _GetXmlSubTagName($key=null, $default=null){
		switch($key){
			case 'users':
				return 'user';
			case 'statuses':
				return 'status';
			case 'friends':
				return 'friend';
			case 'direct_messages':
				return 'direct_message';
			default:
				if ( is_numeric($default) ) 
				{
					$n = rtrim($key, 's');
					if ( $n != $key )
					{
						return $n;
					}
				}
				return $default;
		}
	}

	/**
	  * Remove Invalid Control Char which will coz XML Breakdown.
	  */
	static public function RemoveInvalidChar($value){
		return $value = preg_replace('/[\x00-\x09\x0b\x0c\x0e-\x19]/U',"",$value);   
	}

	/**
	  * Render HTTP_Code, then exit;
	  */
	static public function OutHeader($code=404, $exit=true, $string=null){
		if( isset(self::$mHttpCode[$code]) ){
			Header("HTTP/1.1 $code ".self::$mHttpCode[$code]);
		}
		if( null != $string ) {
			echo $string;
		}
		if( $exit ){
			exit;
		}
	}
}
?>
