<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Message Class
 */
class JWMessage {
	/**
	 * Instance of this singleton
	 *
	 * @var JWMessage
	 */
	static private $msInstance;

	const	DEFAULT_NUM_MAX		= 9999;
	const	DEFAULT_MESSAGE_NUM	= 20;

	const	OUTBOX	= 1;
	const	INBOX	= 2;
	const 	NOTICE	= 3;

	const   MESSAGE_DELETE = 'delete';
	const   MESSAGE_HAVEREAD = 'haveRead';
	const   MESSAGE_NOTREAD = 'notRead';
	const   MESSAGE_NORMAL = 'normal';

	/**
	 * Instance of this singleton class
	 *
	 * @return JWMessage
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


	/*
	 *	@param	int	$time	unixtime
	 */
	static public function Create( $sender_id, $receiver_id, $message, $device='web', $options=array() )
	{
		//if(in_array($sender_id, array(40088)) return false;
		$sender_id = JWDB::CheckInt($sender_id);
		$receiver_id = JWDB::CheckInt($receiver_id);
		$time = isset($options['time']) ? intval($options['time']) : time();
		if ( 0>=$time )
			$time = time();

		$notice = isset($options['notice']) ? 'notice' : 'dm';

		$message_reply_id = abs(intval(@$options['reply_id'])) ? $options['reply_id'] : null;
		$message_status_sender = isset($options['delete']) 
			? 'delete' : 'haveRead';
		/* strip \r\n with \s */
		$message = preg_replace('[\r\n]',' ',$message);

		// // cut message for JW_HARDLEN_DB
		if ( defined( 'JW_HARDLEN_DB' ) )
		{
			$message = mb_substr($message, 0, JW_HARDLEN_DB, 'UTF-8');
		}

		return JWDB_Cache::SaveTableRow('Message', array(
					'idUserSender' => $sender_id,
					'idUserReceiver' => $receiver_id,
					'idMessageReplyTo' => $message_reply_id,
					'messageStatusSender' => $message_status_sender,
					'message' => $message,
					'device' => $device,
					'timeCreate' => JWDB::MysqlFuncion_Now($time),
					'messageType' => $notice,
					));
	}


	/*
	 * @param	int
	 * @return	bool
	 */
	static public function Destroy ($idMessage)
	{
		$idMessage = JWDB::CheckInt($idMessage);

		return JWDB_Cache::DelTableRow('Message', array ('id'=> $idMessage ));
	}


	/*
	 * @param	int		message pk
	 * @param	int		user pk
	 * @return	bool	if user own messsage ( either from or to )
	 */
	static public function IsUserOwnMessage ($idUser, $idMessage)
	{
		$idUser 	= intval($idUser);
		$idMessage	= intval($idMessage);

		if(	JWDB::ExistTableRow('Message', array (	 'id'			=> intval($idMessage)
						,'idUserSender'	=> intval($idUser)
						) ) )
			return JWMessage::OUTBOX;
		else if ( JWDB::ExistTableRow('Message', array (	 'id'				=> intval($idMessage)
						,'idUserReceiver'	=> intval($idUser)
						) ) )
			return JWMessage::INBOX;
		else
			return false;
	}


