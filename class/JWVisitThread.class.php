<?php
/**
 * @package	 JiWai.de
 * @copyright   AKA Inc.
 * @author	  wqsemc@jiwai.com
 * @version	 $Id$
 */

/**
 * 
 */

class JWVisitThread
{
	/**
	 * Instance of this singleton
	 *
	 * @var 
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return 
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}

	static public function Record($idThread, $ip=null)
	{
		$ip = JWRequest::GetRemoteIp();
		$mc_key = self::GetCacheKeyByThreadIdAndIp($idThread, $ip); 
		$memcache = JWMemcache::Instance();

		$v = $memcache->Get( $mc_key );
		if( $v )
			return false;

		$memcache->Set( $mc_key, 1, 0, 600);
		self::SetCount($idThread);

		return true;

	}

	static public function GetCacheKeyThreadIds()
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitThread', 'GetCacheKeyThreadIds' ), array());
		return $mc_key;
	}

	static public function GetCacheKeyTotal()
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitThread', 'GetCacheKeyTotal' ), array());
		return $mc_key;
	}

	static public function GetCacheKeyByThreadId($idThread)
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitThread', 'GetCacheKeyByThreadId' ), array($idThread));
		return $mc_key;
	}

	static public function GetCacheKeyByThreadIdAndIp($idThread, $ip)
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitThread', 'GetCacheKeyByThreadIdAndIp' ), array($idThread, $ip));
		return $mc_key;
	}

	static public function SetCount($idThread)
	{
		$mc_key = self::GetCacheKeyByThreadId($idThread);
		$memcache = JWMemcache::Instance();

		return true;

		$v = $memcache->Get( $mc_key );
		if( !$v )
		{
			$v = 0;
			$memcache->Set( $mc_key, $v);
		}
		$mc_key2 = self::GetCacheKeyThreadIds();
		$v2 = $memcache->Get( $mc_key2 );
		if(!$v2)
			$v2 = array();

		array_push($v2, $idThread);
		$v2 = array_unique($v2);
		if(!$v2)
			$v2 = array();

		array_push($v2, $idThread);
		$v2 = array_unique($v2);
		$memcache->Set($mc_key2, $v2);

		$memcache->Set($mc_key, $v+1);
		return true;
	}

	static public function Update()
	{
		$memcache = JWMemcache::Instance();
		$mc_key2 = self::GetCacheKeyThreadIds();
		$idThreads = $memcache->Get( $mc_key2 );

		foreach($idThreads as $idThread)
		{
			$type = JWStatus::GetTypeById($idThread);
			$mc_key = self::GetCacheKeyByThreadId($idThread);
			$v = $memcache->Get( $mc_key );

			$status_info = JWStatus::GetDbRowById( $idThread );
			if(!empty($status_info))
			{
				$condition = array(
					'idThread' => $idThread,
					'count' => $v,
					'type' => $type,
				);
				$row = JWDB::SaveTableRow('VisitThread', $condition);
			}
			$memcache->Del($mc_key);
		}
		$memcache->Set($mc_key2, array());

		return true;
	}

	static public function Query($type ='normal', $limit = null)
	{
		$yesterday = date('Y-m-d', strtotime('1 days ago'));
		$today = date('Y-m-d', time());
		//$sql="select idThread,sum(count)as sum from VisitThread force index(IDX__VisitThread__timeStamp) where idThread is not null group by idThread order by sum desc";
		$sql="select idThread,sum(count) as sum from VisitThread force index(IDX__VisitThread__timeStamp) where timeStamp >='$yesterday' and timeStamp <'$today' and idThread is not null group by idThread order by sum desc";
		if (!empty($limit)) $sql .= " limit $limit";
		$row = JWDB::GetQueryResult($sql, true);

		if(empty($row))
			$row = array();
		$memcache = JWMemcache::Instance();
		$mc_key = self::GetCacheKeyTotal();
		$memcache->Set($mc_key, $row);

		return $row;
	}

	static public function Total($type ='normal', $limit=null)
	{
		$memcache = JWMemcache::Instance();
		$mc_key = self::GetCacheKeyTotal();
		$v = $memcache->Get($mc_key);
		if (!$v)
		{
			$v = self::Query($type, $limit);
		}

		return $v;
			
	}

	static public function GetCount($idThread)
	{
		$sql = "select sum(count) as sum from VisitThread where idThread=$idThread";
		$row = JWDB::GetQueryResult($sql);

		return $row['sum'];
	}
}
