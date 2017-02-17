﻿
(function($){
$.fn.yomi=function(){
	var data="";
	var _DOM=null;
	var TIMER;
	createdom =function(dom){
		_DOM=dom;
		data=$(dom).attr("data");
		data = data.replace(/-/g,"/");
		data = Math.round((new Date(data)).getTime()/1000);
		
        $(_DOM).append("<span class='jishibb yomiday'></span><span class='jishibb yomihour'></span><span class='jishibb yomimin'></span><span class='jishibb yomisec'></span>");
		reflash();

	};
	reflash=function(){
		var	range  	= data-Math.round((new Date()).getTime()/1000),
					secday = 86400, sechour = 3600,
					days 	= parseInt(range/secday),
					hours	= parseInt((range%secday)/sechour),
					min		= parseInt(((range%secday)%sechour)/60),
					sec		= ((range%secday)%sechour)%60;
                    
                    if(sec<0){
                        $(_DOM).find(".yomiday").html('00');
                		$(_DOM).find(".yomihour").html('00');
                		$(_DOM).find(".yomimin").html('00');
                		$(_DOM).find(".yomisec").html('00');
                    }else{
                        $(_DOM).find(".yomiday").html(nol(days));
                        days_hour = days*24;
                		$(_DOM).find(".yomihour").html(nol(hours));
                		$(_DOM).find(".yomimin").html(nol(min));
                		$(_DOM).find(".yomisec").html(nol(sec));
                    }
		

	};
	TIMER = setInterval( reflash,1000 );
	nol = function(h){
					return h>9?h:'0'+h;
	}
	return this.each(function(){
		var $box = $(this);
		createdom($box);
	});
}
})(jQuery);
$(function(){
	$(".yomibox").each(function(){
		$(this).yomi();
	});

});