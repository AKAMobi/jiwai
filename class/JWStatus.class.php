<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Status Class
 */
class JWStatus {
	/**
	 * Instance of this singleton
	 *
	 * @var JWStatus
	 */
	static private $msInstance = null;

	const	DEFAULT_STATUS_NUM	= 20;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWStatus
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


	static public function Create( $idUser, $status, $device='web' )
	{
		$db = JWDB::Instance()->GetDb();

		if ( $stmt = $db->prepare( "INSERT INTO Status (idUser,status,device) "
								. " values (?,?,?)" ) ){
			if ( $result = $stmt->bind_param("iss"
											, $idUser
											, $status
											, $device
								) ){
				if ( $stmt->execute() ){
					$stmt->close();
					return true;
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
	 *	获取用户的 idStatus 
	 *	@param	int		$idUser	用户的id
	 *	@return	array	array ( 'status_ids'=>array(), 'user_ids'=>array() )
	 */
	static public function GetStatusIdFromUser($idUser, $num=DEFAULT_STATUS_NUM, $start=0)
	{
		$idUser	= intval($idUser);
		$num	= intval($num);
		$start	= intval($start);

		if ( !is_int($idUser) || !is_int($num) || !is_int($start) )
			throw new JWException('must int');

		$sql = <<<_SQL_
SELECT		Status.id	as idStatus
FROM		Status, User
WHERE		Status.idUser=User.id
ORDER BY 	Status.timestamp desc
LIMIT 		$start,$num
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);

		// 装换rows, 返回 id 的 array
		$status_ids = array_map(	create_function(
												'$row'
												, 'return $row["idStatus"];'
											)
							, $rows
						);
		return array (	'status_ids'	=> $status_ids
						,'user_ids'		=> array($idUser)
					);
	}


	/*
	 *	获取用户和好友的 idStatus，并返回相关的 idUser 以供后期组合
	 *	@param	int		$idUser	用户的id
	 *	@return	array	array ( 'status_ids'=>array(), 'user_ids'=>array() )
	 */
	static public function GetStatusIdFromFriends($idUser, $num=DEFAULT_STATUS_NUM, $start=0)
	{
		$idUser	= intval($idUser);
		$num	= intval($num);
		$start	= intval($start);

		if ( !is_int($idUser) || !is_int($num) || !is_int($start) )
			throw new JWException('must int');

		$sql = <<<_SQL_
SELECT
		Status.id	as idStatus
		, User.id as idUser
FROM	
		Status, User
WHERE	
		User.id IN
		(
			SELECT	idFriend
			FROM	Friend
			WHERE	idUser=$idUser
			
			UNION
				SELECT $idUser as idFriend
		)
		AND Status.timestamp > (NOW()-INTERVAL 1 WEEK)
		AND Status.idUser=User.id
ORDER BY
		Status.timestamp desc
LIMIT 
		$start,$num
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);

		// 装换rows, 返回 idStatus / idUser 的 array
		$status_ids = array_map(	create_function(
												'$row'
												, 'return $row["idStatus"];'
											)
							, $rows
						);
		$user_ids 	= array_map(	create_function(
												'$row'
												, 'return $row["idUser"];'
											)
							, $rows
						);

		return array ( 	 'status_ids'	=> $status_ids
						,'user_ids'		=> $user_ids
					);
	}


	/*
	 *	获取 public_timeline 的 idStatus 
	 *	@return	array	array ( 'status_ids'=>array(), 'user_ids'=>array() )
	 */
	static public function GetStatusIdFromPublic($num=DEFAULT_STATUS_NUM, $start=0)
	{
		$num	= intval($num);
		$start	= intval($start);

		if ( !is_int($num) || !is_int($start) )
			throw new JWException('must int');

		$sql = <<<_SQL_
SELECT		
			Status.id	as idStatus
			User.id		as idUser
FROM		
			Status
WHERE		
			Status.idUser=User.id
			AND User.idPicture>0
ORDER BY 	
			Status.timestamp desc
LIMIT 		$start,$num
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);

		// 装换rows, 返回 id 的 array
		$status_ids = array_map(	create_function(
												'$row'
												, 'return $row["idStatus"];'
											)
							, $rows
						);
		$user_ids 	= array_map(	create_function(
												'$row'
												, 'return $row["idUser"];'
											)
							, $rows
						);

		return array ( 	 'status_ids'	=> $status_ids
						,'user_ids'		=> $user_ids
					);
	}


	/*
	 *	根据 idStatus 获取 Row 的详细信息
	 *	@param	array	idStatuses
	 * 	@return	array	以 idStatus 为 key 的 status row
	 * 
	 */
	static public function GetStatusRowById ($idStatuses)
	{
		if ( !is_array($idStatuses) )
			throw new JWException('must array');

		$reduce_function_content = <<<_FUNC_
			if ( !empty($v) )
				$v .= ',';

			return $v . intval($idStatus);
_FUNC_;
		$condition_in = array_reduce(	$idStatuses
										, create_function(
												'$v,$idStatus'
												,"$reduce_function_content"
											)
										,''
									);
		$sql = <<<_SQL_
SELECT
	id as idStatus
	, idUser
	, status
	, UNIX_TIMESTAMP(Status.timestamp) AS timestamp
	, device
FROM	Status
WHERE	idStatus IN ($condition_in)
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);


		foreach ( $rows as $row )
			$status_map[$row['idStatus']] = $row;


		return $status_map;
	}