	/*
	 *	获取用户的 idMessage 
	 *	@param	int		$idUser	用户的id
	 *	@param	int	$type	INBOX or OUTBOX
	 *	@return	array	array ( 'message_ids'=>array(), 'user_ids'=>array() )
	 *	
	 *	根据 $type 选取 INBOX / OUTBOX ，返回的数组中，会自动将不是自己的用户的数据库col name命名为 idUser
	 */
	static public function GetMessageIdsFromUser($idUser, $type=JWMessage::INBOX, $num=JWMessage::DEFAULT_MESSAGE_NUM, $start=0, $idSince=null, $timeSince=null, $messageType=JWMessage::MESSAGE_NORMAL)
	{
		$idUser	= JWDB::CheckInt($idUser);
		$num	= JWDB::CheckInt($num);

		$condition_other = null;
		if( $timeSince ){
			$condition_other .= " AND timeCreate>'{$timeSince}'";
		}
		if ( ($idSince=abs(intval($idSince))) > 0 ) {
			$condition_other .= " AND id > {$idSince}";
		}

		switch ( $type )
		{
			default:
			case JWMessage::NOTICE:
			case JWMessage::INBOX :
				$where_col_name 	= 'idUserReceiver';
				$select_col_name	= ", idUserSender as idUser, idUserReceiver";
				break;
			case JWMessage::OUTBOX :
				$where_col_name 	= 'idUserSender';
				$select_col_name	= ", idUserSender, idUserReceiver as idUser";
				break;
		}

		$messageStatus=JWMessage::GetMessageStatusSql($type, $messageType);

		$sql = "SELECT		id as idMessage {$select_col_name}
			FROM		Message
			WHERE		{$where_col_name}={$idUser}
			{$condition_other} {$messageStatus}
			ORDER BY 	timeCreate desc
			LIMIT 		{$start}, {$num}";

		$rows = JWDB::GetQueryResult($sql,true);

		if ( empty($rows) )
			return array( 'message_ids'=>array() , 'user_ids'=>array() );


		/*
		 *	根据参数，创建 reduce_function，并存入 JWFunction 以备下次使用
		 */
		$func_key_name 		= "JWMessage::GetMessageIdsFromSender_idMessage";
		$func_callable_name	= JWFunction::Get($func_key_name);

		if ( empty($func_callable_name) )
		{
			$reduce_function_content = 'return $row["idMessage"];';
			$reduce_function_param 	= '$row';
			$func_callable_name 	= create_function( $reduce_function_param,$reduce_function_content );

			JWFunction::Set($func_key_name, $func_callable_name);
		}

		// 装换rows, 返回 id 的 array
		$message_ids = array_map(	 $func_callable_name
				,$rows
				);



		/*
		 *	根据参数，创建 reduce_function，并存入 JWFunction 以备下次使用
		 */
		$func_key_name 		= "JWMessage::GetMessageIdsFromSender_idUser";
		$func_callable_name	= JWFunction::Get($func_key_name);

		if ( empty($func_callable_name) )
		{
			$reduce_function_content = 'return $row["idUser"];';
			$reduce_function_param 	= '$row';
			$func_callable_name 	= create_function( $reduce_function_param,$reduce_function_content );

			JWFunction::Set($func_key_name, $func_callable_name);
		}

		// 装换rows, 返回 id 的 array
		$user_ids = array_map(	 $func_callable_name
				,$rows
				);

		array_push($user_ids, $idUser);

		return array ( 	 'message_ids'	=> $message_ids
				,'user_ids'		=> $user_ids
			     );
	}


	/*
	 *	根据 idMessage 获取 Row 的详细信息
	 *	@param	array	idMessages
	 * 	@return	array	以 idMessage 为 key 的 message row
	 * 
	 */
	static public function GetDbRowsByIds ($message_ids)
	{
		if ( empty($message_ids) )
			return array();

		if ( false==is_array($message_ids) )
			throw new JWException('must array');

		$message_ids = array_unique($message_ids);

		$condition_in = JWDB::GetInConditionFromArray($message_ids);

		$sql = "SELECT
			*, id as idMessage
			FROM    Message
			WHERE
			id IN ({$condition_in})";

		$rows = JWDB::GetQueryResult($sql,true);

		if ( empty($rows) )
		{
			$message_map = array();
		}
		else
		{
			$message_map = array();
			foreach ( $rows as $row ) {
				$message_map[$row['idMessage']] = $row;
			}

			$message_map = JWDB_Cache::SortArrayByKeyOrder($message_map, $message_ids);
		}

		return $message_map;
	}

	static public function GetDbRowById($message_id)
	{
		$message_db_rows = self::GetDbRowsByIds(array($message_id));

		if ( empty($message_db_rows) )
			return array();

		return $message_db_rows[$message_id];
	}


	static public function GetTimeDesc ($unixtime)
	{
		return JWStatus::GetTimeDesc($unixtime, true);
	}


	static public function FormatMessage ($message)
	{
		$formated_info = JWStatus::FormatStatus($message);
		return $formated_info['status'];
	}


	/*
	 *	@param	int		$idUser
	 *	@param	int		$type
	 *	@return	int		$messageNum for $idUser
	 */
	static public function GetMessageNum($idUser, $type=JWMessage::INBOX, $messageType=JWMessage::MESSAGE_NORMAL)
	{
		$idUser = JWDB::CheckInt($idUser);

		switch ( $type )
		{
			default:
			case JWMessage::INBOX :
				$col_name = 'idUserReceiver';
				break;
			case JWMessage::OUTBOX :
				$col_name = 'idUserSender';
				break;
		}

		$messageStatus=JWMessage::GetMessageStatusSql($type, $messageType); 

		$sql = "SELECT	COUNT(*) as num
			FROM	Message
			WHERE	{$col_name}={$idUser} {$messageStatus}";

		$row = JWDB::GetQueryResult($sql);

		return $row['num'];
	}

	/**
	 * @param int $idUser
	 * @return new inbox message
	 */
	static public function GetNewMessageNum($idUser) {
		return self::GetMessageStatusNum($idUser, JWMessage::INBOX, JWMessage::MESSAGE_NOTREAD);
	}

