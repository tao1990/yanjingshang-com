/* ======================================================================================================================
 * js������js�ļ���ȡ����common.js��author:yijiangwen����TIME:2012/9/24 15:24��
 * ======================================================================================================================
 * pre-loaded yijq.js
 */
 
//document loading. 

	 
//document loaded.
$(document).ready(function(){

	/*----------------------------------------------------------------��ҳͷ�ű���----------------------------------------------------------------*/
	//yi:�˵���
	$("#nav > li:not(:first)").hover(
		function(){			
			$(this).addClass("nav_on nav_bg"+$(this).index()).children("div").show();
		},
		function(){			
			$(this).removeClass("nav_on nav_bg"+$(this).index()).children("div").hide();
		}
	);	
	/*----------------------------------------------------------------��ҳͷ�ű�end��-------------------------------------------------------------*/

});

/* ----------------------------------------------------------------------------------------------------------------------
 * ���� yi:ҳͷ�û���¼���л�
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
 * ���� yi:ҳͷ�����ղؼ�
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
 * yi:�û�δ��¼��ʾ
 * ----------------------------------------------------------------------------------------------------------------------
 */
function no_login_msg(msg)
{
	if(msg == '')
	{
		msg = '^_^ ����δ��¼�����ȵ�¼��';
	}
	alert(msg);
    location.href = 'user.html';
}