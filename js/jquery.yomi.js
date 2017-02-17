
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
		
		//$(_DOM).append("<div class='time'><ul><li class='yomihour'></li><li class='yomimin'></li><li class='yomisec'></li></ul></div>")
		//<p class='yomihour'></p><p class="maohao">:</p><p class='yomimin'></p><p class="maohao">:</p><p class='yomisec'>59</p>
        $(_DOM).append("<p class='yomihour hour'></p><p class='maohao'>&nbsp;</p><p class='yomimin'></p><p class='maohao'>&nbsp;</p><p class='yomisec'>59</p>");
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
                		$(_DOM).find(".yomihour").html('00');
                		$(_DOM).find(".yomimin").html('00');
                		$(_DOM).find(".yomisec").html('00');
                    }else{
                        $(_DOM).find(".yomiday").html(nol(days));
                        days_hour = days*24;
                		$(_DOM).find(".yomihour").html(nol(hours+days_hour));
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