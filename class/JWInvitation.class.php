<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Invitation Class
 */
class JWInvitation {
	/**
	 * Instance of this singleton
	 *
	 * @var JWInvitation
	 */
	static private $msInstance;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWInvitation
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
	 *	idUser 邀请另外一个用户
	 *	当前用户邀请邮件地址加入网站
	 *	@idUser		int		发送邀请者idUser
	 *	@address	string	被邀请者的地址
	 *	@type		string	被邀请者地址的类型(msn/sms/email... )
	 *	@message	string	用户写给被邀者的话
	 *	@return		idUser	被邀请用户的 idUser，失败为 null;
	 */
	static public function Create($idUser, $address, $type, $message, $inviteCode)
	{
		$idUser		= intval($idUser);

		if ( 0>=$idUser )
			throw new JWException('id not int');

		if ( ! JWDevice::IsValid($address,$type) ){
			JWLog::Instance()->Log(LOG_NOTICE, "JWInvitation::Create($idUser, $address, $type, ...) found invalid address");
			return null;
		}

		if( $idExist = JWDB::ExistTableRow( 'Invitation', array(
							'idUser' => $idUser,
							'address' => $address,
							'type' => $type,
						))  
		){	
			$upArray = array(
					'message' => $message,
					'code' => $inviteCode,
					'timeCreate' => JWDB::MysqlFuncion_Now(),
				);

			JWDB::UpdateTableRow( 'Invitation', $idExist, $upArray );

			return $idExist;
		}

		return JWDB::SaveTableRow('Invitation',	array(
				       	'idUser'	=> $idUser
					,'address'	=> $address
					,'type'		=> $type
					,'message'	=> $message
					,'code'		=> $inviteCode
					,'timeCreate'	=> JWDB::MysqlFuncion_Now()
				));
	}

	/*
	 *	获取用户的 idInvitation
	 *	@param	int		$idUser	用户的id
	 *	@return	array	$invitation_ids
	 */
	static public function GetInvitationIdsFromUser($idUser, $num=JWDB::DEFAULT_LIMIT_NUM, $start=0)
	{
		$idUser	= intval($idUser);
		$num	= intval($num);
		$start	= intval($start);

		if ( !is_int($idUser) || !is_int($num) || !is_int($start) )
			throw new JWException('must int');

		$sql = <<<_SQL_
SELECT		id	as idInvitation
FROM		Invitation
WHERE		Invitation.idUser=$idUser
ORDER BY 	timeCreate desc
LIMIT 		$start,$num
_SQL_;

		$rows = JWDB::GetQueryResult($sql,true);

		if ( empty($rows) )
			return array();


		$invitation_ids = array();

		// 装换rows, 返回 id 的 array
		foreach ( $rows as $row )
			array_push($invitation_ids, $row["idInvitation"]);

		return $invitation_ids;
	}


	/*
	 *
	 *	@param	array	$addresses	array ( array('address'=>'','type'=>''), array(), array() );
	 *	@return	array	@invitation_ids
	 */
	static public function GetInvitationIdsFromAddresses($addresses)
	{
		if ( empty($addresses) )
			return array();

		if ( !is_array($addresses) )
			throw new JWException('must array');

		$condition_in = JWDB::GetInConditionFromArrayOfArray($addresses, array('address','type'), 'char');

		$sql = <<<_SQL_
SELECT	id as idInvitation
FROM	Invitation
WHERE	(address,type) IN ($condition_in)
_SQL_;

		$invitation_ids = array();

		try {
			$rows = JWDB::GetQueryResult($sql,true);
		} catch ( Exception $e ) {
			$sql_str = preg_replace("/\n",$sql);
			JWLog::LogFuncName("JWDB::GetQueryResult($sql_str,true) fail.");
			return array();
		}


		if ( empty($rows) )
			return array();

		foreach ( $rows as $row )
			array_push($invitation_ids, $row['idInvitation']);

		return $invitation_ids;
	}


	static public function GetInvitationIdFromAddress($address)
	{
		$invitation_ids = JWInvitation::GetInvitationIdsFromAddresses( array($address) );

		if ( empty($invitation_ids) )
			return null;

		return array_pop($invitation_ids);
	}


