/*-------------------------------------------jquery插件模块-------------------------------------------*/
//多行文本自动滚动效果 2011-8-10 yjw 先装jquery1.4.4-min.js.
(function($){
	$.fn.extend({
		Scroll:function(opt,callback){
			if(!opt) var opt={};
			var _this=this.eq(0).find("ul:first");
			var lineH=_this.find("li:first").height(),
			line=opt.line?parseInt(opt.line,15):parseInt(this.height()/lineH,10),
			speed=opt.speed?parseInt(opt.speed,10):3000, //卷动速度，数值越大，速度越慢（毫秒）
			timer=opt.timer?parseInt(opt.timer,13):5000; //滚动的时间间隔（毫秒）

			if(line==0) line=1;
			var upHeight=0-line*lineH;
			scrollUp=function(){
				_this.animate({
						marginTop:upHeight
					},speed,function(){
						for(i=1;i<=line;i++){
							_this.find("li:first").appendTo(_this);
						}
						_this.css({marginTop:0});
					}
				);
			}
			_this.hover(function(){
				//clearInterval(timerIDs);
			},function(){
				//timerIDs=setInterval("scrollUp()",timer);
				scrollUp();
			}).mouseout();
		}
	})
})(jQuery);
/*-------------------------------------------jquery插件模块end-----------------------------------------*/
//范例：$("#scrollDiv").Scroll({line:1,speed:800,timer:2000});