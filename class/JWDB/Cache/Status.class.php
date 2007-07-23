<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @date	  	2007/07/06
 */

/**
 * JiWai.de JWDB_Cache_Status Class
 */
class JWDB_Cache_Status implements JWDB_Cache_Interface
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWDB_Cache_Status
	 */
	static private $msInstance = null;

	static private $msMemcache	= null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWDB_Cache_Status
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
		self::$msMemcache = JWMemcache::Instance();
	}


	static function OnDirty($dbRow, $table=null)
	{
		self::Instance();
		/* 	取出 idPK 的 row，
		 *	然后依据自己表中的cache逻辑，得到相关其他应该 OnDirty 的 key
		 *	接下来一个一个的 OnDirty 过去
		 */ 

/*
echo "dbRow: \n";
var_dump($dbRow);
echo "dbRow End. \n";
*/

		$pk_id				= $dbRow['id'];
		$user_id			= $dbRow['idUser'];
		$reply_to_user_id	= $dbRow['idUserReplyTo'];

		$dirty_keys = array (	 
							 JWDB_Cache::GetCacheKeyById		('Status'	, $pk_id)

							,JWDB_Cache::GetCacheKeyByFunction	( array('JWStatus','GetStatusIdsFromUser')			,array($user_id) )
							,JWDB_Cache::GetCacheKeyByFunction	( array('JWStatus','GetStatusIdsFromFriends')		,array($user_id) )
							,JWDB_Cache::GetCacheKeyByFunction	( array('JWStatus','GetStatusIdsFromSelfNReplies')	,array($user_id) )

							,JWDB_Cache::GetCacheKeyByFunction	( array('JWStatus','GetStatusNum')					,array($user_id) )
							,JWDB_Cache::GetCacheKeyByFunction	( array('JWStatus','GetStatusNumFromReplies')		,array($user_id) )
							,JWDB_Cache::GetCacheKeyByFunction	( array('JWStatus','GetStatusNumFromSelfNReplies')	,array($user_id) )

					);

		if ( !empty($reply_to_user_id) )
		{
			array_push(	 $dirty_keys
						,JWDB_Cache::GetCacheKeyByFunction	( array('JWStatus','GetStatusIdsFromReplies')		,array($reply_to_user_id) )
						,JWDB_Cache::GetCacheKeyByFunction	( array('JWStatus','GetStatusIdsFromSelfNReplies')	,array($reply_to_user_id) )

						,JWDB_Cache::GetCacheKeyByFunction	( array('JWStatus','GetStatusNumFromSelfNReplies')	,array($reply_to_user_id) )
						,JWDB_Cache::GetCacheKeyByFunction	( array('JWStatus','GetStatusNumFromReplies')	,array($reply_to_user_id) )
					);
		}
/*
							"Status(id=$pk_id)"

								,"Status[GetStatusIdsFromUser($user_id)]"

								,"Status[GetStatusIdsFromSelfNReplies($reply_to_user_id)]"
								,"Status[GetStatusIdsFromReplies($reply_to_user_id)]"
								,"Status[GetStatusNumFromReplies($reply_to_user_id)]"
								,"Status[GetStatusNumFromSelfNReplies($reply_to_user_id)]"

								,"Status[GetStatusNum($user_id,$reply_to_user_id)]" 
							);
					*/

		foreach ( $dirty_keys as $dirty_key )
		{
			self::$msMemcache->Del($dirty_key);
		}
	}


	/**
	 *	FIXME: not support idSince & $timeScince param.
	 */
	static public function GetStatusIdsFromUser($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$max_num		= $start + $num;
		$mc_max_num	= JWDB_Cache::GetMaxCacheNum($max_num);

		// call back function & param
		$ds_function 	= array('JWStatus','GetStatusIdsFromUser');
		$ds_param		= array($idUser,$mc_max_num);
		// param to make memcache key
		$mc_param		= array($idUser);


		$mc_key 	= JWDB_Cache::GetCacheKeyByFunction	($ds_function,$mc_param);

		/*
		 *	对于过多的结果集，只保留一段时间
		 */
		if ( $mc_max_num > JWDB_Cache::NUM_LARGE_DATASET )
			$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS_LARGE_DATA;
		else
			$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$status_info	= JWDB_Cache::GetCachedValueByKey(
									 $mc_key
									,$ds_function
									,$ds_param
									,$expire_time
								);

		/**
		 *	存在可能：由于以前 cache 了 $num 较小数字时候的返回结果，导致直接取回了数量不足的结果集
		 *	所以对结果集的总数进行比对，如果数据不足，则删除数据，重新进行数据库查询
		 *
		 *	比如：1分钟前，有一次 GetStatusIdsFromReplies($num=100,$start=0)，则会 cache 住前 100 条数据
					现在，我们调用 GetStatusIdsFromReplies($num=20,$start=100)，则会获取到刚刚 cache 的 100 条数据，不足
					所以需要重新进行数据库查询，获取足够的数据：增加 $forceReload=true 的参数
		 *
		 */

		if ( ! JWDB_Cache::IsCachedCountEnough(count($status_info['status_ids']),$max_num) )
		{
			$status_info	= JWDB_Cache::GetCachedValueByKey(
										 $mc_key
										,$ds_function
										,$ds_param
										,$expire_time
										,true
									);
		}

		if ( !empty($status_info['status_ids']) )
		{
			$status_info['status_ids'] = array_slice(	 $status_info['status_ids']
														,$start,$num
													);
		}

		return $status_info;
	}


	/**
	 *	FIXME: not support idSince & $timeScince param.
	 */
	static public function GetStatusIdsFromFriends($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$max_num		= $start + $num;
		$mc_max_num	= JWDB_Cache::GetMaxCacheNum($max_num);

		// call back function & param
		$ds_function 	= array('JWStatus','GetStatusIdsFromFriends');
		$ds_param		= array($idUser,$mc_max_num);
		// param to make memcache key
		$mc_param		= array($idUser);


		$mc_key 	= JWDB_Cache::GetCacheKeyByFunction	($ds_function,$mc_param);

		/**
		 *	这个无法通过逻辑过期（性能问题），但是对实时性要求不高，所以 cache 1 min
		 */
		$expire_time	= 60;

		$status_info	= JWDB_Cache::GetCachedValueByKey(
									 $mc_key
									,$ds_function
									,$ds_param
									,$expire_time
								);

		/**
		 *	存在可能：由于以前 cache 了 $num 较小数字时候的返回结果，导致直接取回了数量不足的结果集
		 *	所以对结果集的总数进行比对，如果数据不足，则删除数据，重新进行数据库查询
		 *
		 *	比如：1分钟前，有一次 GetStatusIdsFromReplies($num=100,$start=0)，则会 cache 住前 100 条数据
					现在，我们调用 GetStatusIdsFromReplies($num=20,$start=100)，则会获取到刚刚 cache 的 100 条数据，不足
					所以需要重新进行数据库查询，获取足够的数据：增加 $forceReload=true 的参数
		 *
		 */

		if ( ! JWDB_Cache::IsCachedCountEnough(count($status_info['status_ids']),$max_num) )
		{
			$status_info	= JWDB_Cache::GetCachedValueByKey(
										 $mc_key
										,$ds_function
										,$ds_param
										,$expire_time
										,true
									);
		}

		if ( !empty($status_info['status_ids']) )
		{
			$status_info['status_ids'] = array_slice(	 $status_info['status_ids']
														,$start,$num
													);
		}

		return $status_info;
	}
	

	statIC PUBLic function GetStatusNumFromFriends($idUser)
	{
		// call back function & param
		$ds_function 	= array('JWStatus','GetStatusNumFromFriends');
		$ds_param		= array($idUser);
		// param to make memcache key
		$mc_param		= $ds_param;


		$mc_key 	= JWDB_Cache::GetCacheKeyByFunction	($ds_function,$mc_param);


		/**
		 *	这个无法通过逻辑过期（性能问题），但是对实时性要求不高，所以 cache 1 min
		 */
		$expire_time	= 60;

		return JWDB_Cache::GetCachedValueByKey(
									 $mc_key
									,$ds_function
									,$ds_param
									,$expire_time
								);


	}
	
	static public function GetStatusIdsFromReplies($idUser, $num=JWStatus::DEFAULT_STATUS_NUM, $start=0)
	{
		$max_num		= $start + $num;
		$mc_max_num		= JWDB_Cache::GetMaxCacheNum($max_num);

		// call back function & param
		$ds_function 	= array('JWStatus','GetStatusIdsFromReplies');
		$ds_param		= array($idUser,$mc_max_num);
		// param to make memcache key
		$mc_param		= array($idUser);

		$mc_key 	= JWDB_Cache::GetCacheKeyByFunction($ds_function,$mc_param);
		
		
		/*
		 *	对于过多的结果集，只保留一段时间
		 */
		if ( $mc_max_num > JWDB_Cache::NUM_LARGE_DATASET )
			$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS_LARGE_DATA;
		else
			$expire_time = JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		$status_info	= JWDB_Cache::GetCachedValueByKey(
									 $mc_key
									,$ds_function
									,$ds_param
									,$expire_time
								);

		/**
		 *	存在可能：由于以前 cache 了 $num 较小数字时候的返回结果，导致直接取回了数量不足的结果集
		 *	所以对结果集的总数进行比对，如果数据不足，则删除数据，重新进行数据库查询
		 *
		 *	比如：1分钟前，有一次 GetStatusIdsFromReplies($num=100,$start=0)，则会 cache 住前 100 条数据
					现在，我们调用 GetStatusIdsFromReplies($num=20,$start=100)，则会获取到刚刚 cache 的 100 条数据，不足
					所以需要重新进行数据库查询，获取足够的数据：增加 $forceReload=true 的参数
		 *
	 	 *	fixme: 翻到最后一页，会导致这里的逻辑认为数据量不足而失效
		 */

		if ( ! JWDB_Cache::IsCachedCountEnough(count($status_info['status_ids']),$max_num) )
		{
			$status_info	= JWDB_Cache::GetCachedValueByKey(
										 $mc_key
										,$ds_function
										,$ds_param
										,$expire_time
										,true
									);
		}

		if ( !empty($status_info['status_ids']) )
		{
			$status_info['status_ids'] = array_slice(	 $status_info['status_ids']
														,$start,$num
													);
		}

		return $status_info;
	}

	/*
	 *	规范命名方式，以后都应该是 GetDbRowsByIds 或者 GetDbRowById，不用在函数名称中加数据库表名
	 */
	static public function GetDbRowsByIds ($idStatuses)
	{
		return JWDB_Cache::GetCachedDbRowsByIds('Status', $idStatuses, array("JWStatus","GetDbRowsByIds") );
	}

	static public function GetDbRowById ($idStatus)
	{
		$status_db_rows = self::GetDbRowsByIds(array($idStatus));

		if ( empty($status_db_rows) )
			return array();

		return $status_db_rows[$idStatus];
	}

	static public function GetStatusNum($idUser)
	{
		// call back function & param
		$ds_function 	= array('JWStatus','GetStatusNum');
		$ds_param		= array($idUser);
		// param to make memcache key
		$mc_param		= $ds_param;

		$mc_key	= JWDB_Cache::GetCacheKeyByFunction($ds_function,$mc_param);
		
		$expire_time	= JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		return JWDB_Cache::GetCachedValueByKey(
									 $mc_key
									,$ds_function
									,$ds_param
									,$expire_time
								);
	}

	static public function GetStatusNumFromReplies($idUser)
	{
		// call back function & param
		$ds_function 	= array('JWStatus','GetStatusNumFromReplies');
		$ds_param		= array($idUser);
		// param to make memcache key
		$mc_param		= $ds_param;

		$mc_key 	= JWDB_Cache::GetCacheKeyByFunction($ds_function, $mc_param);
		
		$expire_time	= JWDB_Cache::PERMANENT_EXPIRE_SECENDS;

		return JWDB_Cache::GetCachedValueByKey(
									 $mc_key
									,$ds_function
									,$ds_param
									,$expire_time
								);
	}


	static public function GetStatusNumFromSelfNReplies($idUser)
	{
		// call back function & param
		$ds_function 	= array('JWStatus','GetStatusNumFromSelfNReplies');
		$ds_param		= array($idUser);
		// param to make memcache key
		$mc_param		= $ds_param;

		$mc_key 	= JWDB_Cache::GetCacheKeyByFunction($ds_function, $mc_param);
		
		$expire_time	= JWStatus::PERMANENT_EXPIRE_SECENDS;

		return JWDB_Cache::GetCachedValueByKey(
									 $mc_key
									,$ds_function
									,$ds_param
									,$expire_time
								);
	}
}
?>