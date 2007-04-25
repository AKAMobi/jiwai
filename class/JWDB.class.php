<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Database Class
 */
class JWDB {
	/**
	 * Instance of this singleton
	 *
	 * @var JWDB
	 */
	static private $instance__;

	/**
	 * MySQLi DB Link
	 *
	 * @var JWConfig
	 */
	static private $mysqli_link__;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWDB
	 */
	static public function &instance()
	{
		if (!isset(self::$instance__)) {
			$class = __CLASS__;
			self::$instance__ = new $class;
		}
		return self::$instance__;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
		$db_config = JWConfig::instance();
		$db_config = $db_config->db;

		if ( !isset($db_config) )
			throw new JWException("DB can't find DB Config");

		self::$mysqli_link__ = new mysqli($db_config->host
				, $db_config->username
				, $db_config->passwd
				, $db_config->dbname
			);

		if (mysqli_connect_errno())
   			throw new JWException("Connect failed: " . mysqli_connect_error());

		if (!self::$mysqli_link__->set_charset("utf8"))
			throw new JWException("Error loading character set utf8: " . $mysqli->error);

	}

	static public function close()
	{
		return ;
		//XXX need to deal with more conditions. use function init_db? provent to init_db every time?
		if (isset(self::$mysqli_link__)){
			self::$mysqli_link__->close();
			self::$mysqli_link__ = null;
		}
	}

	static public function get_db()
	{
		if (empty(self::$mysqli_link__)){
			JWDB::instance();
		}

		return self::$mysqli_link__;
	}


	static public function escape_string( $string )
	{
		return self::get_db()->escape_string($string);
	}


	static public function Execute( $sql )
	{
		$db = self::get_db();

		if ( $result = $db->query($sql) ){
			return $result;
		}else{
			throw new JWException( "DB Query" );
		}
		// XXX here unreachable 
		throw new JWException( "unreachable" );
	}

	static public function get_query_result( $sql, $more_than_one=false )
	{
		//TODO need mysqli_real_escape_string, but it do escape through db server? damn it!
		$db = self::get_db();

		$aResult = null;

		if ( $result = $db->query($sql) ){

			if ( 0!==$result->num_rows && $more_than_one){
				$aResult = array();
			}

			while ( $row=$result->fetch_assoc() ){
				if ( $more_than_one ){ // array of assoc array
					array_push( $aResult, $row );
				}else{ // assoc array
					$aResult = $row;
					break;
				}
			}

		}else{
			throw new JWException( "DB Query" );
		}

		return $aResult;
	}

	/*
	 * 方便删除。
	 * @param condition array key为col name，val为条件值，多个条件的逻辑关系为AND
	 * @return 删除的行数
	 */
	static public function DelTableRow( $table, $condition )
	{
		$db = self::get_db();

		$sql = "DELETE FROM $table WHERE ";
		
		$first = true;
		foreach ( $condition as $k => $v ){
			if ( !$first )
				$sql .= " AND ";

			if ( is_int($v) )
				$sql .= " $k=$v ";
			else
				$sql .= " $k='" . self::escape_string($v) . "' ";

			if ( $first = true )
				$first = false;
		}
		// " WHERE $field='$value' AND field2=value2 ");

		$result = $db->query ($sql);

		if ( !$result ){
			throw new JWException ("DB Error: " . $db->error);
			return false;
		}
		return true;
	}


	/*
	 * @return true / false
	 */
	static public function ExistTableRow( $table, $condition )
	{
		$db = self::get_db();

		$sql = "SELECT 1 FROM $table WHERE ";
		
		$first = true;
		foreach ( $condition as $k => $v ){
			if ( !$first ){
				$sql .= " AND ";
			}

			if ( is_int($v) )
				$sql .= " $k=$v ";
			else
				$sql .= " $k='" . self::escape_string($v) . "' ";

			if ( $first = true )
				$first = false;
		}
		$sql .= ' LIMIT 1 ';
		// " WHERE $field='$value' AND field2=value2  LIMIT 1");

		$result = $db->query ($sql);

		if ( !$result ){
			throw new JWException ("DB Error: " . $db->error);
			return false;
		}
		return $result->num_rows!==0;
	}


	/*
	 * @return true / false
	 */
	static public function SaveTableRow( $table, $condition )
	{
		$db = self::get_db();

		$sql = "INSERT INTO $table ";
		
		$col_list = '';
		$val_list = '';

		$first = true;
		foreach ( $condition as $k => $v ){
			if ( !$first ){
				$col_list .= ",";
				$val_list .= ",";
			}

			$col_list .= "$k";

			if ( is_int($v) )
				$val_list .= "$v";
			else
				$val_list .= "'" . self::escape_string($v) . "'";

			if ( $first = true )
				$first = false;
		}
		$sql .= " ($col_list) values ($val_list) ";
		// " (field1,field2) values (value1,value2)";

		$result = $db->query ($sql);

		if ( !$result ){
			throw new JWException ("DB Error: " . $db->error);
			return false;
		}

		return true;
	}


	/*
	 * @return bool
			succ / fail
	 */
	static public function UpdateTableRow( $tableName, $idPK, $conditionArray )
	{
		if ( ! is_int($idPK) )
			throw JWException ("idPK need be int");

		$db = self::get_db();

		$sql = "UPDATE $tableName SET ";
		
		$first = true;
		foreach ( $conditionArray as $k => $v ){
			if ( !$first ){
				$sql .= " , ";
			}

			if ( is_int($v) )
				$sql .= "$k=$v";
			else
				$sql .= "$k='" . self::escape_string($v) . "'";

			if ( $first = true )
				$first = false;
		}
		$sql .= " WHERE id=$idPK";
		// " (field1,field2) values (value1,value2)";

		//die($sql);

		$result = $db->query ($sql);

		if ( !$result ){
			throw new JWException ("DB Error: " . $db->error);
			return false;
		}

		return $result;
	}



}
?>
