<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de User Class
 */
class JWUser {
	/**
	 * Instance of this singleton
	 *
	 * @var JWUser
	 */
	static private $msInstance = null;


	/**
	 * Reserved Named array, init when first be used
	 *
	 * @var msReservedNames
	 */
	static private $msReservedNames = null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWUser
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
	}

	static public function Logout()
	{
		self::ForgetRemembedUser();
		unset ($_SESSION['idUser']);
	}


	/*
	 * 	检查用户名/email 和 密码是否匹配
	 *	@return	bool	pass/fail
	 */
	static public function GetUserFromPassword($name_or_email, $pass)
	{
		$db = JWDB::GetDb();

		$idUser = null;

		$name_or_email	= $db->escape_string($name_or_email);
		$pass 			= $db->escape_string($pass);


		#
		# Step 1. get idUser & pass(md5) from DB
		#
		if ( strpos($name_or_email,'@') ){
			$sql = <<<_SQL_
SELECT	id as idUser, pass 
FROM	User 
WHERE	email=REVERSE('$name_or_email')
_SQL_;
		}else{ // nameScreen
			$sql = <<<_SQL_
SELECT	id as idUser, pass 
FROM 	User 
WHERE 	nameScreen='$name_or_email'
_SQL_;
		}

		$arr = JWDB::GetQueryResult($sql);

		if ( ! $arr )
			return false;

		$idUser = intval($arr['idUser']);

		#
		# Step 2. 检查密码是否匹配
		#
		if ( ! self::VerifyPassword($pass, $idUser) )
			return null;

		return $idUser;
	}


	static public function Login( $userIdOrName, $isRememberMe=true )
	{
		if ( preg_match("/^\d/",$userIdOrName) ){
			$idUser 	= $userIdOrName;
		}else{
			$user_info 	= self::GetUserInfoByName($userIdOrName);
			$idUser		= $user_info['id'];
		}
		$_SESSION['idUser'] = $idUser;

		if ( $isRememberMe )
			self::SetRememberUser();
		else
			self::ForgetRemembedUser();

		return true;
	}

	/*
	 * @param string
	 * @param int
	 * @return bool
	 */
	static function ChangePassword($plainPassword, $idUser=null)
	{
		// not permit empty pass
		if ( empty($plainPassword) )
			return false;

		if ( null===$idUser )
			$idUser = self::GetCurrentUserId();

		$md5_pass = self::CreatePassword($plainPassword);

		$sql = <<<_SQL_
UPDATE	User
SET		pass='$md5_pass'
WHERE	id=$idUser
_SQL_;
	
		return JWDB::Execute($sql);
	}


	/*
	 * @param string
	 * @return string
	 */
	static function CreatePassword($plainPassword)
	{
		$salt	= '$1$' . JWDevice::GenSecret(8) . '$';
		return	crypt($plainPassword, $salt);
	}


	/*
	 * @param	string
	 * @param	int
	 * @return	bool
	 */
	static public function VerifyPassword($password, $idUser)
	{
		if ( !$idUser )
			throw new JWException('must int');

		$md5_pass = self::GetUserInfoById($idUser,'pass');

		if ( crypt($password,$md5_pass)!=$md5_pass )
			return false;

		return true;
	}


	static public function MustLogined()
	{
		if ( self::IsLogined() ){
			return true;
		}

		$_SESSION['login_redirect_url'] = $_SERVER['SCRIPT_URI'];

		header ("Location: /wo/login"); 
		exit(0);
	}


	static public function GetCurrentUserId()
	{
		if ( self::IsLogined() )
			return intval($_SESSION['idUser']);

		return null;
	}


	/*
	 * 检查是否已经登录，或者是系统记住的登录用户
	 * @return true / false
	 */
	static public function IsLogined()
	{
		if ( array_key_exists('idUser',$_SESSION) )
			return true;

		$idUser = self::GetRememberUser();

		if ( isset($idUser) && is_int($idUser) )
		{
			$_SESSION['idUser'] = $idUser;
			return true;
		}
		
		return false;
	}


	/*
	 * 对客户端选择了“记住我”的cookie处理
	 * @return $idUser or null;
	 * 
	 */
	static function GetRememberUser()
	{
		$idUser = @$_COOKIE['JiWai_de_remembered_user_id'];
		$secret = @$_COOKIE['JiWai_de_remembered_user_code'];

		if ( empty($secret) || empty($idUser) )
			return null;
		

		if ( ! self::LoadRememberMe($idUser,$secret) )
		{
			setcookie('JiWai_de_remembered_user_id',	'', time()-3600, '/');
			setcookie('JiWai_de_remembered_user_code',	'', time()-3600, '/');
			return null;
		}

		// refresh browser cookie lifetime
		self::RefreshRememberUser();

		return intval($idUser);
	}


	/*
	 * @description refresh browser cookie lifetime.
	 * @return 		bool 
	 */
	static function RefreshRememberUser()
	{
		$id_user	= @$_COOKIE['JiWai_de_remembered_user_id'];
		$secret		= @$_COOKIE['JiWai_de_remembered_user_code'];

		if ( empty($secret) || empty($id_user) )
			return false;
	
		setcookie('JiWai_de_remembered_user_id', 	$id_user, time() + 31536000	, '/');
		setcookie('JiWai_de_remembered_user_code',	$secret	, time() + 31536000	, '/');

		return true;
	}


	/*
	 * @return bool 
	 */
	static function SetRememberUser()
	{
		$id_user = self::GetCurrentUserId();

		if ( empty($id_user) )
			return false;
			
		$secret = JWDevice::GenSecret(16);

		if ( ! self::SaveRememberMe($id_user,$secret) )
		{
			setcookie('JiWai_de_remembered_user_id'		, '' , time()-3600	, '/');
			setcookie('JiWai_de_remembered_user_code'	, '' , time()-3600	, '/');
			return false;
		}

		setcookie('JiWai_de_remembered_user_id', 	$id_user, time() + 31536000	, '/');
		setcookie('JiWai_de_remembered_user_code',	$secret	, time() + 31536000	, '/');
		
		return true;
	}


	/*
	 * @return bool
	 */
	static function ForgetRemembedUser()
	{

		$id_user = @$_COOKIE['JiWai_de_remembered_user_id'];
		$secret = @$_COOKIE['JiWai_de_remembered_user_code'];

		setcookie('JiWai_de_remembered_user_id'		, '', time()-3600, '/');
		setcookie('JiWai_de_remembered_user_code'	, '', time()-3600, '/');
		
		if ( isset($id_user) || isset($secret) )
			self::DelRememberMe($id_user,$secret);


		return true;
	}

	/*
	 * @return bool
	 */
	static function SaveRememberMe($idUser, $secret)
	{
		
		if ( empty($idUser) || empty($secret) || (!is_numeric($idUser)) )
			return false;

		return JWDB::SaveTableRow('RememberMe', array (	'idUser'	=>	intval($idUser)
													, 'secret'	=>	$secret
											) );
	}


	/*
	 * @return bool
	 */
	static function LoadRememberMe($idUser, $secret)
	{
		if ( empty($idUser) || empty($secret) || (!is_numeric($idUser)) )
			return false;
		

		return JWDB::ExistTableRow('RememberMe', array (	'idUser'	=> intval($idUser)
													, 'secret'	=> $secret
												)
						);
	}


	/*
	 * @return int
			deleted row num
	 */
	static function DelRememberMe($idUser, $secret)
	{
		if ( empty($idUser) || empty($secret) || (!is_numeric($idUser)) )
			return true;
		
		return JWDB::DelTableRow('RememberMe', array (	'idUser'	=> intval($idUser)
														, 'secret'	=> $secret
												) );
	}


	static public function GetCurrentUserInfo( $one_item=null )
	{
		if ( $id_user = self::GetCurrentUserId() )
		{
			$user_info = self::GetUserInfoById($id_user,$one_item);

			// maybe user be deleted in database.
			if ( !empty($user_info) )
				return $user_info;
			else
				self::Logout();
		}

		self::Logout();
		return null;
	}


	static public function GetUserInfoById( $idUser=null, $one_item=null )
	{
		return self::GetUserInfo('idUser',$idUser, $one_item);
	}

	static public function GetUserInfoByName( $nameScreen=null, $one_item=null )
	{
		return self::GetUserInfo('nameScreen',$nameScreen, $one_item);
	}


	/*
	 * @param	string			by_what		condition key
	 * @param	string			value		condition val, could be array in the furture
	 * @param	string			one_item 	column name, if set, only return this column.

	 * @return	array/string	user info 	array(string if one_item set). 
											(or array of array if val is array in the furture).
	 */
	static function GetUserInfo( $by_what, $value=null, $one_item=null )
	{
		switch ( $by_what ){
		case 'idUser':
			$by_what = 'id';
			if ( !is_int(intval($value)) ) return null;
			break;

		case 'nameScreen':
			if ( !self::IsValidName($value) ) return null;
			$value = JWDB::escape_string($value);
			break;

		case 'email':
			if ( !self::IsValidEmail($value) ) return null;
			// email need reverse 
			$value = JWDB::escape_string(strrev($value));
			break;


		default:
			throw new JWException("Unsupport get user info by $by_what");
		}

		$sql = <<<_SQL_
SELECT	*
FROM	User 
WHERE	$by_what='$value' LIMIT 1
_SQL_;

		//TODO memcache here.
		$aUserInfo 			= JWDB::GetQueryResult($sql);

		$aUserInfo['email']	= strrev($aUserInfo['email']);

		if ( empty($one_item) ){
			return $aUserInfo;
		}

		if ( isset($aUserInfo) && array_key_exists($one_item,$aUserInfo) ){
			return $aUserInfo[$one_item];
		}

		return null;
	}

	/*
	 * 修改用户信息
	 * @param 
			array() 内存修改过的用户信息，不需要修改的不要加入, 
			int		idUser, null为当前用户
		@return
			bool	成功/失败
	 */
	static public function Update($modifiedUserInfo, $idUser=null)
	{
		if ( null===$idUser )
			$idUser = self::GetCurrentUserId();

		if ( empty($modifiedUserInfo) )
			return false;

		if ( array_key_exists('email', $modifiedUserInfo) )
			$modifiedUserInfo['email'] = strrev($modifiedUserInfo['email']);

		return JWDB::UpdateTableRow('User', $idUser, $modifiedUserInfo);
	}


	/*
	 * make a user invisible.
	 * @param int
	 */
	static public function Delete( $idUser )
	{
		$sql = <<<_SQL_
DELETE 	FROM USER
WHERE	id=$idUser
_SQL_;
		// TODO
		return true;
	}


	/*
	 *	创建一个帐号
	 *	@param	userInfo	k => v 的用户信息
	 *	@return	idUser on success, false on fail
	 */
	static public function Create( $userInfo )
	{
		$db = JWDB::Instance()->GetDb();

		// Generate md5 password
		$userInfo['pass']	= self::CreatePassword($userInfo['pass']);

		if ( $stmt = $db->prepare( "INSERT INTO User (timeCreate,nameScreen,pass,email,nameFull,location,protected,isActive)"
								. " values (NOW(),?,?,?,?,?,?,?)" ) ){
			if ( $result = $stmt->bind_param("ssssss"
											, $userInfo['nameScreen']
											, $userInfo['pass']
											, strrev($userInfo['email'])
											, $userInfo['nameFull']
											, $userInfo['location']
											, $userInfo['protected']
											, $userInfo['isActive']
								) )
			{
				if ( $stmt->execute() ){
					$stmt->close();
					return JWDB::GetInsertId();
				}else{
					JWLog::Instance()->Log(LOG_ERR, $db->error );
				}
			}
		}else{
			JWLog::Instance()->Log(LOG_ERR, $db->error );
		}
		return false;
	}

	/*
	 * @desc	1、英文字母打头（为了方便的区分 nameScreen 和 idUser，禁止nameScreen以数字打头)
	 *			2、允许数字、字母、"."、"_"、"-"作为帐号字符
	 *			3、在底层，不限制长度
	 * @param	$name	nameScreen
	 * @return	bool	valid?
	 *
	 */
	static public function IsValidName( $name )
	{
		$regexp = '/^[[:alpha:]][\w\d_\-]+$/';

		$ret = preg_match($regexp, $name);

		if ( 1!==$ret )
			return false;

		return true;
	}

	static public function IsValidEmail( $email, $strict=false )
	{
		$valid = false;
		
		$regexp = '/^[\w\d._\-]+@[\w\d._\-]+$/';

		if ( preg_match($regexp, $email) ){
			if ( $strict ){
				list ($user,$domain) = split ('@', $email, 2);

				// we check A or MX is set
				if ( gethostbynamel($domain) || dns_get_mx($domain,$mxhosts) )
					$valid = true;
			}else{
				$valid = true;
			}
		}

		return $valid;
	}


	static public function IsExistEmail ($email)
	{
		return JWDB::ExistTableRow('User',array('email'=>strrev($email)));
	}


	/*
	 *	@param		string	nameScreen
	 *	@return 	bool	is exist
	 */
	static public function IsExistName ($nameScreen)
	{
		self::Instance();

		// XXX use db in the furture?
		if ( !isset(self::$msReservedNames) )
		{
			self::$msReservedNames = array (	
												'all'				=> true
												, 'api'				=> true
												, 'asset'			=> true
												, 'blog'			=> true
												, 'bug'				=> true
												, 'faq'				=> true
												, 'help'			=> true
												, 'jiwai'			=> true
												, 'jiwaide'			=> true
												, 'm'				=> true
												, 'mashup'			=> true
												, 'public_timeline'	=> true
												, 'sms'				=> true
												, 'team'			=> true
												, 'twitter'			=> true
												, 'wo'				=> true
												, 'www'				=> true
												, 'zixia'			=> true
											);
		}

		if ( isset(self::$msReservedNames[$nameScreen]) )
			return true;

		return JWDB::ExistTableRow('User',array('nameScreen'=>$nameScreen));
	}


	
	/*
	 *	设置用户的头像
 	 *	@param	idUser		int
	 *	@param	idPicture	int	图像的id，如果设置为null或者0，则删除用户头像。
	 *	@return
	 */
	static public function SetIcon($idUser, $idPicture=null)
	{
		// set 0 to disable
		if ( null===$idPicture )
			return JWDB::UpdateTableRow( 'User', $idUser, array ('idPicture' => '') );

		$idUser = intval($idUser);
		$idPicture = intval($idPicture);

		if ( 0>=$idPicture || 0>=$idPicture )
			throw new JWException('must int');

		// if enabled, we set the timestamp of new picture
		return JWDB::UpdateTableRow( 'User', $idUser, array ( 'idPicture' => $idPicture ) );
	}

	/*
	 * @return array ( pm => n, friend => x, follower=> )
	 */
	static public function GetState($idUser=null)
	{
		if ( null===$idUser )
			throw new JWException("no idUser");

		//TODO
		//$num_pm			= JWMessage::GetMessageNum($idUser);
		$num_fav		= JWFavorite::GetFavoriteNum($idUser);
		$num_friend		= JWFriend::GetFriendNum($idUser);
		$num_follower	= JWFollower::GetFollowerNum($idUser);
		$num_status		= JWStatus::GetStatusNum($idUser);

		return array(	'pm'			=> 0
						, 'fav'			=> $num_fav
						, 'friend'		=> $num_friend
						, 'follower'	=> $num_follower
						, 'status'		=> $num_status
					);
	}
	

	/*
	 *	获取用户的通知设置
	 *	@param	idUser				用户id
	 *	@return	notice_settings		设置的 $k => $v array，key 有：auto_nudge_me / send_new_friend_email / send_new_direct_text_email
	 */
	static public function GetNotification($idUser)
	{
		$user_info = self::GetUserInfoById($idUser);

		return array ( 	 'auto_nudge_me'	=> $user_info['noticeAutoNudge']
						,'send_new_friend_email'	=> $user_info['noticeNewFriend']
						,'send_new_direct_text_email'	=> $user_info['noticeNewMessage']
					);
	}


	/*
	 *	设置用户通知设置
	 *	@param	idUser			用户id
	 *	@param	noticeSettings	用户修改的设置 ( auto_nudge_me / send_new_friend_email / send_new_direct_text_email ), 
								如果isset,则设为 Y
	 */
	static public function SetNotification($idUser, $noticeSettings)
	{
		$db_change_set = array();
		$user_info	= self::GetUserInfoById($idUser);

		
		$noticeSettings['auto_nudge_me']				= isset($noticeSettings['auto_nudge_me']) 				? 'Y':'N';
		$noticeSettings['send_new_friend_email']		= isset($noticeSettings['send_new_friend_email']) 		? 'Y':'N';
		$noticeSettings['send_new_direct_text_email']	= isset($noticeSettings['send_new_direct_text_email']) 	? 'Y':'N';

		if ( $user_info['noticeAutoNudge']!=$noticeSettings['auto_nudge_me'] )
				$db_change_set['noticeAutoNudge'] = $noticeSettings['auto_nudge_me'];


		if ( $user_info['noticeNewFriend']!=$noticeSettings['send_new_friend_email'] )
				$db_change_set['noticeNewFriend'] = $noticeSettings['send_new_friend_email'];

		if ( $user_info['noticeNewMessage']!=$noticeSettings['send_new_direct_text_email'] )
				$db_change_set['noticeNewMessage'] = $noticeSettings['send_new_direct_text_email'];

		if ( !count($db_change_set) )
			return true;

//die(var_dump($db_change_set));
		$idUser	= intval($user_info['id']);

		return JWDB::UpdateTableRow('User', $idUser, $db_change_set);
	}
}
?>
