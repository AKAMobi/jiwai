#!/usr/bin/php
<?php
define ('CONSOLE', true);

require_once(dirname(__FILE__) . "/../../jiwai.inc.php");


/*
function test () {
	echo "okok!\n";
}

$items = array (	'item1'		=> array (	'identifier'	=> 1
											, 'text'		=> 'item1 text'
											, 'callback'	=> 'die'
										)
					, 'item2'	=> array (	'identifier'	=> 2
											, 'text'		=> 'item2 text'
											, 'callback'	=> 'test'
										)
				);

JWConsole::menu ($items, true);
*/

//echo JWConsole::convert("%yzixia");

// 禁止 memcache 的本地缓存
JWMemcache::Instance('default', JWMemcache::TCP, false);

try {
	JWRobot::run();
}catch(Exceptione $e){
	JWLog::Log(LOG_ERR, "JWException: " . $e->GetMessage() );
}
//?>
