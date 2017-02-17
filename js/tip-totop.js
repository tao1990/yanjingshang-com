/*===========================回到顶部,解决了ie6的兼容.先要有jquery1.4.4-min.js文件. 2011-6-9====================*/
//不要写css文件.
$(function(){
var imgUrl="http://www.easeeyes.com/themes/default/images/to-top.gif";
$('head').append('<style>#go_top{position:fixed; width:19px; height:63px; background:url('+imgUrl+') no-repeat; cursor:pointer; display:none;}</style>');
$('body').append('<div id="go_top"></div>');

var obj=$("#go_top");
var flag=false;
var onlyOne=true;
var clearTime=null;
var layoutWidth=990;//设定页面宽度
obj.css("left",Math.floor(($(window).width()-layoutWidth)/2)+layoutWidth+5+"px");
if($.browser.msie && $.browser.version=='6.0'){
	obj.css("position","absolute");
}else{
	obj.css("top",$(window).height()-260+"px");
}
obj.click(function(){
	$(window).scrollTop(0);
});
$(window).scroll(function(){
	if($(window).scrollTop()==0){
	obj.fadeOut();
	flag=true;
}else if(flag==true){
	flag=false;
	obj.fadeIn();
}else if(onlyOne==true){
	obj.fadeIn();
	onlyOne=false;
}
if($.browser.msie && $.browser.version=='6.0'){
obj.css('top',$(window).height()+$(window).scrollTop()-260+'px');
if(clearTime!=null){
	clearTimeout(clearTime);
	obj.css("display","none");
}
if($(window).scrollTop()>0)
	clearTime=setTimeout("$('#go_top').fadeIn('10');",20);
}
});
$(window).resize(function(){
if($.browser.msie && $.browser.version=='6.0'){
	obj.css('top',$(window).height()+$(window).scrollTop()-260+'px');
}
else{
	obj.css("top",$(window).height()-260+"px");
}
var HalfWidth=Math.floor(($(window).width()-layoutWidth)/2);
if(HalfWidth>10)
	obj.css("left",HalfWidth+layoutWidth+5+"px");
});
});