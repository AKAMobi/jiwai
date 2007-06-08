<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de LogUserAction Class
 */
class JWLogUserAction {
	/**
	 * Instance of this singleton
	 *
	 * @var JWLogUserAction
	 */
	static private $msInstance;

	static private $msClientIp;
	static private $msProxyIp;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWLogUserAction
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
		self::$msClientIp 	= JWRequest::GetClientIp();
		self::$msProxyIp 	= JWRequest::GetProxyIp();
	}

	static public function OnLogin($idUser)
	{
		self::Instance();

		$ip 	= self::$msClientIp;
		$proxy 	= self::$msProxyIp;

		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
INSERT INTO	LogUserAction
SET			 action	='login'
			,idUser	= $idUser
			,ip 	= INET_ATON('$ip')
			,proxy 	= INET_ATON('$proxy')
_SQL_;

		JWDB::Execute($sql);
	}

	static public function OnRememberLogin($idUser)
	{
		self::Instance();

		$ip 	= self::$msClientIp;
		$proxy 	= self::$msProxyIp;

		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
INSERT INTO	LogUserAction
SET			 action='rememberlogin'
			,idUser	= $idUser
			,ip 	= INET_ATON('$ip')
			,proxy 	= INET_ATON('$proxy')
_SQL_;

		JWDB::Execute($sql);

	}


	static public function OnLogout($idUser)
	{
		self::Instance();

		$ip 	= self::$msClientIp;
		$proxy 	= self::$msProxyIp;

		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
INSERT INTO	LogUserAction
SET			 action='logout'
			,idUser	= $idUser
			,ip 	= INET_ATON('$ip')
			,proxy 	= INET_ATON('$proxy')
_SQL_;

		JWDB::Execute($sql);

	}

	static public function OnRememberMe($idUser)
	{
		self::Instance();

		$ip 	= self::$msClientIp;
		$proxy 	= self::$msProxyIp;

		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
INSERT INTO	LogUserAction
SET			 action='rememberme'
			,idUser	= $idUser
			,ip 	= INET_ATON('$ip')
			,proxy 	= INET_ATON('$proxy')
_SQL_;

		JWDB::Execute($sql);

	}


	static public function OnForgetMe($idUser)
	{
		self::Instance();

		$ip 	= self::$msClientIp;
		$proxy 	= self::$msProxyIp;

		$idUser = JWDB::CheckInt($idUser);

		$sql = <<<_SQL_
INSERT INTO	LogUserAction
SET			 action='forgetme'
			,idUser	= $idUser
			,ip 	= INET_ATON('$ip')
			,proxy 	= INET_ATON('$proxy')
_SQL_;

		JWDB::Execute($sql);

	}


}
?>