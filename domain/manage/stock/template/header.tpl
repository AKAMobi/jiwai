<html>
<head>
<title>叽歪de(JiWai&trade;)股票社区管理系统</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="${JWTemplate::GetAssetUrl('/lib/mootools/mootools.v1.11.js')}"></script>
<script type="text/javascript" src="${JWTemplate::GetAssetUrl('/js/jiwai.js')}"></script>
<script type="text/javascript" src="${JWTemplate::GetAssetUrl('/js/location.js')}"></script>
<script type="text/javascript" src="${JWTemplate::GetAssetUrl('/js/validator.js')}"></script>
<style>
body { margin:0px; line-height:120%; font-size:14px; width:1000px;}
table{ width: 750px; }
textarea{font-size:14px;}
textarea.cmd { width:740px;height:40px; float:left; line-height:120%;}
textarea.res { width:740px;height:380px; color:#FFF; line-height:120%; background-color:#000; text-align:left; }

td,th{ font-size:14px; }
#header {padding:20px;text-align:center;font-size:32px; font-weight:bold; background-color:#456789; color:#FFF;}
#footer {padding:10px;text-align:center;font-size:14px; border-top:1px solid #999;}

.notice { padding:10px; border:1px solid #900; margin:10px; background:#FFD9D9; width:720px;}
.page { width:100%; }
.clear { clear:both; }

#left { width:180px; float:left; padding:10px; border-right:1px solid #999;}
#left h2 { font-size:16px; margin:0px; }
#left ul { margin:0px; padding:0px; display:block;}
#left li { margin:5px; list-style:none; display:block; margin:5px;}
#left li a{color:#00F;}
#left li a:hover { font-size:15px; font-weight:bold; }
#left li.selected a{ font-size:15px; font-weight:bold; color:#F00;}

#main { padding:10px; float:left;clear:right;}
#main h2 { font-size:20px; margin:0 0 15px 0; }
#main h3 { font-size:16px; margin:0 0 10px 0; }

.result{ margin:15px; padding:0px; text-align:center; color:#104755; background-color:#b0b4c8; }
.result tr{ text-align:right; color:#333; font-weight: normal; background-color:#fff; nowrap; }
.result th{ background:orange; color:#333; border: 0px solid #777; font-weight: bold; nowrap; }
.result td{ text-align:right; color:#333; font-weight: normal; nowrap; }

.pages {float:left; width:360px; padding-top:5px; height:100%;}
.pages a { float:left; border:1px solid #E3E3E3; padding:2px 8px; background-color:#CB4; color:#00F; font-family:Verdana, Arial, Helvetica, sans-serif; margin:5px; text-decoration:none;}
.pages .now {border:none; background-color:#00F; color:#000000; font-weight:bold;}

i{color:red;font-size:12px;display:block;margin:0px;font-style:normal;}
</style>
</head>
<body>
<div id="header">
	叽歪de股票社区管理系统
</div>

<div class="page">
	<div id="left">
		<!--{include menu}-->
	</div>
	<div id="main">
	<!--{if ($notice = GetNotice() )}-->
		<div class="notice">{$notice}</div>
	<!--{/if}-->
