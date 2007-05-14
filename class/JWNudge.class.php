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

	/*
	 *	向 $idUSers 的设备上发送消息
	 *	@param	array of int	$idUsers
	 *	@param	string			$message
	 *	@type	string			$messageType	{'nudge'|'direct_messages'}
	 *	@return	
	 */
	static public function NudgeUserIds($idUsers, $message, $messageType='nudge')
	{
		$user_rows		= JWUser::GetUserRowsByIds			($idUsers);
		$device_rows	= JWDevice::GetDeviceRowsByUserIds	($idUsers);

		foreach ( $idUsers as $user_id )
		{
			$user_row	= $user_rows	[$user_id];
			$device_row	= $device_rows	[$user_id];

			// device_num 只可能：0:没有，1:有一个（im or sms），2:两个都有（im and sms）
			$device_num = count(array_keys($device_row));

			
			switch( $device_num ) 
			{	
				case 0:
					// 如果用户没有 sms / im ，处理下一个
					continue;
					break;

				case 1:
					$device_type	= array_keys($device_row);
					$device_type	= $device_type[0];

					JWNudge::NudeDevice( $device_row, $device_type, $message, $messageType );
					break;
				
				case 2:
					// 用户有 im & sms, fall to default
				default:
					switch ( $user_row['deviceSendVia'] )
					{
						case 'sms':
							// legal, fall to im
						case 'im':
							JWNudge::NudeDevice( $device_row, $user_row['deviceSendVia'], $message, $messageType );
							break;

						case 'none':
							//fall to default
						default:
							break;
					}
					break;
			}
		}
	}


	/*
	 *	向一个 device 上发送消息
	 *	@param	array	$deviceRow	JWDevice::GetDeviceRowsByUserIds 的返回结构
	 *	@param	string	$smsOrIm	{'sms'|'im'}
	 *	@param	string	$message
	 *	@param	string	$messageType	{'nudge'|'direct_messages'}
	 */
	static public function NudeDevice( $deviceRow, $smsOrIm, $message, $messageType )
	{
		// 对特定的 device ( sms / im） - 查看 Device.enabledFor:
		// enabledFor 可能有三个值: everything / nothing / direct_messages
		switch ( $deviceRow[$smsOrIm]['enabledFor'] )
		{
			case 'direct_messages':
				if ( 'direct_messages'!=$messageType )
					break;
				// if equal, fall to everything: send it.

			case 'everything':
				$type		= $deviceRow[$smsOrIm]['type'];
				$address 	= $deviceRow[$smsOrIm]['address'];
				JWRobot::SendMtRaw($address, $type, $message);

				break;

			case 'nothing':
				// fall to default
			default:
				break;
		}
	}
}
?>
