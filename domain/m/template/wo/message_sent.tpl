<!--{include header}-->
<h2><a href="/wo/message/inbox">收件箱</a>｜发件箱｜<a href="/wo/message/notice">提醒</a></h2>

<ul>
<!--{foreach $messages as $message}-->
<li>
    发给 <a href="${buildUrl('/'.$users[$message['idUserReceiver']]['nameUrl'].'/')}" rel="contact">${getDisplayName($users[$message['idUserReceiver']])}</a>：{$message['message']}
    <span class="stamp">
    ${JWStatus::GetTimeDesc($message['timeCreate'])}
    <a href="/wo/message/destroy/{$message['idMessage']}">删除</a>
    </span>
</li>
<!--{/foreach}-->
</ul>
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
