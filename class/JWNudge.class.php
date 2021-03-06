<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Nudge Class
 */
class JWNudge {
	/**
	 * Instance of this singleton
	 *
	 * @var JWNudge
	 */
	static private $msInstance;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWNudge
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

	static public function NudgeToUsers($idUsers, $message=null, $messageType='nudge', $source='bot', $options=array() ){
		if( empty( $idUsers ) )
			return true;

		settype( $idUsers, 'array' );
		$idUsers = array_unique( $idUsers );

		/* other important infomation retrieve */
		$idConference = isset( $options['idConference'] ) ? intval( $options['idConference'] ) : null;
		$idStatus = isset( $options['idStatus'] ) ? intval( $options['idStatus'] ) : null;

		$status = ( $idStatus == null ) ? null : JWStatus::GetDbRowById( $idStatus );
		$conference = ( $idConference == null ) ? null : JWConference::GetDbRowById( $idConference );
		$user = ( $conference == null ) ? null : JWUser::GetUserInfo( $conference['idUser'] );
		$user = ( $user == null ) ? ( $status == null ? null : JWUser::GetUserInfo($status['idUser']) ) : $user;
		$receiver_user = isset($options['receiver_id']) ? JWUser::GetUserInfo($options['receiver_id']) : null;

		$tracks = isset($options['track']) ? $options['track'] : null;

		$nudgeOptions = array(
			'conference' => $conference,
			'status' => $status,
			'user' => $user,
		);

		foreach( $idUsers as $idUser )
		{
			$userTo = JWUser::GetUserInfo( $idUser );
			if( empty( $userTo ) )
				continue;
			
			// sleep time constrains
			if ( JWUser::InTimeChip( $idUser, 'SLEEP' ) )
				continue;

			// allow reply type constrains
			if ( false==empty($receiver_user) )
			{
				if ( 'none' == $userTo['allowReplyType'] )
					continue;
				else if ( 'mine' == $userTo['allowReplyType'] )
				{
					if ( $idUser != $receiver_user['id'] )
						continue;
				}
				else if ( 'each' == $userTo['allowReplyType'] )
				{
					if ( $idUser != $receiver_user['id'] 
						&& false==JWFollower::IsFollower( $receiver_user['id'], $idUser ) )
						continue;
				}
				else if ( 'everyone' == $userTo['allowReplyType'] )
				{
					;
				}
			}
			// end allow reply

			$deviceRows= JWDevice::GetDeviceRowByUserId( $idUser );
			if( empty( $deviceRows ) )
				continue;

			$deviceSendVia = $userTo['deviceSendVia'];
			$availableSendVia = self::GetAvailableSendVia_Temp( $deviceRows, $deviceSendVia );

			if( null == $availableSendVia )
				continue;

			if( $messageType == 'direct_messages' ) 
			{
				$idMessage = $message['idMessage'];
				/**
				 * for display new message until read on web
				JWMessage::SetMessageStatus( $idMessage, JWMessage::INBOX, JWMessage::MESSAGE_HAVEREAD );
				*/
				$message = $message['message'];
			}

			$oneoptions = $nudgeOptions;
			if ( $tracks && isset($tracks[$idUser]) ) {
				$oneoptions['plus'] = " 叽歪词汇 http://JiWai.de/k/{$tracks[$idUser]}/";
			}

			$deviceRow = $deviceRows[ $availableSendVia ];
			JWNudge::NudgeToUserDevice( $deviceRow, $message, $messageType, $oneoptions);
		}
	}

