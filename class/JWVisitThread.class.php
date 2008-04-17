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

	static public function Record($idThread, $ip)
	{

		$mc_key = self::GetCacheKeyByThreadIdAndIp($idThread, $ip); 
		$memcache = JWMemcache::Instance();

		$v = $memcache->Get( $mc_key );
		if( $v )
			return false;

		$memcache->Set( $mc_key, 1, 0, 1);
		self::SetCount($idThread);

		return true;

	}

	static public function GetCacheKeyThreadIds()
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitThread', 'GetCacheKeyThreadIds' ), array());
		return $mc_key;
	}

	static public function GetCacheKeyTotal($type)
	{
		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array( 'JWVisitThread', 'GetCacheKeyTotal' ), array($type));
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

		$v = $memcache->Get( $mc_key );
		if( !$v )
		{
			$v = 0;
			$memcache->Set( $mc_key, $v);
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
			if(10<=count($v2)) self::Update();
		}

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
			if(!$v) $v=1;

			$condition = array(
				'idThread' => $idThread,
				'count' => $v,
				'type' => $type,
			);
			$row = JWDB::SaveTableRow('VisitThread', $condition);
			$memcache->Set($mc_key, 0);
		}
		$memcache->Set($mc_key2, array());

		return true;
	}

	static public function Query($type ='normal', $limit = null)
	{
		$month = date("m");
		$day = date("d");
		$year = date("Y");
		$yesterday = date("Y-m-d", mktime (0, 0, 0, $month, $day-1, $year));
		$today = "$year-$month-$day";
		$sql="select idThread,sum(count)as sum from VisitThread force index(IDX__VisitThread__timeStamp) where type = '$type' group by idThread order by sum desc";
		//$sql="select idThread,sum(count)as sum from VisitThread force index(IDX__VisitThread__timeStamp) where timeStamp >='$yesterday' and timeStamp <'$today' and type = '$type' group by idThread order by sum desc";
		if (!empty($limit)) $sql .= " limit $limit";
		$row = JWDB_Cache::GetQueryResult($sql, true);

		if(empty($row))
			$row = array();
		$memcache = JWMemcache::Instance();
		$mc_key = self::GetCacheKeyTotal($type);
		$memcache->Set($mc_key, $row);

		return $row;
	}

	static public function Total($type ='normal', $limit=null)
	{
		$memcache = JWMemcache::Instance();
		$mc_key = self::GetCacheKeyTotal($type);
		$v = $memcache->Get($mc_key);
		if (true||!$v)
		{
			$v = self::Query($type, $limit);
		}

		return $v;
			
	}
}
