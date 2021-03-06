/*
 *	JiWai.de Lib
 *	Author: zixia@zixia.net
 *	AKA Inc.
 *	2007-05
 */

var JiWai = 
{ 
	mVersion 	: 1,

	GetBgColor : function () 
	{
   		var id_element = $(arguments[0]);

		var color;
		var n=0;
		while ( 100>n++ ) 
		{
			try {
				color = id_element.getStyle('background-color');
			} catch ( e ) {
				break;
			}

			id_element = id_element.getParent();

			if ( 'transparent'!=color )
				break;
		}

		if ( 'transparent'==color )
			color = '#fff';

		return color;
	},

	Yft		: function (selector, hideSecs) 
	{
		return;

		$$(selector).each( function(yft_element) 
		{
			background_color = yft_element.getStyle('background-color');

			orig_color 		= this.GetBgColor(yft_element);

			yellow_color	= new Color('#ff0');
			yellow_color	= yellow_color.mix(orig_color);

			yft_element.effect(
				'background-color'
				,{
				 	duration: 3000
					,transition: Fx.Transitions.Quad.easeOut
				}
			).start(
				orig_color
				,yellow_color
			).chain( function () 
				{
					yft_element.effect
					(
					 	'background-color'
						,{
						 	duration: 1000
							,transition: Fx.Transitions.Bounce.easeOut
						}
					).start(yellow_color,orig_color)
				}
			).chain( function () 
				{
					yft_element.setStyle('background-color', background_color);

					if ( hideSecs )
					{
						(
							function()
							{
								var mySlider = new Fx.Slide(yft_element, {duration: 500});
								mySlider.toggle();
								//yft_element.setStyle('display', 'none');
							}
						).delay(hideSecs*1000); // FIXME
					}
				}
			) 
		}, JiWai ); // end each
	},
	AssetUrl: function(src) {
		return "http://asset."+location.host+src;
	},
	AddScript: function(src) {
		var g = document.createElement("script");
		g.type = "text/javascript";
		g.src = JiWai.AssetUrl(src);
		document.getElementsByTagName('head')[0].appendChild(g);
	},
	OpenLink: function(link) {
		urchinTracker('/wo/outlink/'+link);
		window.open('http://'+link);
	},
	ToggleStar: function(id) {
		var el = $('status_star_'+id);
		var elspan = $('ico_star_'+id);
		new Ajax( '/wo/favourites/create/'+id, {
			method: 'post',
			data: 'post=true',
			headers: {'AJAX':true},
			onSuccess: function(html) {
				var d = html.test(/delete/);
				var t = d ? '取消收藏' : '收藏';
				el.title = el.innerHTML = t;
				if(elspan){
					var n = d ? 'ico_favd' : 'ico_fav';
					elspan.className = n;
				}
			}
		}).request();
	},
	DoTrash: function(id) {
		if (confirm('请确认操作：删除后将永远无法恢复！')) 
		{
			var refresh = false;
			new Ajax( '/wo/status/destroy/'+id, {
				method: 'post',
				data: '_method=delete',
				headers: {'AJAX':true},
				onSuccess: function(e, x) {
					if (refresh) location.reload();
					/*var countReply = $('countReply');
					if (countReply)
						countReply.innerHTML = e;*/
				}
			}).request();
			setTimeout(function() {
				var el = $('status_'+id);
				if (!el) { refresh = true; return; }
				var line = el.getNext() || el.getPrevious();
				(new Fx.Slide(el,{nowrapper:true})).slideOut().addEvent('onComplete', function() { el.remove(); });
				if (line) if (line.hasClass('line')) line.remove();
			}, 0);
		};
	},
	ChangeDevice: function(dev, name) {
		new Ajax( '/wo/account/update_send_via', {
			method: 'post',
			headers: {'AJAX':true},
			data: 'current_user[send_via]='+dev,
			onSuccess: function(html) {
				if($('device')) {
					    $('device').innerHTML = name;
					    (function(){$("othObj").style.visibility="visible"}).delay(500);
				}
			}
		}).request();
	},
	EnableDevice: function(id, postdata) {
		new Ajax( '/wo/devices/enable/'+id, {
			method: 'post',
			headers: {'AJAX':true},
			data: postdata,
			onSuccess: function(html) {
				var el = $('tips_' + id );		
				if(el) { 
					el.innerHTML = html;
					(function(){el.innerHTML='';}).delay(3000);
				}
			}
		}).request();
	},
	Refresh: function() {
		var last = 0;
		$$('#timeline .odd').each(function(el) {
			var id = el.id.split('_')[1];
			if (id>last) last = id;
		});
		if (!last) return;
		new Ajax(location.path, {
			method: 'get',
			data: 'ajax&last='+last,
			onSuccess: function(html) {
				alert(html);
			}
		});
		setTimeout(JiWai.Refresh, RefreshInterval*1000);
	},
	AutoEmote: function() {
		if (!$("timeline")) return;
		_auto_emote = "timeline";
		JiWai.AddScript("/system/emote/themes/default.js");
	},
	ShowThumb: function(el) {
		el.style.display='inline';
	},
	HideThumb: function(el) {
		el.style.display='none';
	},
	ShowTip: function(txt, url) {
		$('sitetip').setHTML(txt + (url ? '<a href="'+url+'">查看</a> ' : '') + '<a href="#" onclick="JiWai.HideTip();">消除</a>');
		$('sitetip').style.display='block';
	},
	HideTip: function() {
		$('sitetip').style.display='none';
	},
	KillNote: function(el) {
		el = $(el);
		(new Fx.Slide(el)).slideOut().addEvent('onComplete', function() { el.remove(); });
		return false;
	},
	requestFriend: function(screenName, el) {
		var mba = new PBBAcpBox({
			name: 'JiWai'
		});
		mba.prompt('对方希望验证你的身份，可以在下面输入一句话介绍你自己：', '', {onComplete:function(v){
			if (v===false) return;
			location.href=el.href+'?note='+encodeURIComponent(v);
		}});
		return false;
	},
    slideTo: function(nameToSlide, typeToSlide, timeToSlide, modeToSlide)
    {

        if(null == typeToSlide ) typeToSlide = 'toggle';
        if(null == timeToSlide ) timeToSlide = '500';

        var JWSlide = new Fx.Slide(nameToSlide, {duration: timeToSlide});
        switch(typeToSlide.toLowerCase())
        {
            case 'toggle':
                JWSlide.toggle(modeToSlide);
                break;
            case 'slidein':
                JWSlide.slideIn(modeToSlide);
                break;
            case 'slidein1':
                JWSlide.hide();
                $(nameToSlide + '1').setStyle('display','block');
                JWSlide.slideIn(modeToSlide);
                $('StartReg').focus();
                //$('user_DeviceNo').focus();
                break;
            case 'slidein2':
                JWSlide.slideIn(modeToSlide);
                (
                function()
                {
                JWSlide.slideOut(modeToSlide);
                }
                ).delay(1000);
                break;
            case 'slideout':
                JWSlide.slideOut(modeToSlide);
                break;
            case 'slideout2':
                JWSlide.slideOut(modeToSlide);
                (
                function()
                {
                JWSlide.slideIn(modeToSlide);
                }
                ).delay(600);
                break;
            case 'slideout12':
                JWSlide.slideOut(modeToSlide);
                (
                function()
                {
                $(nameToSlide + '2').setStyle('display','block');
                $(nameToSlide + '1').setStyle('display','none');
                JWSlide.slideIn(modeToSlide);
                //$('user_email').focus();
                }
                ).delay(600);
                break;
            case 'slideout21':
                JWSlide.slideOut(modeToSlide);
                (
                function()
                {
                $(nameToSlide + '1').setStyle('display','block');
                $(nameToSlide + '2').setStyle('display','none');
                JWSlide.slideIn(modeToSlide);
                }
                ).delay(600);
                break;
            case 'slideout13':
                JWSlide.slideOut(modeToSlide);
                (
                function()
                {
                $('user_DeviceNo3').value=$('user_DeviceNo1').value;
                $('user_nameScreen3').value=$('user_nameScreen1').value;
                $(nameToSlide + '3').setStyle('display','block');
                $(nameToSlide + '1').setStyle('display','none');
                JWSlide.slideIn(modeToSlide);
                //$('user_email3').focus();
                }
                ).delay(600);
                break;
            case 'slideout31':
                JWSlide.slideOut(modeToSlide);
                (
                function()
                {
                $(nameToSlide + '1').setStyle('display','block');
                $(nameToSlide + '3').setStyle('display','none');
                JWSlide.slideIn(modeToSlide);
                }
                ).delay(600);
                break;
        }
    },

    copyToClipboard: function(obj) 
    {
        obj.select();

        txt=obj.value;
	$$('.copytips').each(function(item){item.style.display="none";});
        tip=$(obj.id + "_tip");
	tip.style.display="inline";
	(function(){tip.style.display="none";}).delay(3000);
        if(window.clipboardData) 
        {    
            window.clipboardData.clearData();    
            window.clipboardData.setData("Text", txt);    
        }
        else if(navigator.userAgent.indexOf("Opera") != -1) 
        {    
            window.location = txt;    
        }
        else if (window.netscape) 
        {    
            tip.style.display="none";
        }    
    },

	InitHook: function()
	{
		var pre_init_str = 'jiwai_init_hook_';
		for ( var h in Window )
		{
			if ( 0 != h.indexOf(pre_init_str) )
				continue;
			var func = Window[h];
			if ( typeof func == 'function' )
			{
				try { func(); }catch(e){}
			}
		}
	},
	onLoad: function() 
	{
		if (JWLocation) JWLocation.init();
		if (JWValidator) JWValidator.init();
		if (JWBuddyIcon) JWBuddyIcon.init();

		JiWai.AutoEmote();
		JiWai.InitHook();

		if ( window.RefreshInterval && location.search && location.search.length>1) 
			setTimeout(JiWai.Refresh, RefreshInterval*1000);
	},
	showPublic: function() {
		new Ajax( '/wo/ajax/public', {
			method: 'get',
			headers: {'AJAX':true},
			data: '',
			onSuccess: function(html) {
				var el = $('pub1');		
				if(el) 
					el.innerHTML = html;
			}
		}).request();
	},
	
    ShowDiv: function(name, curr, len) {
        for(var i=1; i<=len; i++)
        {   
            if(i!=curr)
            {   
                $(name+i).className = ''; 
                $(name+'_'+i).style.display = 'none';
            }   
            else
            {   
                $(name+i).className = 'now';
                $(name+'_'+i).style.display = 'block';
            }   
        }   
    },
	Init: function() {
		window.TimeOffset = window.ServerTime ? Math.floor((new Date()).getTime()/1000) - window.ServerTime : 0;
		window.addEvent('domready', JiWai.onLoad);
	}
}

JiWai.Init();