	/**
	 *  send message to user with custom device
	 */
	static public function NudgeToUserDevice( $deviceRow, $message, $messageType, &$options=array() ) {
		
		switch( $deviceRow['enabledFor'] ){
			case 'direct_messages':
				if( 'direct_messages' != $messageType )
					break;
			case 'everything':
				// 检查设备是否已经验证通过
				$isVerified= $deviceRow['verified'];
				if ( false == $isVerified )
				{
					JWLog::Log(LOG_INFO, "JWNudge::Nudge skip unverfied device for idUser"
										. '[' . $deviceRow['idUser'] . ']'
										. ' of device [' . $deviceRow['type'] 
										. ':' .  $deviceRow['address']
					);
					break;
				}
				
				//fetch from nudge options
				$user = $options['user'];
				$conference = $options['conference'];
				$status = $options['status'];
				$statusType = ( $status == null ) ? 'NONE' : $status['statusType'];
				
				//fetch from deviceRow
				$type = $deviceRow['type'];
				$address = $deviceRow['address'];

				if( is_array( $message ) ){
					if( $type == 'sms' ){
						$message = $message[ 'sms' ];
					}else{
						$message = $message[ 'im' ];
					}
				}

				$serverAddress = null;
				if( $type=='sms' && $serverAddress==null && 'MMS'==$statusType ) {
					$serverAddress = JWFuncCode::GetMmsNotifyFunc($address, $status['id'] );
				}
				if( $type=='sms' && $serverAddress==null ) {
					$serverAddress = JWNotify::GetServerAddress( $address, $conference, $user );
				}

				//附加投票链接
				if( $type!='sms' && $statusType=='VOTE' ) {
					$message .= '这是'.$user['nameScreen'].'在叽歪发起的投票,可以在这里( http://jiwai.de/'.$user['nameUrl'].'/statuses/'.$status['id'].' )参与投票并查看投票结果';
				} else if ( $type!='sms' && isset($options['plus'])) {
					$message .= $options['plus'];
				}

				JWRobot::SendMtRawQueue($address, $type, $message, $serverAddress, null);
			break;
			case 'nothing':
			break;
		}
		return true;	
	}
	
	/**
	 * 选取在线的设备发送，如果选定msn，且不在线，那么不发送； 
	 */
	static public function GetAvailableSendVia_Temp( $deviceRows = array(), $deviceSendVia = 'web' ) {

		if( empty( $deviceRows ) || $deviceSendVia == 'web' )
			return null;

		if( false == isset( $deviceRows[ $deviceSendVia ] ) )
			return null;

		$deviceRow = $deviceRows[ $deviceSendVia ];
		$user_info = JWUser::GetUserInfo( $deviceRow['idUser'] );
		$online = JWIMOnline::GetDbRowByAddressType( $deviceRow['address'] , $deviceSendVia );
		if( false == empty( $online ) && 'OFFLINE' == $online['onlineStatus'] && 'N' == $user_info['isReceiveOffline'])
			return null;
		
		/*if( $deviceSendVia == 'qq' ) {
			$deviceRow = $deviceRows['qq'];
			$online = JWIMOnline::GetDbRowByAddressType( $deviceRow['address'] , $deviceSendVia );
			if( false == empty( $online ) && $online['onlineStatus'] == 'OFFLINE' )
				return null;
		}*/

		return $deviceSendVia;
	}
	
	/**
	 * 为用户选择发送通知的设备；
	 * 如果用户默认为 sms ，则未其检查在线其他im
	 * 如过默认为 web，不发送  | default MSN/GTALK/SKYPE/QQ/SMS/WEB
	 */
	static public function GetAvailableSendVia( $deviceRow = array(), $deviceSendVia = 'web' ) {
		
		//如果没有设备，或用户接受设备为web，那么不需要nudge
		if( empty( $deviceRow ) || $deviceSendVia == 'web' )
			return null;

		return $deviceSendVia;
		
		$originOrder = $deviceSendVia;
		$nudgeOrder = JWDevice::$nudgeOrderArray;

		$shortcutArray = array();	
		foreach( $deviceRow as $type=>$row ){
			// 用户选了不用此设备接受更新，那么算了吧；
			if( $row['enabledFor'] == 'nothing' ) {
				$nudgeOrder = array_diff( $nudgeOrder, array( $type ) );
				continue;
			}
			array_push( $shortcutArray, array( 'type' => $type, 'address' => $row['address'] ) );
		}

		$onlineArray = JWIMOnline::GetDbRowsByAddressTypes( $shortcutArray );

		$onlineIms = array();
		foreach( $nudgeOrder as $device ){
			foreach( $onlineArray as $key=>$o ){
				if( 0 == strncasecmp( $key, $device, strlen($device) ) ){
					if( $o['onlineStatus'] !== 'OFFLINE' ) 
						array_push( $onlineIms, $device );
				}
			}
		}
		
		//如果有在线的IM 设备，选择发送
		if( in_array( $originOrder, $onlineIms ) ) {
			return $originOrder;
		} else if( false == empty( $onlineIms ) ) {
			return $onlineIms[0];
		}
		
		//如果选定了QQ，那么即使不在线，也发送，我们无法判定QQ在线
		if( isset( $deviceRow['qq']) && $originOrder == 'qq' ){
			return 'qq';
		}
		
		//如果到了这里，且绑定了手机，那么发短信吧；
		if( isset( $deviceRow['sms']) ){
			return 'sms';
		}

		return null;
	}
}
?>
