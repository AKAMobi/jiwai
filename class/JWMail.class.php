<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * Pear::Mail
 */
require_once('Mail.php');

/**
 * JiWai.de Mail Class
 */
class JWMail {
	/**
	 * Instance of this singleton
	 *
	 * @var JWMail
	 */
	static private $msInstance;

	/**
	 * path_config
	 *
	 * @var
	 */
	static private $msTemplateRoot = null;

	/**
	 * Pear::Mail Object
	 *
	 * @var
	 */
	static private $msMailObject = null;


	/**
	 * Instance of this singleton class
	 *
	 * @return JWMail
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

		self::$msMailObject     =   & Mail::factory('smtp');
		self::$msTemplateRoot	= 	$directory->mail->template ;

		if ( ! file_exists(self::$msTemplateRoot) ){
			throw new JWException( "dir not exist [" . self::$msTemplateRoot . "]");
		}
	}


	/*
	 *	将邮件头中的字符串做 =?UTF-8?B?BASE64?= 的转义
	 *	@param	string	string 是需要转义的字符串
	 *	@param	string	encoding	编码，缺省为UTF-8
	 *	@return	string	转义过的字符串
	 */
	static private function EscapeHeadString($string,$encoding='UTF-8', $force=false )
	{
		if ( 'UTF-8'!=$encoding )
			$string	= mb_convert_encoding($string, $encoding, "UTF-8");

		if( $force ) 
			return '=?' . $encoding . '?B?'. base64_Encode($string) .'?=';

		return preg_replace_callback(
			 '/([\x80-\xFF]+.*[\x80-\xFF]+)/'
			 ,create_function
			 (
			  '$matches'
			  ,"return \"=?$encoding?B?\".base64_encode(\$matches[1]).\"?=\";"
			 )
			 ,$string
			);
	}

	
	/*
	 *	发送邮件的最后一步
	 *	@param options	
						contentType		= true
						messageId		= null
						encoding		= 'UTF-8'
 	 *
 	 *
 	 *
	 *
	 */
	static function SendMail($from, $to, $subject, $message, $options=null)
	{

		self::Instance();

		if ( !isset($options['subjectenc']) )
			$options['subjectenc'] 	= 'GBK';

		if ( !isset($options['encoding']) )
			$options['encoding'] 	= 'UTF-8';

		if ( !isset($options['contentType']) )
			$options['contentType'] = 'text/plain';


		$message_head = preg_replace("/\n/s",' ',$message);
		$message_head = substr($message_head,0,40);
		JWLog::Instance()->Log(LOG_INFO,"JWMail::SendMail($from, $to, $subject,...)");


		if ( 'UTF-8'!=$options['encoding'] )
			$message = mb_convert_encoding($message, $options['encoding'], 'UTF-8');

		$message = chunk_split(base64_encode($message));

		$subject = self::EscapeHeadString($subject, $options['subjectenc'], true);
		$from = self::EscapeHeadString($from, $options['subjectenc']);
		$to = self::EscapeHeadString($to, $options['subjectenc']);

		$headers = array(
			'Mime-Version' => '1.0',
			'Content-Type' => "$options[contentType]; charset=$options[encoding]",
			'Content-Transfer-Encoding' => 'base64',
			'X-Mailer' => 'JMWailer/1.0',
			'From' => $from,
			'To' => $to,
			'Subject' => $subject,
		);

		if ( isset($options['messageId']) )
			$headers["Message-Id"] = "<$options[messageId]>";

		return self::$msMailObject->send($to, $headers, $message);
	}

