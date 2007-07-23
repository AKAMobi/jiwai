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
/*
(function(){alert(1);return this;}).delay(1000);

alert('ok');
//.chain(function(){alert(2)}).chain(function(){alert(3)}).chain(function(){alert(4)});
*/

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
	ApplyFav: function(el) {
		if (el.ev) return;
		el.ev = true;
		el.addEvent('click', function(e){
			var id = el.id.split('_')[2];
			new Ajax( '/wo/favourites/'+((this.src || this.innerHTML).indexOf('full')==-1 ? 'create' : 'destroy')+'/'+id, {
				method: 'get',
				headers: {'AJAX':true},
				async: true,
				evalScripts: true,
				evalResponse: true,
				onRequest: function() { 
					$('status_star_'+id).src=JiWai.AssetUrl('/img/icon_throbber.gif') 
				}
			}).request();
		});
	},
	AutoEmote: function() {
		$$('#timeline .status_favor').each(JiWai.ApplyFav);
		if (!$("timeline")) return;
		_auto_emote = "timeline";
		JiWai.AddScript("/system/emote/themes/default.js");
	},
	Init : function() {
		window.TimeOffset = window.ServerTime ? Math.floor((new Date()).getTime()/1000) - window.ServerTime : 0;
		window.addEvent('domready', JiWai.AutoEmote); 
	}
}

JiWai.Init();
