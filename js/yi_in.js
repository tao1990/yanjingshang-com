/*------------------------------------------所有内页里面的脚本程序 2011-04-06-------------------------------------------------*/

/*---促销活动-选项卡切换-----*/
function change_tab(n){
	if(n){
		document.getElementById("con_tab1").style.display="none";
		document.getElementById("con_tab2").style.display="none";
		document.getElementById("con_tab3").style.display="none";
		document.getElementById("con_tab4").style.display="none";		
		
		document.getElementById("cx_menu1").style.background="url('themes/default/images/cat_bg1.gif') no-repeat 0 4px";	
		document.getElementById("cx_menu2").style.background="url('themes/default/images/cat_bg1.gif') no-repeat 0 4px";
		document.getElementById("cx_menu3").style.background="url('themes/default/images/cat_bg1.gif') no-repeat 0 4px";
		document.getElementById("cx_menu4").style.background="url('themes/default/images/cat_bg1.gif') no-repeat 0 4px";		
		document.getElementById("cx_menu"+n).style.background="url('themes/default/images/cat_bg2.gif') no-repeat 0 4px";	
		
		document.getElementById("cx_menu1").className="cx_menu";
		document.getElementById("cx_menu2").className="cx_menu";
		document.getElementById("cx_menu3").className="cx_menu";
		document.getElementById("cx_menu4").className="cx_menu";		
				
		document.getElementById("con_tab"+n).style.display="";	
	}	
}

