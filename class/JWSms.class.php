<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */


/**
 * JiWai.de Sms Class
 */
class JWSms {
	/**
	 * Instance of this singleton
	 *
	 * @var JWSms
	 */
	static $msInstance;

	/**
	 * path_config
	 *
	 * @var mQueuePathMo mQueuePathMt
	 */
	static $msQueuePathMo;
	static $msQueuePathMt;


	/**
	 * bad msg here
	 */
	static $msQuarantinePathMo;
	static $msQuarantinePathMt;

	/**
	 * SP GID
	 */
	const GID_CHINAMOBILE 		= 1;   //9911 old china mobile
	const GID_CHINAMOBILE_TWO 	= 85;  //50136 new china mobile
	const GID_UNICOM		= 45;  //9501 new unicom
	const GID_UNICOM_TWO		= 3;   //9318 old unicom
	const GID_PAS			= 52;  //99318
	const GID_UNKNOWN		= 1;

	/**
	 * Error_Code
	 */

	static $errorCode = array(
					0 => 'HE_ERR_OK', 
					52 => 'HE_ERR_MSG',
					53 => 'HE_ERR_USERNUMBER',
				     	54 => 'HE_ERR_PID', 
					55 => 'HE_ERR_MOFLAG',
					56 => 'HE_ERR_GATEWAY', 
					57 => 'HE_ERR_MSGTYPE', 
					58 => 'HE_ERR_ILLEGAL_IP', 
					59 => 'HE_ERR_ILLEGAL_APPID', 
					124 => 'HE_ERR_ZIXIA_MOBILE_NO',

					129 => 'HE_ERR_OK_NEW',
			);



	/**
	 * Instance of this singleton class
	 *
	 * @return JWSms
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
		$config 	= JWConfig::Instance();
		$directory 	= $config->directory;

		self::$msQueuePathMo	= $directory->queue->root 
								. $directory->queue->sms
								. $directory->mo
								;

		self::$msQueuePathMt	= $directory->queue->root 
								. $directory->queue->sms
								. $directory->mt
								;

		self::$msQuarantinePathMo	= $directory->quarantine->root 
									. $directory->quarantine->sms
									. $directory->mo
									;

		self::$msQuarantinePathMt	= $directory->quarantine->root 
									. $directory->quarantine->sms
									. $directory->mt
									;
		self::InitDirectory();

	}


	static function InitDirectory()
	{
		if ( ! file_exists(self::$msQueuePathMo)
				|| ! file_exists(self::$msQueuePathMt)
				|| ! file_exists(self::$msQuarantinePathMo)
				|| ! file_exists(self::$msQuarantinePathMt)
				)
		{
			mkdir(self::$msQueuePathMo,0700,true);
			mkdir(self::$msQueuePathMt,0700,true);
			mkdir(self::$msQuarantinePathMo,0700,true);
			mkdir(self::$msQuarantinePathMt,0700,true);
		}
	
		if ( ! is_writeable(self::$msQueuePathMo) 
				|| !is_writeable(self::$msQueuePathMt)
				|| !is_writeable(self::$msQuarantinePathMo)
				|| !is_writeable(self::$msQuarantinePathMt)
				)
		{
			/*
			echo self::$msQueuePathMo
					 . "<br>\n" . self::$msQueuePathMt
					 . "<br>\n" . self::$msQuarantinePathMo
					 . "<br>\n" . self::$msQuarantinePathMt
					 . "<br>\n" ;
			*/

