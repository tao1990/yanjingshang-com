/* ======================================================================================================================
 * js：公共js文件，取代旧common.js【author:yijiangwen】【TIME:2012/9/24 15:24】
 * ======================================================================================================================
 * pre-loaded yijq.js
 */
 
//document loading. 

	 
//document loaded.
$(document).ready(function(){

	/*----------------------------------------------------------------【页头脚本】----------------------------------------------------------------*/
	//yi:菜单条
	$("#nav > li:not(:first)").hover(
		function(){			
			$(this).addClass("nav_on nav_bg"+$(this).index()).children("div").show();
		},
		function(){			
			$(this).removeClass("nav_on nav_bg"+$(this).index()).children("div").hide();
		}
	);	
	/*----------------------------------------------------------------【页头脚本end】-------------------------------------------------------------*/

});

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:页头用户登录行切换
 * ----------------------------------------------------------------------------------------------------------------------
 */
function pan_hide(id)
{
	document.getElementById(id).style.display='none';
}
function pan_show(id)
{
	document.getElementById(id).style.display='block';
}

function pan_hide_h2()
{
	document.getElementById("help_tip_pan").style.display='none';
	document.getElementById("help_tip_pan2").style.display='none';
}
function pan_show_h2()
{
	document.getElementById("help_tip_pan").style.display='block';
	document.getElementById("help_tip_pan2").style.display='block';
}
/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:页头加入收藏夹
 * ----------------------------------------------------------------------------------------------------------------------
 */
function add_book_mark(txt, url){
    if((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function")){
        window.sidebar.addPanel(txt, url, "");
    }else{
        window.external.AddFavorite(url, txt);
    }
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:用户未登录提示
 * ----------------------------------------------------------------------------------------------------------------------
 */
function no_login_msg(msg)
{
	if(msg == '')
	{
		msg = '^_^ 您还未登录，请先登录！';
	}
	alert(msg);
    location.href = 'user.html';
}