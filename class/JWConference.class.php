<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Conference Class
 */
class JWConference {
	/**
	 * Instance of this singleton
	 *
	 * @var JWConference
	 */
	static private $instance__;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWConference
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
	 * Get idConference from idUser,idUserReplyTo --- 这个只能从 回复的ID中分析，
	 * 对于特服号码相关的 会议号分析 
	 * 请使用 JWFuncCode::FetchConference(serverAddress, mobileNo)方法
	 *
	 * Deprecated by seek@jiwai.com 2008-03-22
	 */
	static public function FetchConference( $idSender,$idReceiver=null , $device='sms' ) 
	{
		$idSender = JWDB::CheckInt( $idSender );
	
		//发送者是开启了会议模式用户	
		$userSender = JWUser::GetUserInfo( $idSender );
		if( empty($userSender) ) {
			return array();
		}

		if( $userSender['idConference'] ){ // Simple...
			return self::GetDbRowById( $userSender['idConference'] );
		}

		//会议用户信息
		$userInfo = $conference = null;

		//从特服号中分析不出，分析 idUserReplyTo 
		if( $idReceiver && ( empty($userInfo) || empty($conference) ) ) 
		{
			$userInfo = JWUser::GetUserInfo( $idReceiver );
			if( false == empty( $userInfo ) && $userInfo['idConference'] ) {
				$conference = self::GetDbRowById( $userInfo['idConference'] );
			}
		}

		//分析 设备类型，好友允许设置
		if( false == empty( $userInfo ) && false == empty( $conference ) ) 
		{
			$deviceCategory = JWDevice::GetDeviceCategory( $device );
			$allowedDevice = $conference['deviceAllow'];
			if( in_array( $deviceCategory, explode(',', $allowedDevice) ) ){
				if( $conference['friendOnly'] == 'N' || JWFollower::IsFollower( $idSender, $idReceiver ) ) {
					return $conference;
				}
			}
		}

		return array();;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
	}

	/**
	 * Get Conference ById
	 */
	static public function GetDbRowById($idConference){
		$idConference = JWDB::CheckInt( $idConference );
		$sql = <<<_SQL_
SELECT 
	* 
FROM 
	Conference
WHERE 
	id = $idConference
_SQL_;

		$row = JWDB::GetQueryResult( $sql, false );

		return $row;
	}

	/**
	 * Get User Conference Setting
	 */
	static public function GetDbRowFromUser($idUser){
		$idUser = JWDB::CheckInt( $idUser );
		$sql = <<<_SQL_
SELECT 
	*
FROM
	Conference
WHERE
	idUser = $idUser
LIMIT 1
_SQL_;

		$row = JWDB::GetQueryResult( $sql, false );

		return $row;
	}

	/**
	 * Get Conference Setting By Number
	 */
	static public function GetDbRowFromNumber($number){
		if( null===$number )
			return array();

		$sql = <<<_SQL_
SELECT 
	*
FROM
	Conference
WHERE
	`number` = '$number'
LIMIT 1
_SQL_;

		$row = JWDB::GetQueryResult( $sql, false );

		return $row;
	}
	
	/**
	 * Create User Conference Setting
	 */
	static public function Create( $idUser, $options=array() ){

		$filter = isset($options['filter']) ? $options['filter'] : 'N';
		$notify = isset($options['notify']) ? $options['notify'] : 'Y';
		$number = isset($options['number']) ? $options['number'] : null;
		$deviceAllow = isset($options['deviceAllow']) ? $options['deviceAllow'] : 'sms,im,web';
		$friendOnly = isset($options['friendOnly']) ? $options['friendOnly'] : 'N';
		$timeCreate = isset($options['timeCreate']) ? $options['timeCreate'] : date('Y-m-d H:i:s');

		return JWDB::SaveTableRow('Conference', array(
					'idUser' =>  $idUser,
					'friendOnly' => $friendOnly,
					'deviceAllow' => $deviceAllow,
					'number' => $number,
					'filter' => $filter,
					'notify' => $notify,
					'timeCreate' => $timeCreate,
					));
	}

	/**
	 * Update User Conference Setting
	 */
	static public function Update( $idConference, $friendOnly='Y', $deviceAllow='sms,im,web', $number=null){
		$idConference = JWDB::CheckInt( $idConference );
		return JWDB::UpdateTableRow( 'Conference' , $idConference, array(
						'friendOnly' => $friendOnly,
						'deviceAllow' => $deviceAllow,
						'number' => $number,
					));
	}

	/**
	 * Update Row
	 */
	static public function UpdateRow( $idConference, $updatedRow = array() ){
		$idConference = JWDB::CheckInt( $idConference );
		return JWDB::UpdateTableRow( 'Conference' , $idConference, $updatedRow );
	}

	/**
	 * Get Conference All 
	 */
	static public function GetDbRowAll()
	{
		$sql = 'SELECT * FROM Conference';
		$row = JWDB::GetQueryResult( $sql, true );

		return $row;
	}

	/**
	 * Get Conference Enable All 
	 */
	static public function GetDbRowEnableAll()
	{   
		$rows_all = JWConference::GetDbRowAll();
		$rows = array();
		if (false == empty($rows_all))
		{   
			foreach( $rows_all as $row )
			{   
				$user_info = JWUser::GetUserInfo( $row['idUser'] );
				if ( false == empty($user_info) && $row['id']==$user_info['idConference'] )
				{   
					$rows[$row['id']]= array(
						'id' => $row['id'],
						'idUser' => $row['idUser'],
						'number' => $row['number'],
						'deviceAllow' => $row['deviceAllow'],
						'friendOnly' => $row['friendOnly'],
						'nameScreen' => $user_info['nameScreen'],
						'nameUrl' => $user_info['nameUrl'],
						'nameFull' => $user_info['nameFull'],
					);  
				}   
			}   
		}   

		return $rows;
	} 

	static public function IsAllowJoin($conference_id, $sender_id=1, $device='sms')
	{
		if ( null == $conference_id )
			return false;

		$conference_id = JWDB::CheckInt($conference_id);
		$conference = self::GetDbRowById( $conference_id );

		if ( $sender_id == $conference['idUser'] )
			return true;

		if ( empty($conference) || null==$conference['idUser'] )
			return false;

		$device_category = JWDevice::GetDeviceCategory( $device );

		$device_allowed = $conference['deviceAllow'];
	
		if( in_array( $device_category, explode(',', $device_allowed ) ) )
		{
			if( $conference['friendOnly'] == 'N' 
				|| JWFollower::IsFollower($sender_id, $conference['idUser']) ) 
			{
					return true;
			}
		}

		return false;
	}

}
?>