	static public function GetTimeDesc ($unixtime)
	{

		$duration = time() - $unixtime;
		if ( $duration > 2*86400 ){
			return strftime("%Y-%m-%d 周%a %H:%M",$unixtime);
		}else if ( $duration > 86400 ){
			return strftime("%Y-%m-%d %H:%M",$unixtime);
			//return "1 天前";
		}else if ( $duration > 3600 ){ // > 1 hour
			$duration = intval($duration/3600);
			return "$duration 小时前";
		}else if ( $duration > 60 ){ // > 1 min
			$duration = intval($duration/60);
			return "$duration 分钟前";
		}else{ // < 1 min
			if ( $duration > 30 ){
				return "半分钟前";
			}else if ( $duration > 20 ){
				return "20 秒前";
			}else if ( $duration > 10 ){
				return "10 秒前";
			}else if ( $duration > 5 ){
				return "5 秒前";
			}else{
				return "就在刚才";
			}
		}
	}


	/*
	 * @param	int
	 * @return	bool
	 */
	static public function Destroy ($idStatus)
	{
		if ( !is_numeric($idStatus) ){
			throw new JWException("must be numeric! [$idStatus]");
		}

		return JWDB::DelTableRow('Status', array (	'id'	=> intval($idStatus) ));
	}


	/*
	 *	根据 idStatus 获取一条 status
	 *	@param	int		idStatus 
						TODO 未来考虑支持array?
	 * 	@return	array	status 信息 
	 * 
	 */
	static public function GetStatus ($idStatus)
	{
		$idStatus = intval($idStatus);

		if ( 0>=$idStatus )
			throw new JWException('must int');

		$sql = <<<_SQL_
SELECT
	id
	, idUser
	, status
	, UNIX_TIMESTAMP(Status.timestamp) AS timestamp
	, device
FROM	Status
WHERE	id=$idStatus
_SQL_;

		return JWDB::GetQueryResult($sql);
	}



	/*
	 * @param	int		status pk
	 * @param	int		user pk
	 * @return	bool	if user own status
	 */
	static public function IsUserOwnStatus ($idStatus, $idUser=null)
	{
		if ( null===$idUser )
			$idUser = JWUser::GetCurrentUserId();

		if ( !is_numeric($idStatus) || !is_numeric($idUser) )
			throw new JWException("must be int! [$idStatus] [$idUser]");

		return JWDB::ExistTableRow('Status', array (	'id'		=> intval($idStatus)
														,'idUser'	=> intval($idUser)
											) );
	}


	/*
	 *	@param	string	status
	 *
	 *	@return	array	formated status & other info
	 *					array ( 'status' => ..., 'replyto' => ... );
	 */
	static public function FormatStatus ($status)
	{
		$replyto	= null;
		if ( preg_match('/^@([\d\w._\-]+)\s/',$status,$matches) )
		{
			$replyto = $matches[1];
		}


		/* 
		 * if status contains URL, we split status str to 4 parts: url_before, url_domain, url_path, url_after.
		 *
		 * URL is ascii. UTF8 ascii is 1 byte so we use 0x00-0xff to match ascii.
		 * 		":" is 0x3a
		 *		"/" is 0x2F
		 *		' ' is 0x20
		 *
		 */
		if ( preg_match(	'/'
									// head_str
									. '^(.*?)'
									. 'http:\/\/'
										// url_domain
										. '([' . '\x00-\x1F' ./*' '*/ '\x21-\x2B' ./*','*/ '\x2D-\x2E' ./*'/'*/ '\x30-\x39' ./*':'*/ '\x3B-\x7F' . ']+)'
										// url_path
										. '([' . '\x00-\x1F' ./*' '*/ '\x21-\x7F' . ']*)'
									// tail_str
									. '(.*)$/'
							, $status
							, $matches 
						) )
		{
			//var_dump($matches);
			$head_str		= htmlspecialchars($matches[1]);
			$url_domain		= htmlspecialchars($matches[2]);
			$url_path		= htmlspecialchars($matches[3]);
			$tail_str		= htmlspecialchars($matches[4]);

			if (!empty($url_path) && '/'!=$url_path[0])
			{
				$tail_str = $url_path . $tail_str;
				$url_path = '';
			}

			$url_str		= <<<_HTML_
<a href="#" target="_blank" onclick="urchinTracker('/wo/outlink/$url_domain$url_path'); 
						this.href='http://$url_domain$url_path';">http://$url_domain/...</a>
_HTML_;
			$status 		= $head_str . $url_str . $tail_str;
		}
		else
		{
			$status = htmlspecialchars($status);
		}

		return array ( 'status'		=> $status
						, 'replyto'	=> $replyto
					);
	}
	

	/*
	 *	@param	int		$idUser
	 *	@return	int		$statusNum for $idUser
	 */
	static public function GetStatusNum($idUser)
	{
		$idUser = intval($idUser);

		if ( !is_int($idUser) )
			throw new JWException('must be int');

		$sql = <<<_SQL_
SELECT	COUNT(*) as num
FROM	Status
WHERE	idUser=$idUser
_SQL_;
		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}


	/*
	 *	返回当前最大的Status的ID号
 	 */
	static public function GetMaxId()
	{
		$sql = <<<_SQL_
SELECT	MAX(id) as idStatus
FROM	Status
_SQL_;

		$result = JWDB::GetQueryResult($sql);
		return $result['idStatus'];
	}


	/*
	 *	获取在某一时刻之前的最大 idStatus
	 */
	static public function GetMaxIdStatusBeforeTime($unixtime)
	{
		$sql = <<<_SQL_
SELECT	MAX(id) as idMax
FROM	Status
WHERE	timestamp < FROM_UNIXTIME($unixtime)
_SQL_;
		$row = JWDB::GetQueryResult($sql);
		return $row['idMax'];
	}

}
?>