	/*
	 *	渲染邮件模板的通用部分，注意 User 是收件人。替换的宏包括：
					User.nameScreen
					User.nameFull
					Friend.nameScreen
					Friend.nameFull
 	 *	@param	string	template	模板
	 *	@return	string	template	render好的模板
	 */
	static private function RenderTemplate($template, $user, $friend)
	{
		$replace_array	= array (
			 '/%User.nameUrl%/i' => $user['nameUrl'],
			 '/%User.nameScreen%/i' => $user['nameScreen'],
			 '/%EUser.nameScreen%/i' => UrlEncode($user['nameScreen']),
			 '/%User.nameFull%/i' => $user['nameFull'],

			 '/%Friend.nameScreen%/i' => @$friend['nameScreen'],
			 '/%EFriend.nameScreen%/i' => UrlEncode(@$friend['nameScreen']),
			 '/%EFriend.idUser%/i' => UrlEncode(@$friend['id']),
			 '/%Friend.nameFull%/i' => @$friend['nameFull'],
			 '/%Friend.nameUrl%/i' => @$friend['nameUrl'],
			 '/%Friend.contact%/i' => @$friend['contact'],
		);

		return preg_replace( array_keys($replace_array), array_values($replace_array), $template );
	}

	
	/*
	 *	获取模板文件内容
	 *	@param	string	relTemplateFile	模板文件的相对路径
	 *	@return	string	内容 
	 */
	static private function LoadTemplate($relTemplateFile)
	{
		self::Instance();

		$template_abs_path = self::$msTemplateRoot . $relTemplateFile;

		$file_content = file_get_contents($template_abs_path);

		if ( empty($file_content) )
			throw new JWException("no template found at [$template_abs_path]");

		return $file_content;
	}


	/*
	 *	将模板文件进行初步解析，分离 META 和 HTML 并返回
	 *	@param	string	relTemplateFile	模板文件的相对路径
	 *	@return	array	array ( 'html'	=> '', 'subject' => '', 'from' => '' )
	 */
	static private function ParseTemplate($templateData)
	{
		$template_info = array();

		if ( !preg_match('/^(.+?)[\r\n]{2}(.+)$/s',$templateData,$matches) )
			throw new JWException("template split meta & body error for [$templateData]");

		$template_info['html']	= $matches[2];
		$meta_lines = split("\n", $matches[1]);

		foreach ( $meta_lines as $meta_line )
		{
			if ( ! preg_match('/^([^:]+):\s*(\S.*)$/',trim($meta_line),$matches) )
				throw new JWException("template header meta parse error");

			$key = strtolower($matches[1]);
			$val = $matches[2];
			$template_info[$key] = $val;
		}

		return $template_info;
	}

	/*
	 *	$user 将 $friend 新加为好友，给 $friend 发送一封通知信
 	 *	@param	array	user	user_info的结构
 	 *	@param	array	friend	user_info的结构
 	 */
	static public function SendMailNoticeEverInvite($user, $friend)
	{
		if ( !JWUser::IsValidEmail($user['email']) )
			return;

		$template_file	= 'NoticeEverInvite.tpl';

		$template_data = self::LoadTemplate($template_file);
		$template_data = self::RenderTemplate($template_data,$user,$friend);
	

		$template_info = self::ParseTemplate($template_data);

		return self::SendMail( $template_info['from']
			,$user['email']
			,$template_info['subject']
			,$template_info['html']
		);
	}

	/*
	 *	$user 将 $friend 新加为好友，给 $friend 发送一封通知信
 	 *	@param	array	user	user_info的结构
 	 *	@param	array	friend	user_info的结构
 	 */
	static public function SendMailNoticeNewFriend($user, $friend)
	{
		if ( !JWUser::IsValidEmail($friend['email']) )
			return;

		$template_file	= 'NoticeNewFriend.tpl';

		$template_data = self::LoadTemplate($template_file);
		$template_data = self::RenderTemplate($template_data,$user,$friend);
	

		$template_info = self::ParseTemplate($template_data);

		return self::SendMail( $template_info['from']
			,$friend['email']
			,$template_info['subject']
			,$template_info['html']
		);
	}