	/*
	 *
	 *
	 */
	static public function GetInvitationDbRowsByIds($idInvitations)
	{
		if ( empty($idInvitations) )
			return array();

		if ( !is_array($idInvitations) )
			throw new JWException('must array');

		$in_condition = JWDB::GetInConditionFromArray($idInvitations);

		$sql = <<<_SQL_
SELECT	*, id as idInvitation
FROM	Invitation
WHERE	id IN ( $in_condition )
_SQL_;

		$rows = JWDB::GetQueryResult($sql, true);
	
		$invitation_map = array();

		foreach ( $rows as $row )
			$invitation_map[$row['idInvitation']] = $row;
		
		return $invitation_map;
	}

	static public function GetInvitationDbRowById($idInvitation)
	{
		if ( empty($idInvitation) )
			return array();

		$invitation_db_rows = JWInvitation::GetInvitationDbRowsByIds(array($idInvitation));
		return $invitation_db_rows[$idInvitation];
	}

	
	/*
	 *	好像没有必要考虑 memcache
	 */
	static public function GetInvitationInfoByCode($code)
	{
		$row = JWDB::GetTableRow('Invitation', array('code'=>$code));
		$row['idInvitation'] = $row['id'];

		return $row;
	}

/* unknown function
	static public function GetInvitationrIdByCode($code)
	{
		$row = self::GetInvitationInfoByCode($code);
		return $row['idUser'];
	}
*/

	/*
	 *	设置 $idInvitations 之间互相为好友（包括邀请者）
	 *	@param	array of int	$idInvitations	邀请表的主键
	 */
	static public function SetReciprocal( $idInvitations )
	{
		if (empty($idInvitations))
			throw new JWException('no idInvitations');

		$idReciprocal = intval(max($idInvitations));

		$in_condition	= JWDB::GetInConditionFromArray($idInvitations);

		$sql = <<<_SQL_
UPDATE		Invitation
SET			idReciprocal=$idReciprocal
WHERE		id IN ( $in_condition )
_SQL_;
		// 这里用 Execute 是因为有 IN 条件。
		// 注意 Execute 无法 cache，能不用就不用！
		return JWDB::Execute($sql);
	}

	/*
	 *	获取属于某次 invite 的一群被邀者注册后的用户 id，以便于互相加为好友
	 *
	 */
	static public function GetReciprocalUserIds( $idInvitation )
	{
		$idInvitation = JWDB::CheckInt($idInvitation);

		$sql = <<<_SQL_
SELECT 	idInvitee
FROM	Invitation
WHERE	idReciprocal = 
		(
			SELECT	idReciprocal
			FROM	Invitation
			WHERE	id=$idInvitation
			LIMIT	1
		)
		AND idInvitee IS NOT NULL
_SQL_;
		$rows = JWDB::GetQueryResult($sql, true);

		$invitee_ids = array();

		if ( isset($rows) ) {
			foreach ( $rows as $row ) {
				array_push($invitee_ids, $row['idInvitee']);
			}
		}

		return $invitee_ids;
	}


	/*
	 *	设置用户接受邀请，
	 */
	static public function LogAccept($idInvitation)
	{
		$idInvitation = JWDB::CheckInt($idInvitation);

		$now = date(DATE_ATOM,time());

		return JWDB::UpdateTableRow('Invitation', $idInvitation, array('timeAccept'=>$now) );
	}


	/*
	 *	记录用户接受邀请后注册的用户帐号
	 */
	static public function LogRegister($idInvitation, $idInvitee)
	{
		$idInvitation 	= JWDB::CheckInt($idInvitation);
		$idInvitee 		= JWDB::CheckInt($idInvitee);

		$now = date(DATE_ATOM,time());

		$condition = array ( 'timeRegister'	=> $now
							,'idInvitee'	=> $idInvitee
						);

		return JWDB::UpdateTableRow('Invitation', $idInvitation, $condition);
	}


	static public function Destroy($idInvitation)
	{
		$idInvitation = JWDB::CheckInt($idInvitation);

		return JWDB::DelTableRow( 'Invitation', array( 'id'	=> $idInvitation ) );
	}

}
?>