	/**
	 * @param int $idUser
	 * @return new notice message
	 */
	static public function GetNewNoticeMessageNum($idUser) {
		return self::GetMessageStatusNum($idUser, JWMessage::NOTICE, JWMessage::MESSAGE_NOTREAD);
	}

	/**
	 * @param int $idUser
	 * @return all notice message
	 */
	static public function GetAllNoticeMessageNum($idUser) {
		return self::GetMessageStatusNum($idUser, JWMessage::NOTICE, JWMessage::MESSAGE_NORMAL);
	}

	/**
	 * @param int $idUser
	 * @return all input message
	 */
	static public function GetAllInputMessageNum($idUser) {
		return self::GetMessageStatusNum($idUser, JWMessage::INBOX, JWMessage::MESSAGE_NORMAL);
	}

	/**
	 * @param int $idUser
	 * @return all out message
	 */
	static public function GetAllOutputMessageNum($idUser) {
		return self::GetMessageStatusNum($idUser, JWMessage::OUTBOX, JWMessage::MESSAGE_NORMAL);
	}


	/*
	 *	@param	int		$idUser
	 *	@param	int		$type
	 *	@param	enum('Y','N')		$messageStatus
	 *	@return	int		$messageStatusNum for $idUser
	 */
	static public function GetMessageStatusNum($idUser, $type=JWMessage::INBOX, $messageType=JWMessage::MESSAGE_NORMAL)
	{
		$idUser = JWDB::CheckInt($idUser);

		switch ( $type )
		{
			default:
			case JWMessage::INBOX :
				$col_name = 'idUserReceiver';
				break;
			case JWMessage::OUTBOX :
				$col_name = 'idUserSender';
				break;
		}

		$messageStatus=JWMessage::GetMessageStatusSql($type, $messageType); 


		$sql = "SELECT	COUNT(*) as num
			FROM	Message
			WHERE	{$col_name}={$idUser} {$messageStatus}";

		$row = JWDB::GetQueryResult($sql);
		return $row['num'];
	}



	/*
	 *	@param	int		$idMessage
	 *	@param	int		$type
	 *	@param	enum('Y','N')		$messageStatus
	 *	@return	
	 */
	static public function SetMessageStatus($idMessage, $type=JWMessage::INBOX, $messageType=JWMessage::MESSAGE_NORMAL)
	{
		if( is_numeric( $idMessage ) ) 
			$idMessage = JWDB::CheckInt( $idMessage );

		if( empty( $idMessage ) )
			return true;

		setType( $idMessage, 'array' );

		$idMessageString = implode( $idMessage, ',' );

		switch ( $type )
		{
			default:
			case JWMessage::INBOX :
				$message_type= 'messageStatusReceiver';
				break;
			case JWMessage::OUTBOX :
				$message_type= 'messageStatusSender';
				break;
		}

		$sql = "UPDATE Message SET $message_type = '$messageType' WHERE id IN ($idMessageString)";
		$flag = JWDB::Execute( $sql );

		self::ClearCache( $idMessage );

		return $flag;
	}

	static public function GetMessageStatusSql($type=JWMessage::INBOX, $messageType=JWMessage::MESSAGE_NORMAL )
	{
		$messageStatus = null;
		switch ( $type )
		{
			default:
			case JWMessage::INBOX :
				$message_type= 'messageStatusReceiver';
				$messageStatus = " AND messageType='dm'";
				break;
			case JWMessage::OUTBOX :
				$message_type= 'messageStatusSender';
				$messageStatus = " AND messageType='dm'";
				break;
			case JWMessage::NOTICE :
				$message_type= 'messageStatusReceiver';
				$messageStatus = " AND messageType='notice'";
				break;
		}

		switch( $messageType )
		{
			case JWMessage::MESSAGE_NORMAL:
				$messageStatus .= " AND ( {$message_type} = '". JWMessage::MESSAGE_NOTREAD."' OR  {$message_type} = '". JWMessage::MESSAGE_HAVEREAD."')";
				break;
			default:
				$messageStatus .= " AND {$message_type} = '{$messageType}'";
				break;
		}

		return $messageStatus;
	}
	
	/**
	 * Clear JWDB_Cache
	 */
	static public function ClearCache($idMessage) {
		if( is_numeric( $idMessage ) ) 
			$idMessage = JWDB::CheckInt( $idMessage );

		if( empty( $idMessage ) )
			return true;

		setType( $idMessage, 'array' );
		$rows = self::GetDbRowsByIds($idMessage);
		foreach( $rows AS $one ) {
			JWDB_Cache::OnDirty($one, 'Message');
		}
	}

}
?>