			throw new JWException("JWSms queue_path not writeable");
		}
		return true;
	}



	/*
	 *
	 * @return true is succeed, false is err
	 */
	static public function ReceiveMo($mobileNo, $serviceNo, $smsMsg, $linkId, $gateId)
	{
		self::Instance();

		/* check null robotmsg */
		if ( null==$smsMsg && false==JWDevice::IsExist($mobileNo, 'sms', false) )
		{
			$smsMsg = '第一次叽歪，请大家多多关照。';
		}

		$now = strftime("%Y-%m-%d %H:%M:%S",time());

		JWLog::Instance()->Log(LOG_INFO, "$now ReceiveMo: msg [$smsMsg]"
					. " from mobile [$mobileNo] to service [$serviceNo]"
					. " by link [$linkId] through [$gateId]" );

		$code = JWSPCode::GetCodeByGidAndMobileNo( $gateId, $mobileNo );
		if( empty( $code ) ) {
			$gateNo = 9911;
		}else{
			$gateNo = $code['code'];
		}

		$robot_msg = new JWRobotMsg();
		$robot_msg->Set($mobileNo, 'sms', $smsMsg);
		$robot_msg->SetHeader('serveraddress', $gateNo.$serviceNo);
		$robot_msg->SetHeader('linkid', $linkId);

		$robot_msg->SetFile( self::$msQueuePathMo . $robot_msg->GenFileName() );

		return $robot_msg->Save();
	}

	/*
	 *
	 *
	 */
	static public function SubscribeReport($mobileNo, $isSub, $productId, $gateId)
	{
		self::Instance();

		JWLog::Instance()->Log ( LOG_INFO, "SubscribeReport: mobile [$mobileNo] had $isSub-ed [$productId] @[$gateId]" );
		return true;
	}


	/*
	 *
	 *
	 */
	static public function DeliveReport ($mobileNo, $msgId, $deliveState, $errCode, $gateId)
	{
		self::Instance();

		JWLog::Instance()->Log ( LOG_INFO, "DeliverReport: [$msgId] of [$mobileNo] state [$deliveState], err [$errCode] @[$gateId]" );
		return true;
	}


	/*
	 *
	 *
	 * 9911(chn) & 9318(uni) mt
	 *
	 */
	static public function SendMt ($mobileNo, $smsMsg, $serverAddress=null, $linkId=null)
	{
		/* {{{ */
		// For Green Channel 
		if ( self::IsGreenNo($mobileNo) )
		{
			require_once( dirname(__FILE__) .'/../lib/3p/renweiben/index.php' );
			$ret = send_sms_renweiben( $mobileNo, $smsMsg );
			self::LogSentResult($ret, 'leoman', $mobileNo, 'RenWeiBen');
			return true;
		}
		/* }}} */


		/* {{{ */ 
		//Add RateLimit For Sms Send , seek@jiwai.com 2008-05-29;
		$r_facility = 'mt-sms'; 
		$r_window = 86400;  //one day;
		$r_threshold = 30; //must less than 100;
		$r_credential = $mobileNo;
		if ( JWRateLimit::Protect($r_facility, $r_credential, 
			$r_credential, $r_window) ) {
			return true;
		}
		if ( time() > strtotime('00:00:00') 
			&& time() < strtotime('07:00:00') ) {
			return true;
		}
		/* }}} */

		// 第三方下行接口，只对移动有效
		$MT_HTTP_URL_3RD	= 'http://211.157.106.111:8092/sms/third/submit';

		// 普通下行接口，移动联通小灵通都可以使用。不过要提供 linkId
		$MT_HTTP_URL_LINKID	= 'http://211.157.106.111:8092/sms/submit';

		if ( null == $serverAddress )
		{
			$code = JWSPCode::GetCodeByMobileNo( $mobileNo );
		}
		else
		{
			$code = JWSPCode::GetCodeByServerAddressAndMobileNo( $serverAddress, $mobileNo );
		}
		if( empty( $code ) ) {
			JWLog::Instance()->Log(LOG_ERR,"JWSms::SendMt Get Invalid SpCode with $mobileNo,$serverAddress.");
			return true;
		}

		/**
		 * for 1065055
		 */
		if ( 0===strpos($code['code'], '1065055') )
		{
			$server_address = ( $serverAddress==null) ? 
				$code['code'] . $code['func'] . $code['funcPlus'] : $serverAddress;

			return JWRobot::SendMtRawQueue($mobileNo, 'mobiz', $smsMsg, $server_address);
		}

		$func = ( null == $serverAddress || 0 == $serverAddress ) ? 
			$code['func'] . $code['funcPlus'] : substr( $serverAddress, strlen( $code['code']) );
		$gid = $code['gid'];
		$appid = 93; // 数字，应用编号，需分配

		/* 	
		 *	如果有 linkId，则使用 linkid 参数（移动、联通通用）；
		 *	如果没有 LinkId，则只能给移动用户第三方下行
		 */
		if ( ( true || empty($linkId) ) && $gid == self::GID_CHINAMOBILE )
			$MT_HTTP_URL = $MT_HTTP_URL_3RD;
		else
			$MT_HTTP_URL = $MT_HTTP_URL_LINKID;


		$mt_type = array(
			'MT_TYPE_MO_FIRST' => 0, // MO点播引起的第一条MT消息
			'MT_TYPE_MO_NOT_FIRST' => 1, // MO点播引起的非第一条MT消息
			'MT_TYPE_NO_MO' => 2, // 非MO点播引起的MT消息
			'MT_TYPE_SYSTEM' => 3, // 系统反馈引起的MT消息
		);

		$mt_fee	= array(
			'FEE_FREE' => 0, // 免费消息
			'FEE_NORMAL' => 1, // 正常收费
			'FEE_MONTHLY_LIST' => 2, // 包月话单
			'FEE_MONTHLY_DOWNLOAD' => 3, // 包月下发
		);


		$dst = $mobileNo;	// 数字,目的手机号 
		$msgfmt = 0;	// 英文，如果是中文，就去掉这个参数

		list($msg,$msgfmt) = self::FormatSms($smsMsg, $dst);

		/**
		 * Other SMS send via
		 */
		foreach ( $msg as $m )
		{
			$ret = null;
			switch( $code['code'] )
			{
				case '10605328':
					$ret = self::SendSmsViaSJZK( $m, $dst, $serverAddress );
					self::LogSentResult($ret, $serverAddress, $dst, 'ShiJiZhongKai');
					break;
				case '10668228':
					$ret = self::SendSmsViaLinkTone( $m, $dst, $serverAddress );
					self::LogSentResult($ret, $serverAddress, $dst, 'ZhangShangLingTong');
					break;
			}
			if ( is_bool( $ret ) )
				return $ret;
		}
		/**
		 * End send via
		 */

		$pid = 0;
		if( $gid == self::GID_UNICOM ) {
			if( $func == 456 ) 
				$pid = 46;
			else
				$pid = 47;
		}
		
		$moflag = $mt_type['MT_TYPE_NO_MO'];
		$msgtype = $mt_fee['FEE_FREE'];
		
		$param = 'nofilter';

		// appid=XX&gid=X&dst=1331234567&pid=XX&msg=XXX&linkid=XXX&func=XXX&moflag=X&msgtype=X 
		$ret = true;
		foreach( $msg as $m ) {

			$m = urlEncode( mb_convert_encoding($m, 'GB2312', 'UTF-8, GB2312') );
			$rpc_url = $MT_HTTP_URL . "?appid=$appid"
				. "&gid=$gid"
				. "&dst=$dst"
				. "&pid=$pid"
				. "&msg=$m"
				. "&linkid=$linkId"
				. "&func=$func"
				. "&moflag=$moflag"
				. "&msgtype=$msgtype"
				. "&param=$param"
				;

			if ( isset($msgfmt) )
				$rpc_url .= "&msgfmt=$msgfmt";

			$ret =  self::SendSmsViaUrl( $rpc_url ) ;
			self::LogSentResult($ret, $serverAddress, $dst, 'QianXiang');

			if( $ret == false )
				return false;

		}

		return true;
	}


	static public function SendSmsViaUrl( $rpc_url ) {

		if( true ) {
			$v = intval( JWRuntimeInfo::Get('ROBOT_COUNT_SMS_MT') );
			JWRuntimeInfo::Set( 'ROBOT_COUNT_SMS_MT', ++$v );
		}

		JWLog::Instance()->Log(LOG_INFO,"JWSms::SendMt Calling: [$rpc_url]");

		$retry = 0;

		$return_content = @file_get_contents($rpc_url);

		while ( empty($return_content) && $retry++<3 )
		{
			JWLog::Instance()->Log(LOG_ERR,"JWSms::SendMt connect to sp failed. retry #$retry.");
			$return_content = @file_get_contents($rpc_url);
		}

		if ( empty($return_content) )
		{
			JWLog::Instance()->Log(LOG_CRIT,"JWSms::SendMt connect to sp failed after retry $retry times.");
			return false;
		}


		if ( !preg_match('/^(\d+)\s+(\S+)$/',$return_content,$matches) )
		{
			if ( preg_match('/^(\d+)$/',$return_content,$matches) )
				$ret = $matches[1];

			JWLog::Instance()->Log(LOG_ERR, "JWSms::SendMt return content parse err:[$return_content](".self::$errorCode[$ret].")");
			return false;
		}

		$ret	= $matches[1];
		$msgid	= $matches[2];

		JWLog::Instance()->Log(LOG_INFO,"JWSms::SendMt succ. returns: ret[$ret](". self::$errorCode[$ret]. ") / msgid[$msgid]");

		return true;
	}

	/*
	 * 根据是否包含中文决定发送 140(ascii) 还是 70(中文)
	 * @return array, (one_sms_string, msg_fmt)
	 */
	static function FormatSms($smsMsg, $mobileNo)
	{
		$smsMsg = preg_replace('/\r/', "", $smsMsg);

		//$smsMsg = preg_replace('/\n/', "\r\n", $smsMsg);
		// XXX 
		// 1. treo 650 显示 \n 有时候不正常
		// 2. JWRobotMsg->Save()后，文件中为什么会多出\r字符？

		$onlyOne = false;
		if( true && $mobileNo )
		{
			$moKey = JWDB_Cache::GetCacheKeyByFunction( array( 'JWWosms', 'UserMO'), $mobileNo );
			$memcache = JWMemcache::Instance();
			$moed = $memcache->Get( $moKey );
			$onlyOne = ( $moed ) ? false : true;
		}

		if ( preg_match('/^[\x00-\x7F]+$/', $smsMsg) )
		{ 	// 英文字符

			return array ( self::SplitSms($smsMsg, 138, 'UTF-8'), 0 , $onlyOne);
		}

		// 有中文

		$smsMsg = self::SplitSms( $smsMsg, 69, 'UTF-8' , $onlyOne);

		return array ($smsMsg, null);
	}

	static function SplitSms($smsMsg, $len=70, $encoding='UTF-8', $onlyOne = false ) {

		if( $onlyOne ) {
			return array( mb_substr($smsMsg, 0, $len, $encoding) );
		}

		if( ( $strlen = mb_strlen( $smsMsg, $encoding ) ) > $len ) {

			$msgArray = array();

			$len = $len - 4;
			$times = ceil( $strlen / $len );

			for( $i = 0; $i < $times; $i++ ) {
				$index = $i * $len;
				$c = mb_substr( $smsMsg, $index, $len, $encoding );
				$c = (1 + $i) . '/' . $times .' '. $c;
				array_push( $msgArray, $c );
			}

			return $msgArray;
		}

		return array( $smsMsg );
	}

	static function SendSmsViaSJZK($message, $address, $server_address)
	{
		$sn = 'SDK-BBX-010-01953';
		$pwd = '649707';

		$message = mb_convert_encoding($message, 'GBK', 'UTF-8,GBK');
		$message = urlEncode( $message );
		$url = "http://211.157.113.148:8060/z_send.aspx?sn=$sn&pwd=$pwd&mobile=$address&content=$message&ext=";
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);  
		$return_content = trim(curl_exec($ch));
		curl_close($ch);

		if ( '1' == $return_content )
		{
			JWLog::Instance()->Log(LOG_INFO,"JWSms::SendMt succ. returns: $return_content");
			return true;
		}
		else
		{
			error_log( var_export( $return_content , true ) );
			JWLog::Instance()->Log(LOG_ERR,"JWSms::SendMt fail. returns: $return_content");
			return false;
		}
	}

	static function SendSmsViaLinkTone($message, $address, $server_address, $linkid=null)
	{
		$rpc_url = "http://211.99.200.90:8080/synd/mt_service";

		$toicp = substr($server_address, 8);
		$message = mb_convert_encoding($message, 'GBK', 'UTF-8,GBK');

		$data = <<<_XML_
<?xml version="1.0" encoding="GBK"?>
<request>
	<coopid>jiwainet</coopid>
	<linkid/>
	<product>jiwainet</product>
	<msgtype>TEXT</msgtype>
	<content>$message</content>
	<tomobile>$address</tomobile>
	<channel/>
	<spid>cmcc-8228</spid>
	<toicp>$toicp</toicp>
	<feecategory>FREE</feecategory>
	<url/>
</request>
_XML_;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $rpc_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$return = curl_exec($ch);
		curl_close($ch);

		return preg_match( '/>ok</', $return ) && preg_match( '/>0</', $return );

	}

	static public function SendSmsViaGreenChannel($address, $message)
	{
		list($msg_array,$msg_fmt) = self::FormatSms($message, $address);

		foreach( $msg_array AS $one )
		{
			$s = '1066822861';
			$r = self::SendSmsViaLinkTone($one, $address, $s);
			self::LogSentResult($r, $s, $address, 'LinkTone61');
		}
		return true;
	}

	static function LogSentResult($bool=true, $server_address, $mobile_no, $supportor="QianXiang")
	{
		$result =  $bool ? 'SUCC' : 'FAIL';
		error_log( "[$result]:\tsms://$server_address\t=>\tsms://$mobile_no\t[$supportor]" );
	}

	static function IsGreenNo($mobileNo) {
		$fn = FRAGMENT_ROOT . 'nocommon/greenno.txt';
		if ( file_exists($fn)) {
			$c = file_get_contents($fn);
			return preg_match("/$mobileNo/", $c);
		}
		return false;
	}

}
?>
