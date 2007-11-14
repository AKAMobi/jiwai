<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Robot Lingo Class
 */
class JWRobotLingo {
	/**
	 * Instance of this singleton
	 *
	 * @var JWRobotLingo
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWRobotLingo
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
	 *
	 */
	static function	Lingo_Help($robotMsg)
	{
		$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_HELP_SUC' );
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}


	/*
	 *
	 */
	static function	Lingo_Tips($robotMsg)
	{
		$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_TIPS_SUC' );
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}


	/*
	 *
	 */
	static function	Lingo_On($robotMsg)
	{
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	
		$body 		= $robotMsg->GetBody();	

		$device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		
		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) ) 
			return JWRobotLogic::CreateAccount($robotMsg);

		$user_id	= $device_db_row['idUser'];
		$device_id	= $device_db_row['idDevice'];

		
		$ret = JWUser::SetSendViaDevice($user_id, $type);
			
		if ( false == $ret )
			JWLog::Log(LOG_ERR, "JWRobotLingo::Lingo_On JWUser::SetSendViaDevice($user_id,$type ...) failed");

		if( $device_id ) 
			$ret = JWDevice::SetDeviceEnabledFor($device_id, 'everything');

		if ( false == $ret )
			JWLog::Log(LOG_ERR, "JWRobotLingo::Lingo_On JWDevice::SetDeviceEnabledFor($device_id,...) failed");
		
		// 如果是Notice
		if ( preg_match('/^\w+\s+(\S+)\s*$/i', $body, $matches) ) {
			return self::Lingo_Notice( $robotMsg, true );
		}

		if( $ret ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_ON_SUC' );
		}else{
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_ON_ERR' );
		}

		return JWRobotLogic::ReplyMsg($robotMsg, $reply);

	}

	/*
	 *
	 */
	static function	Lingo_Off($robotMsg)
	{
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	
		$body 		= $robotMsg->GetBody();	

		$device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);


		$user_id = $device_db_row['idUser'];
		$device_for_user = JWDevice::GetDeviceRowByUserId($user_id);

		if( $type != 'web' )
			$ret = JWUser::SetSendViaDevice($user_id, 'web');
			
		if ( false == $ret )
			JWLog::Log(LOG_ERR, "JWRobotLingo::Lingo_Off JWUser::SetSendViaDevice($user_id,'web'...) failed");

		if ( preg_match('/^\w+\s+(\S+)\s*$/i',$body,$matches) ) {
			return self::Lingo_Notice( $robotMsg, false );
		}

		if ( false == $ret ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_OFF_SUC' );
		}else{
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_OFF_ERR' );
		}
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}


	/*
	 *
	 */
	static function	Lingo_Notice($robotMsg, $on=true)
	{
		$address 	= $robotMsg->GetAddress();	
		$serverAddress  = $robotMsg->GetServerAddress();
		$type 		= $robotMsg->GetType();	
		$body = $robotMsg->GetBody();

		$device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row['idUser']) )
			return JWRobotLogic::CreateAccount($robotMsg);

		$address_user_id = $device_db_row['idUser'];
		$address_user_row = JWUser::GetUserInfo($address_user_id);

		/*
	 	 *	解析命令参数
	 	 */
		$body = JWRobotLingoBase::ConvertCorner( $body );

		$param_array = preg_split('/\s+/', $body );
		$cmd = array_shift( $param_array );
		$param_array = array_unique( $param_array );

		if( count( $param_array ) == 0 ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_ON_HELP' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}
	
		$count_followe = count( $param_array );
		$follower_name = array();
		$followe = null;

		foreach( $param_array as $followe ) {

			if( $followe == 28006 ) {
				$followe = 'qzgwclub';
			}

			$userInfoFollower= JWUser::GetUserInfo( $followe );
			if ( empty($userInfoFollower) ) {
				continue;
			}

			if( JWFollower::IsFollower( $userInfoFollower['idUser'], $address_user_id ) ){
				if( $on ) {
					JWFollower::SetNotification( $userInfoFollower['idUser'], $address_user_id, 'Y' );
				}else{
					JWFollower::SetNotification( $userInfoFollower['idUser'], $address_user_id, 'N' );
				}
			}else{
				if( $on ) {
					JWSns::CreateFollower( $userInfoFollower['idUser'], $address_user_id, 'Y' );
					$outMessage = JWRobotLingoReply::GetReplyString( $robotMsg, 'OUT_FOLLOW', array(
						$address_user_row['nameScreen'],
						urlEncode($address_user_row['nameUrl']),		
					));
					JWNudge::NudgeToUsers( $userInfoFollower['idUser'], $outMessage, 'nudge', $type);
				}
			}
				
			array_push( $follower_name, $userInfoFollower['nameScreen'] );
		}

		if( empty( $follower_name ) ){
			$fnames = implode('、', $param_array );
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_NOUSER', array( 
				$fnames,
			));
		}else{
			$fnames = implode('、', $follower_name );
			if( $count_followe == 1 ){
				$replyConstant = $on ? 'REPLY_ON_SUC_USER' : 'REPLY_OFF_SUC_USER';
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, $replyConstant, array(
					$fnames,
				));
			}else{
				$replyConstant = $on ? 'REPLY_ON_SUC_MUL' : 'REPLY_OFF_SUC_MUL';
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, $replyConstant, array(
					$fnames,
				));
			}
		}

		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}


	/*
	 *
	 */
	static function	Lingo_Leave($robotMsg)
	{
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	

		$device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row['idUser']) )
			return JWRobotLogic::CreateAccount($robotMsg);


		$address_user_id = $device_db_row['idUser'];

		/*
	 	 *	解析命令参数
	 	 */
		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		$param_array = preg_split('/\s+/', $body );
		$cmd = array_shift( $param_array );
		$param_array = array_unique( $param_array );

		if( count( $param_array ) == 0 ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_LEAVE_HELP' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}
	
		$count_followe = count( $param_array );
		$follower_name = array();
		foreach( $param_array as $followe ) {

			$userInfoFollower= JWUser::GetUserInfo( $followe );
			if ( empty($userInfoFollower) ) {
				continue;
			}
			JWSns::DestroyFollowers($userInfoFollower['idUser'], array( $address_user_id ) );
			array_push( $follower_name, $userInfoFollower['nameScreen'] );
		}

		if( empty( $follower_name ) ){
			$fnames = implode('、', $param_array );
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_NOUSER', 
						array( 
							$fnames,
						));
		}else{

			$fnames = implode('、', $follower_name );
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_LEAVE_SUC', 
					array(
						$fnames,
					));
		}

		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}

	static function Lingo_Add($robotMsg) {

		$type = $robotMsg->GetType();
		$address = $robotMsg->GetAddress();

		$device_db_row 	= JWDevice::GetDeviceDbRowByAddress( $address, $type );

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);

		$address_user_id = $device_db_row['idUser'];
		$address_user_row = JWUser::GetUserDbRowById($address_user_id);

		/*
	 	 *	解析命令参数
	 	 */
		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		if ( ! preg_match('/^\w+\s+(\S+)\s*$/i',$body,$matches) ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_ADD_HELP');
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$user_input_invitee_address 	= $matches[1];

		/*
		 * 用户输入的邀请地址，是否包含类型信息？is full address? 
		 * (msn://)zixia.net (sms://)13911833788 or 13911833788 (qq://)918999
		 */
		if ( preg_match('#^([^/]+)://(.+)$#', $user_input_invitee_address, $matches) ) 
		{
			$invitee_type = $matches[1];
			$invitee_address = $matches[2];
		} else {
			$invitee_address = $user_input_invitee_address;

			if ( preg_match('/@/',$invitee_address) ) 
			{
				$invitee_type = $robotMsg->GetType();
			} 
			else if ( preg_match('/^[\d\+]?\d+$/', $invitee_address) ) 
			{
				if ( JWDevice::IsValid($invitee_address, 'sms') ) 
				{
					$invitee_type	= 'sms';
				}
				else
				{
					$invitee_address= preg_replace('/\+/','',$invitee_address);
					$invitee_type	= 'qq';
				}
			} 	
			else 
			{
				$invitee_type	= 'nameScreen';
			}
		}

		/*
		 *	检查 
		 *	1、不存在的用户名，并处理好友添加操作
		 *	2、错误的地址和
		 */
		if ( 'nameScreen'==$invitee_type )
		{
			return self::Lingo_Follow($robotMsg);
		}


		if ( false == JWDevice::IsValid($invitee_address,$invitee_type) )
		{
	
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_ADD_NOADDRESS', array( $user_input_invitee_address, ));
			return JWRobotLogic::ReplyMsg($robotMsg, $reply );
		}

		switch ( $invitee_type )
		{
			case 'sms':
				$invitee_address	= preg_replace("/^\+86/","",$invitee_address);
				break;

			default:
				// 没有动作
		}


		/*
		 *	查看被添加的地址是否已经存在
		 */
		$invitee_device_id = JWDevice::GetDeviceIdByAddress(array('address'=>$invitee_address,'type'=>$invitee_type) );
		$invitee_device_db_row = JWDevice::GetDeviceDbRowById($invitee_device_id);

		if ( false == empty($invitee_device_db_row) )
		{
			$userInfo = JWUser::GetUserInfo( $invitee_device_db_row['idUser'] );
			$robotMsg->setBody( "Follow $userInfo[nameScreen]");
			return self::Lingo_Follow( $robotMsg );
		}
		else
		{
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_ADD_REQUEST_INVITE' );

			/*
			 *	没有注册用户，发送邀请
			 *	使用 msg 数组，区分 email / im 的消息
			 */
			$invite_msg['email'] = JWRobotLingoReply::GetReplyString( $robotMsg, 'OUT_ADD_EMAIL', array( $address_user_row['nameFull'], $address_user_row['nameScreen'], ) );
			$invite_msg['im'] = JWRobotLingoReply::GetReplyString( $robotMsg, 'OUT_ADD_IM', array( $address_user_row['nameFull'], $address_user_row['nameScreen'], ) );
			$invite_msg['sms'] = JWRobotLingoReply::GetReplyString( $robotMsg, 'OUT_ADD_SMS', array( $address_user_row['nameFull'], $address_user_row['nameScreen'], ) );

			/**
			 * NotifyQueue When invite
			 */

			$msg = ( $invitee_type == 'sms' ) ? $invite_msg['sms'] : ( ($invitee_type == 'email') ? $invite_msg['email'] :  $invite_msg['im'] );

			$metaInfo = array( 
				'message' => $msg, 
				'address' => $invitee_address,
				'type' => $invitee_type,
			);
			JWNotifyQueue::Create( $address_user_id, null, JWNotifyQueue::T_INVITE, $metaInfo );
		}

		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}

	/*
	 * Follow 用户
	 */
	static function	Lingo_Follow($robotMsg)
	{
		$type = $robotMsg->GetType();
		$address = $robotMsg->GetAddress();


		$device_db_row 	= JWDevice::GetDeviceDbRowByAddress( $address, $type );

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);

		$address_user_id = $device_db_row['idUser'];
		$address_user_row = JWUser::GetUserDbRowById($address_user_id);

		/** Parse Param  **/
		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		/** parameter not enough **/
		if ( ! preg_match('/^\w+\s+(\S+)\s*$/i',$body,$matches) ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_FOLLOW_HELP');
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$invitee_address = $matches[1];
		$follower = JWUser::GetUserInfo( $matches[1] );

		if( empty( $follower ) ) 
		{
			if ( preg_match( '#^([^/]+)://(.+)$#', $invitee_address, $matches )
					|| preg_match('/@/',$invitee_address)
					|| preg_match('/^[\d\+]?\d+$/', $invitee_address) )
			{
				return self::Lingo_Add( $robotMsg );
			}else
			{
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_FOLLOW_NOUSER', array( 
					$invitee_address,
				));
				return JWRobotLogic::ReplyMsg($robotMsg, $reply);
			}
		}

		$friend_user_id = $follower['idUser'];

		if ( JWFollower::IsFollower( $friend_user_id, $address_user_id ) )
		{
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_FOLLOW_EXISTS', array( 
				$follower['nameScreen'],
			));
		} else
	       	{
			JWSns::CreateFollower( $friend_user_id, $address_user_id );
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_FOLLOW_SUC', array(
				$follower['nameScreen'],
			));

			$outMessage = JWRobotLingoReply::GetReplyString( $robotMsg, 'OUT_FOLLOW', array(
				$address_user_row['nameScreen'], urlEncode($address_user_row['nameUrl']),		
			));
			JWNudge::NudgeToUsers($follower['id'], $outMessage, 'nudge', $type);
		}

		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}


	/*
	 *
	 */
	static function	Lingo_Delete($robotMsg)
	{
		/*
		 *	获取发送者的 idUser
		 */
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	


		$device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);

		$address_user_id = $device_db_row['idUser'];


		$address_user_row = JWUser::GetUserDbRowById($address_user_id);


		/*
	 	 *	解析命令参数
	 	 */
		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		if ( ! preg_match('/^\w+\s+(\S+)\s*$/i',$body,$matches) ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_DELETE_HELP' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$friend_name = $matches[1];

		/*
		 *	获取被删除者的用户信息
		 */
		$friend_user_row = JWUser::GetUserInfo( $friend_name );

		if ( empty($friend_user_row) ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_DELETE_NOUSER', array( $friend_name,));
			return JWRobotLogic::ReplyMsg($robotMsg, $reply );
		}
		
		$bio = $address_user_row['protected'] == 'Y' || $friend_user_row['protected'] == 'Y';

		JWSns::DestroyFriends($address_user_id, array($friend_user_row['idUser']), $bio );
		//JWSns::DestroyFollowers ($friend_user_row['idUser'], array($address_user_id), $bid );

		$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_DELETE_SUC', array( $friend_user_row['nameScreen'],) );
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}


	/*
	 *
	 */
	static function	Lingo_Get($robotMsg)
	{
		/*
	 	 *	解析命令参数
	 	 */
		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		$address = $robotMsg->GetAddress();	
		$type = $robotMsg->GetType();	


		$device_db_row 	= JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);

		$address_user_id = $device_db_row['idUser'];
		$address_user_row = JWUser::GetUserDbRowById($address_user_id);

		if ( ! preg_match('/^\w+\s+(\S+)\s*$/i',$body,$matches) ){
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_GET_HELP' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$friend_name = $matches[1];

		/*
		 *	获取被订阅者的用户信息
		 */
		$friend_user_db_row = JWUser::GetUserInfo( $friend_name );

		if ( empty($friend_user_db_row) ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_NOUSER', array($friend_name,) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		/*
		 * 检查好友关系
		 */
		if( $friend_user_db_row['protected'] == 'Y' 
				&& false == JWFriend::IsFriend( $friend_user_db_row['idUser'], $address_user_id )
		  ){
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_GET_NOPERM', array(
				$friend_user_db_row['nameScreen'],
			));
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		if( $friend_user_db_row['idConference'] ) {
			$status_ids = JWStatus::GetStatusIdsFromConferenceUser($friend_user_db_row['idUser'], 1);
		}else{
			$status_ids = JWStatus::GetStatusIdsFromUser($friend_user_db_row['idUser'], 1);
		}

		$sender = $friend_user_db_row['nameScreen'];

		if ( empty($status_ids['status_ids']) )
		{
			$status = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_GET_NOSTATUS' );
		}
		else
		{
			$status_id = $status_ids['status_ids'][0];

			$status_rows = JWStatus::GetStatusDbRowsByIds ( array($status_id) );
			$status_row = $status_rows[$status_id];
			$status	= $status_row['status'];

			if( $status_row['idUser'] != $friend_user_db_row['idUser'] ) {
				$senderUser = JWUser::GetUserInfo( $status_row['idUser'] );
				$sender = $sender.'['.$senderUser['nameScreen'].']';
			}
		}
		

		$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_GET_SUC', array($sender, $status, ) );
		return JWRobotLogic::ReplyMsg($robotMsg, $reply );
	}


	/*
	 *
	 */
	static function	Lingo_Nudge($robotMsg)
	{
		/*
		 *	获取发送者的 idUser
		 */
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	

		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);


		$address_user_id = $device_db_row['idUser'];

		/*
	 	 *	解析命令参数
	 	 */
		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		if ( ! preg_match('/^\w+\s+(\S+)\s*$/i',$body,$matches) ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_NUDGE_HELP' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$address_user_db_row = JWUser::GetUserDbRowById($address_user_id);
		$friend_name = $matches[1];

		if( strtolower( trim($friend_name) ) == 'all' ) {
			$friendIds = JWFollower::GetBioFollowingIds( $device_db_row['idUser'] );
			$nudge_message = JWRobotLingoReply::GetReplyString( $robotMsg, 'OUT_NUDGE', array(
				JWNotify::GetPrettySender($address_user_db_row),
			));
			foreach( $friendIds as $idFriend ) {
				JWNudge::NudgeToUsers($idFriend, $nudge_message, 'nudge', $type);
			}

			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_NUDGE_SUC', array(
				'和你紧密联系的人',
			));
			return JWRobotLogic::ReplyMsg($robotMsg, $reply );
		}

		$friend_user_db_row = JWUser::GetUserInfo($friend_name);

		if ( empty($friend_user_db_row) ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_NOUSER', array($friend_name,) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply );
		}

		$friend_user_id		= $friend_user_db_row['idUser'];
		$send_via_device	= JWUser::GetSendViaDeviceByUserId($friend_user_id);

		// TODO 要考虑判断用户的 device 是否已经通过验证激活
		if ( 'web'==$send_via_device ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_NUDGE_DENY', array($friend_user_db_row['nameScreen'],) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply );
		}

		if ( JWBlock::IsBlocked($address_user_id, $friend_user_db_row['idUser'], false ) ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_NUDGE_NOPERM', array($friend_user_db_row['nameScreen'],) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}


		if( $device_db_row['idUser'] == $friend_user_db_row['id'] ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_NUDGE_SELF' );
		}else{
			$nudge_message = JWRobotLingoReply::GetReplyString( $robotMsg, 'OUT_NUDGE', array(
				JWNotify::GetPrettySender($address_user_db_row),
			));
			JWNudge::NudgeToUsers( array($friend_user_db_row['idUser']), $nudge_message, 'nudge', $type );
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_NUDGE_SUC', array(
						$friend_user_db_row['nameScreen'],
			));
		}
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}



	/*
	 *
	 */
	static function	Lingo_Whois($robotMsg)
	{
		/*
	 	 *	解析命令参数
	 	 */
		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		if ( ! preg_match('/^\w+\s+(\S+)\s*$/i',$body,$matches) ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_WHOIS_HELP' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$friend_name 		= $matches[1];
		$friend_user_row	= JWUser::GetUserInfo($friend_name);

		if ( empty($friend_user_row['idUser']) ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_NOUSER', array($friend_name,) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply );
		}


		$register_date	= date("Y年n月",strtotime($friend_user_row['timeCreate']));
	
		$reply= "姓名：$friend_user_row[nameFull]，注册时间：$register_date";

		if ( !empty($friend_user_row['bio']) )
			$reply .= "，自述：$friend_user_row[bio]";

		if ( $location = JWLocation::GetLocationName($friend_user_row['location']) )
			$reply .= "，位置：$location";

		if ( !empty($friend_user_row['url']) )
			$reply .= "，网站：$friend_user_row[url]";

		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}


	/*
	 *
	 */
	static function	Lingo_Accept($robotMsg)
	{
		/*
	 	 *	解析命令参数
	 	 */
		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		if ( ! preg_match('/^\w+\s+(\S+)\s*$/i',$body,$matches) ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_ACCEPT_HELP');
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$inviter_name 		= $matches[1];
		$inviter_user_row 	= JWUser::GetUserInfo( $inviter_name );
		/*
		 *	检查发送者是否已经注册 
		 */
		$address 	= $robotMsg->GetAddress();	
		$type 		= $robotMsg->GetType();	

		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		/*
		 *	分为三种情况处理：
				1、用户已经注册
				2、用户没有注册，但是有邀请
				3、用户没有注册，没有邀请
		 */
		if ( ! empty($device_db_row) && !empty($inviter_user_row) )
		{
			/*
			 *	 1、用户已经注册
			 */
			$address_user_id = $device_db_row['idUser'];

			if( JWFriendRequest::IsExist($inviter_user_row['id'], $address_user_id) ) {
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_ACCEPT_SUC_REQUEST', array($inviter_user_row['nameScreen'],) );
			}else{
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_ACCEPT_SUC_NOREQUEST', array($inviter_user_row['nameScreen'],) );
			}
			if( false == JWSns::CreateFriends($inviter_user_row['id'], array($address_user_id), false) ){
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_ACCEPT_500', array($inviter_user_row['nameScreen'],) );
			}
		}
		else if ( !empty($inviter_user_row) )
		{
			/*
			 *	2、 被邀请用户没有完成注册 
			 * 		这时用户回复的字符串，只要不是命令，即会被系统当作用户选择的用户名。
			 *		回复提示信息
			 */

			$invitation_id	= JWInvitation::GetInvitationIdFromAddress( array('address'=>$address,'type'=>$type) ); 


			if ( empty($invitation_id) ) {
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_ACCEPT_INVITE', array($inviter_name,));
			}else{
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_ACCEPT_INVITE_SUC', array($inviter_name,));
			}
		}
		else
		{
			/*
				3、无效邀请 *		邀请者(Accept的用户)不存在？
			 */
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_ACCEPT_INVITE_NOUSER', array($inviter_name,));
		}
		return JWRobotLogic::ReplyMsg($robotMsg, $reply );
	}


	/*
	 *
	 */
	static function	Lingo_Deny($robotMsg)
	{
		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		if ( ! preg_match('/^\w+\s+(\S+)\s*$/i',$body,$matches) ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_DENY_HELP' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}


		$friend_name 	= $matches[1];

		$address 	= $robotMsg->GetAddress();
		$type 		= $robotMsg->GetType() ;
		$invitation_id	= JWInvitation::GetInvitationIdFromAddress( array(
							'address' => $address,
							'type'	  => $type,
							)); 

		if ( empty($invitation_id) )
		{
			/*
			 * 没有邀请过这个设备
			 * 检查是否设备已经注册过，如果没有注册过则引导注册
			 */
			if ( JWDevice::IsExist($address,$type) ) {
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_DENY_NOINVITE', array($friend_name,) );
				return JWRobotLogic::ReplyMsg($robotMsg, $reply );
			} else {
				return JWRobotLogic::CreateAccount($robotMsg);
			}
		}

		/*
		 *	删除邀请记录
		 *	FIXME: 如果一个 address 被多人邀请多次，这里可能删除的是别人的邀请……
					这样需要多 deny 几次，就全部删除了……
		 */
		JWInvitation::Destroy($invitation_id);

		$friend_db_row = JWUser::GetUserInfo($friend_name);

		if ( empty($friend_db_row) )
		{
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_DENY_NOUSER', array($friend_name,) );
		}
		else
		{
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_DENY_SUC', array( $friend_db_row['nameScreen'], ) );
		}

		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}


	/*
	 *
	 */
	static function	Lingo_D($robotMsg)
	{
		$address 	= $robotMsg->GetAddress();
		$type 		= $robotMsg->GetType() ;

		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) )
			return JWRobotLogic::CreateAccount($robotMsg);

		$address_user_id = $device_db_row['idUser'];

		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		if ( ! preg_match('/^\w+\s+(\S+)\s+(.+)$/i',$body,$matches) ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_D_HELP');
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$friend_name 	= $matches[1];
		$message_text	= $matches[2];

		$friend_row	= JWUser::GetUserInfo($friend_name);

		if ( empty($friend_row) )
		{
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_NOUSER', array($friend_name,) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$friend_id = $friend_row['idUser'];

		/*
		if ( !JWFriend::IsFriend($friend_id, $address_user_id) )
		{
			if ( JWFriend::IsFriend($address_user_id, $friend_id) )
			{
				$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_D_NOPERM', array($friend_name,) );
				return JWRobotLogic::ReplyMsg($robotMsg, $reply );
			}
			else
			{
				$reply = JWRobotLingoReply::GetReplyString($robotMsg,'REPLY_D_NOPERM_BIO',array($friend_name,));
				return JWRobotLogic::ReplyMsg($robotMsg, $reply);
			}
		}
		*/	

		if ( JWSns::CreateMessage($address_user_id, $friend_id, $message_text, $type) ) {
			if( false == in_array( $type, array('sms', 'api') ) ) {
				$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_D_SUC', array($friend_name,));
				return JWRobotLogic::ReplyMsg($robotMsg, $reply);
			}
		}
		return null;
	}

	/*
	 * Reg nameScreen nameFull
	 */
	static function Lingo_Reg($robotMsg){

		$address 	= $robotMsg->GetAddress();
		$type 		= $robotMsg->GetType();	
		$body 		= $robotMsg->GetBody();	
		
		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);
		if( false  == empty( $device_db_row ) )
			$user_info = JWUser::GetUserInfo( $device_db_row['idUser'] );

		$registered = true;
		if( empty( $device_db_row ) || empty($user_info) ){
			$registered = false;
		}

		$body = JWRobotLingoBase::ConvertCorner( $body );

		if ( preg_match('/^([[:alpha:]]+)\s+([\S]+)\s*([\S]*)$/',$body, $matches) ) {

			$nameScreen = $matches[2];
			
			if( false == JWUser::IsValidName( $nameScreen ) ){
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_INVALID_NAME', array( $nameScreen, ) );
				return JWRobotLogic::ReplyMsg($robotMsg, $reply );

			}

			$nameFull = isset( $matches[3] ) ? $matches[3] : null;

			if( false == isset( $matches[3] ) ){
				if ( $registered == false )
					$nameFull = $nameScreen;
			}

			if( $registered == false ) {
				return JWRobotLogic::CreateAccount( $robotMsg, true, $nameScreen, $nameFull );
			}

			//only change nameFull
			if( $user_info['nameScreen'] == $nameScreen ) {
				if( $nameFull == null ) {
					$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_SAME', array($nameScreen, ) );
					return JWRobotLogic::ReplyMsg($robotMsg, $reply );
			       	}else{
					$uRow = array( 'nameFull' => $nameFull );
					if( JWUser::Modify( $user_info['id'], $uRow) ){
						$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_SUC_NICK', array($nameFull, ) );
						return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
					}else{
						$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_500' );
						return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
					}
				}
			}


			$email = in_array( $type, array('jabber','msn','gtalk','email') ) ? $address : null;
			$user_name = JWUser::GetPossibleName( $nameScreen, $email, $type );

			//if no nameFull , use user_name;
			if( $nameFull == null )
				$nameFull = $user_name;
			//end if

			if( empty($user_name) ) {
				if( $registered ) {
					$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_GM_HOT', array($user_name,));
				} else {
					$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_HOT', array($user_name,));
				}
				return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
			}else{

				$uRow = array('nameScreen' => $user_name );
				if ( null != $nameFull ) 
					$uRow['nameFull']  = $nameFull;

				if( JWUser::Modify( $user_info['id'], $uRow ) ){
					if( $nameFull == null ) {
						$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_SUC_NICK', array($user_name, ) );
						return JWRobotLogic::ReplyMsg( $robotMsg, $reply);
					}else{
						$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_SUC_ALL', array($nameFull, $user_name, ) );
						return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
					}
				}else{
					$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_500' );
					return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
				}
			}


		}else{

			if( $registered ) {
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_HELP_GM' );
			}else{
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_REG_HELP' );
			}
			return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
		}
	}


	/*
	 *
	 */
	static function	Lingo_Whoami($robotMsg)
	{
		$address 	= $robotMsg->GetAddress();
		$type 		= $robotMsg->GetType();
		$serverAddress = $robotMsg->GetServerAddress();

		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) ) {
			return JWRobotLogic::CreateAccount($robotMsg);
		}

		$address_user_id = $device_db_row['idUser'];

		if ( empty($address_user_id) )
		{
			// 可能 device 还在，但是用户没了。
			// 删除 device.
			JWDevice::Destroy($device_db_row['idDevice']);
			return JWRobotLogic::CreateAccount($robotMsg);
		}

		$address_user_row = JWUser::GetUserInfo($address_user_id);
		$is_web_user = JWUser::IsWebUser($address_user_row['idUser']);
	
		if ( $is_web_user )
		{
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_WHOAMI_WEB', array( $address_user_row['nameScreen'], ) );
		}
		else
		{
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_WHOAMI_IM', array( $address_user_row['nameScreen'], ) );
		}

		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}

	/**
  	 * 0000 | 00000
	 */	 
	static function Lingo_0000($robotMsg) 
	{
		$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_0000_HELP' );
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}

	/**
	 * Track
	 */
	static function Lingo_Track($robotMsg){
		$address = $robotMsg->GetAddress();
		$type = $robotMsg->GetType();
		$serverAddress = $robotMsg->GetServerAddress();
		$body = $robotMsg->GetBody();
		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) ) {
			return JWRobotLogic::CreateAccount($robotMsg);
		}
		
		$param_array = preg_split( '/\s+/', $body, 2);
		if( count( $param_array) == 1 ) {
			$wordList = JWTrackUser::GetWordListByIdUser( $device_db_row['idUser'] );
			if( null == $wordList ) {
				$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_TRACK_HELP' );
			}else{
				$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_TRACK_SHOW', array($wordList));
			}

			return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
			// show user,track array
		}

		$sourceWord = $param_array[1];
		$words = preg_split( '/,|，/', $sourceWord );
		foreach( $words as $word ) {
			JWTrackUser::Create( $device_db_row['idUser'], $word );
		}

		$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_TRACK_SUC', array( $sourceWord ) );
		return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
	}

	/**
	 * UnTrack
	 */
	static function Lingo_UnTrack($robotMsg){
		$address 	= $robotMsg->GetAddress();
		$type 		= $robotMsg->GetType();
		$serverAddress = $robotMsg->GetServerAddress();
		$body = $robotMsg->GetBody();
		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		if ( empty($device_db_row) ) {
			return JWRobotLogic::CreateAccount($robotMsg);
		}

		$param_array = preg_split( '/\s+/', $body, 2);
		if( count( $param_array) == 1 ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_UNTRACK_HELP' );
			return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
		}

		$sourceWord = $param_array[1];
		$words = preg_split( '/,|，/', $sourceWord );
		foreach( $words as $word ) {
			JWTrackUser::Destroy( $device_db_row['idUser'], $word );
		}

		$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_UNTRACK_SUC', array( $sourceWord ) );
		return JWRobotLogic::ReplyMsg( $robotMsg, $reply );
	}
	
	/**
	 * Block Somebody
	 */
	static function	Lingo_Block($robotMsg)
	{
		$address 	= $robotMsg->GetAddress();
		$type 		= $robotMsg->GetType();
		$serverAddress = $robotMsg->GetServerAddress();
		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) ) {
			return JWRobotLogic::CreateAccount($robotMsg);
		}

		/**
		 * 参数
		 */
		$param_array = preg_split('/\s+/', $body );
		$cmd = array_shift( $param_array );
		$param_array = array_unique( $param_array );

		if( count( $param_array ) == 0 ) {
			$idUserBlocks = JWBlock::GetIdUserBlocksByIdUser( $device_db_row['idUser'] );
			if( empty( $idUserBlocks ) ) {
				$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_BLOCK_HELP' );
				return JWRobotLogic::ReplyMsg($robotMsg, $reply);
			}

			$users = JWUser::GetDbRowsByIds( $idUserBlocks, false, count($idUserBlocks) );
			$nameScreens = null;
			foreach( $users as $u ) {
				$nameScreens .= $u['nameScreen'].', ';
			}
			
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_BLOCK_LIST', array(trim($nameScreens, ', '),) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}
		
		$nameScreens = null;
		foreach( $param_array as $p ) {
			$u = JWUser::GetUserInfo( $p );
			if(false == empty( $u ) ){
				JWSns::Block( $device_db_row['idUser'], $u['id'] );
				$nameScreens .= $u['nameScreen'].', ';
			}
		}

		$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_BLOCK_SUC', array(trim($nameScreens, ', '),) );
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}

	static function	Lingo_UnBlock($robotMsg)
	{
		$address 	= $robotMsg->GetAddress();
		$type 		= $robotMsg->GetType();
		$serverAddress = $robotMsg->GetServerAddress();
		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) ) 
			return JWRobotLogic::CreateAccount($robotMsg);

		/**
		 * 参数
		 */
		$param_array = preg_split('/\s+/', $body );
		$cmd = array_shift( $param_array );
		$param_array = array_unique( $param_array );

		if( count( $param_array ) == 0 ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_UNBLOCK_HELP' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}
		
		$nameScreens = null;
		foreach( $param_array as $p ) {
			$u = JWUser::GetUserInfo( $p );
			if(false == empty( $u ) ){
				JWSns::UnBlock( $device_db_row['idUser'], $u['id'] );
				$nameScreens .= $u['nameScreen'].', ';
			}
		}

		$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_UNBLOCK_SUC', array(trim($nameScreens, ', '),) );
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}

	static function	Lingo_Pass($robotMsg)
	{
		$address 	= $robotMsg->GetAddress();
		$type 		= $robotMsg->GetType();
		$serverAddress = $robotMsg->GetServerAddress();
		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) 
			$device_db_row = self::CreateAccount($robotMsg);

		if ( empty($device_db_row) ) 
			return JWRobotLogic::CreateAccount($robotMsg);

		/**
		 * 参数
		 */
		$param_array = preg_split('/\s+/', $body, 2);
		$cmd = array_shift( $param_array );
		$param_array = array_unique( $param_array );

		if( count( $param_array ) == 0 ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_PASS_HELP' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$userInfo = JWUser::GetUserInfo( $device_db_row['idUser'] );
		$password = array_shift( $param_array );
		JWUser::ChangePassword( $device_db_row['idUser'], $password );

		$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_PASS_SUC', array(
					$userInfo['nameScreen'], 
					$password,
		));
		return JWRobotLogic::ReplyMsg($robotMsg, $reply);
	}

	static public function Lingo_Merge($robotMsg){
		$address 	= $robotMsg->GetAddress();
		$type 		= $robotMsg->GetType();
		$serverAddress = $robotMsg->GetServerAddress();
		$body = $robotMsg->GetBody();
		$body = JWRobotLingoBase::ConvertCorner( $body );

		$device_db_row = JWDevice::GetDeviceDbRowByAddress($address,$type);

		/** Create Account For IM/SMS User **/
		if ( empty($device_db_row) ) {
			$device_db_row = self::CreateAccount($robotMsg);
			return null;
		}

		if ( empty($device_db_row) ) 
			return JWRobotLogic::CreateAccount($robotMsg);

		if( false == in_array( $type, array('qq','msn','gtalk','skype','yahoo','sms') ) ){
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_MSG_WEBREQ' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		/**
		 * 参数
		 */
		$param_array = preg_split('/\s+/', $body, 3);

		if( count( $param_array ) < 3 ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_MERGE_TIPS', array(
				array_shift($param_array),
			));
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		$cmd = array_shift( $param_array );
		$nameScreen = @array_shift( $param_array );
		$password = @array_shift( $param_array );

		$userInfo = JWUser::GetUserInfo( $device_db_row['idUser'] );
		$mergeToUserInfo = JWUser::GetUserInfo( $nameScreen );

		if( $userInfo['isWebUser'] == 'Y' ) {
			$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_MERGE_WEBUSER' );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		if( false==empty($mergeToUserInfo) && $userInfo['id'] == $mergeToUserInfo['id'] ) {
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_MERGE_OWN', array($nameScreen) );
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}

		if( false==empty($mergeToUserInfo)
				&& false==empty($password) 
				&& JWUser::VerifyPassword( $mergeToUserInfo['id'], $password ) ) {
			//Suc
			$dDeviceRows = JWDevice::GetDeviceRowByUserId( $userInfo['id'] );
			if( count( $dDeviceRows ) > 1 ) {
				$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_MERGE_MULTI');
				return JWRobotLogic::ReplyMsg($robotMsg, $reply);
			}

			$mDeviceRows = JWDevice::GetDeviceRowByUserId( $mergeToUserInfo['id'] );
			if( isset($mDeviceRows[$type]) ){
				if( empty($mDeviceRows[$type]['secret']) ){
					$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_MERGE_HAVE', array(
						$nameScreen, $type, $mDeviceRows[$type]['address'],
					));
					return JWRobotLogic::ReplyMsg($robotMsg, $reply);
				}else{
					JWDevice::Destroy( $mDeviceRows[$type]['id'] );
				}
			}

			//merge device;
			$upArray = array( 'idUser' => $mergeToUserInfo['id'] );
			JWDB::UpdateTableRow( 'Device', $device_db_row['id'], $upArray );
			
			//merge status;
			$sql = "UPDATE Status SET idUser=$mergeToUserInfo[id] WHERE idUser=$device_db_row[idUser]";
			JWDB::Execute( $sql );

			//destroy user;
			JWUser::Destroy( $device_db_row['idUser'] );

			//reply
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_MERGE_SUC', array(
				$type, $address, $nameScreen,
			));
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);

		}else{
			$reply = JWRobotLingoReply::GetReplyString($robotMsg, 'REPLY_MERGE_ERR', array(
				$nameScreen,
			));
			return JWRobotLogic::ReplyMsg($robotMsg, $reply);
		}
	}

	static public function CreateAccount($robotMsg) {
		
		$address = $robotMsg->GetAddress();
		$type = $robotMsg->GetType();

		$device_db_row = JWDevice::GetDeviceDbRowByAddress( $address, $type );
		if( false == empty( $device_db_row ) ) {
			return $device_db_row;
		}

		switch($type) {
			case 'qq':
				$nameScreen = 'QQ'.$address;
			break;
			case 'sms':
				$nameScreen = preg_replace_callback('/([0]?\d{3})([\d]{4})(\d+)/', create_function('$m','return "$m[1]XXXX$m[3]";'), $address);
			break;
			case 'skype':
			case 'yahoo':
				$nameScreen = $address;
			break;
			default:
				list($nameScreen) = split( '@', $address );
				$nameScreen = is_numeric($nameScreen) ? 'M'.$nameScreen : $nameScreen;
		}

		/* 如果 nameScreen 长度小于 5，则补齐 */
		if( strlen($nameScreen) < 5 ) {
			$plusLen = 5 - strlen( $nameScreen ) ;
			$nameScreen .= JWDevice::GenSecret( $plusLen );
		}

		$nameFull = $nameScreen;
		$nameScreen = JWUser::GetPossibleName( $nameScreen );

		$uArray = array(
			'nameScreen' => $nameScreen,
			'nameFull' => $nameFull,
			'pass' => JWDevice::GenSecret(16),
			'isWebUser' => 'N', 
			'noticeAutoNudge' => 'Y',   //Not nudge
			'ip' => JWRequest::GetIpRegister($type),
		);

		$idUser =  JWSns::CreateUser($uArray);
		if( $idUser ) {
			if( JWSns::CreateDevice($idUser, $address, $type, true, array(
					'isSignatureRecord' => 'Y', ) )
			){
				$reply = JWRobotLingoReply::GetReplyString( $robotMsg, 'REPLY_CREATE_USER_FIRST', array(
					$nameScreen,
				));
				$replyRobotMsg = JWRobotLogic::ReplyMsg( $robotMsg, $reply );
				JWRobot::SendMt( $replyRobotMsg );
				return JWDevice::GetDeviceDbRowByAddress( $address, $type );
			}
		}else{
			return array();
		}
	}
}
?>