	/*
	 *	$user 向 $mails 发送邀请注册信
 	 *	@param	array	user	user_info的结构
 	 *	@param	string	email	邮件接收者
 	 */
	static public function SendMailInvitation($user, $email, $message, $options=array())
	{
		if ( false == JWUser::IsValidEmail($email) )
			return false;

		return self::SendMailInvitation2009($user, $email, $message);

		$template_file = isset( $options['template_file'] ) 
			? $options['template_file'] : 'Invitation.tpl';

		$send_options = array(
			'contentType' => isset($options['content_type']) ? $options['content_type'] : null,
		);

		$has_photo = !empty($user['idPicture']);
		if ( $has_photo ){
			$photo_url = JWPicture::GetUserIconUrl($user['id'],'thumb96');
		}else{
			$photo_url = JWTemplate::GetAssetUrl('/img/stranger.gif');
		}

		$num_status = JWStatus::GetStatusNum( $user['id'] );
		$num_following = JWDB_Cache_Follower::GetFollowingNum( $user['id'] );
		$num_follower = JWDB_Cache_Follower::GetFollowerNum( $user['id'] );

		$invitation_code = JWUser::GetIdEncodedFromIdUser( $user['id'] );
	
		$friend['nameFull'] = '敬启者';

		$template_data = self::LoadTemplate($template_file);
		$template_data = self::RenderTemplate($template_data,$user, $friend);
	
		$template_data = preg_replace('/%INVITATION_ID%/i', $invitation_code, $template_data);
		$template_data = preg_replace('/%SUBJECT%/i', $message, $template_data);
		$template_data = preg_replace('/%DATE%/i', date('Y/m/d'), $template_data);
		$template_data = preg_replace('/%Photo.Url%/i', $photo_url, $template_data);

		$template_data = preg_replace('/%Num.Status%/i', $num_status, $template_data);
		$template_data = preg_replace('/%Num.Following%/i', $num_following, $template_data);
		$template_data = preg_replace('/%Num.Follower%/i', $num_follower, $template_data);

		$template_data = preg_replace('/%Invitation.Code%/i', $invitation_code, $template_data);

		$template_info = self::ParseTemplate($template_data);

		return self::SendMail( $template_info['from']
			,$email
			,$template_info['subject']
			,$template_info['html']
			,$send_options
		);
	}


	/*
	 *	$sender 发送悄悄话 $message 给 $receiver，并给 $friend 发送一封通知信
 	 *	@param	array	sender		user_info的结构
 	 *	@param	array	receiver	user_info的结构
 	 *	@param	string	message		direct message
 	 */
	static public function SendMailNoticeDirectMessage($sender, $receiver, $message, $device, $message_id=0)
	{
		if ( !JWUser::IsValidEmail($receiver['email']) )
			return;

		$template_file	= 'NoticeDirectMessage.tpl';

		$template_data = self::LoadTemplate($template_file);
		$template_data = self::RenderTemplate($template_data,$receiver,$sender);
	
		$template_data = preg_replace('/%DirectMessage.message%/i'	,$message	,$template_data);
		$template_data = preg_replace('/%DirectMessage.device%/i'	,$device	,$template_data);
		$template_data = preg_replace('/%DirectMessage.idMessage%/i'	,$message_id	,$template_data);

		$template_info = self::ParseTemplate($template_data);

		return self::SendMail( $template_info['from']
			,$receiver['email']
			,$template_info['subject']
			,$template_info['html']
		);
	}


	/*
	 *	向 $user 发送重置密码的邮件
 	 *	@param	array	user		user_info的结构
 	 *	@param	string	secret		密码
 	 */
	static public function ResendPassword($user, $url)
	{
		if ( !JWUser::IsValidEmail($user['email'],true) )
			return;

		$template_file	= 'ResetPassword.tpl';

		$template_data = self::LoadTemplate($template_file);
		$template_data = self::RenderTemplate($template_data,$user,$user);
	
		$template_data = preg_replace('/%RESET_PASSWORD_URL%/i'	,$url	,$template_data);

		$template_info = self::ParseTemplate($template_data);

		return self::SendMail( $template_info['from']
			,$user['email']
			,$template_info['subject']
			,$template_info['html']
		);
	}

	static public function SendMailInvitation2009($user, $email, $subject) 
	{
		$param = array(
			'g_current_user_id' => $user['idUser'],
		);
		ob_start();
		$element = JWElement::Instance();
		$element->mail_template_invitation($param);
		$content = ob_get_clean();
		
		//send
		$from = "{$user['nameScreen']}（{$user['nameFull']}） <noreply@jiwai.de>";
		$send_options = array( 
				'contentType' => 'text/html',
				);
		return self::SendMail( $from, $email, $subject, $content, $send_options);
	}
}
?>
