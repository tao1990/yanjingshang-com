// JavaScript Document
function show(n){
if(n){
document.getElementById("mshow1").style.display="none";
document.getElementById("mshow2").style.display="none";
document.getElementById("mshow3").style.display="none";

document.getElementById("menuover1").className="color_qianfei";
document.getElementById("menuover2").className="color_qianfei";
document.getElementById("menuover3").className="color_qianfei";


document.getElementById("picmbj1").style.background="url('themes/default/images/defaulbg.jpg')";
document.getElementById("picmbj2").style.background="url('themes/default/images/defaulbg.jpg')";
document.getElementById("picmbj3").style.background="url('themes/default/images/rightout.jpg')";
document.getElementById("picmbj"+n).style.background="url('themes/default/images/picover.jpg')";


if(n==3)document.getElementById("picmbj3").style.background="url('themes/default/images/rightoverd.jpg')";

document.getElementById("mshow"+n).style.display="";
document.getElementById("menuover"+n).className="color_zhhei";


}

}

/////////////////////////
function shows(n){
if(n){
document.getElementById("mshows1").style.display="none";
document.getElementById("mshows2").style.display="none";
document.getElementById("mshows3").style.display="none";

document.getElementById("menuovers1").className="color_qianfei";
document.getElementById("menuovers2").className="color_qianfei";
document.getElementById("menuovers3").className="color_qianfei";


document.getElementById("picmbjs1").style.background="url('themes/default/images/defaulbg.jpg')";
document.getElementById("picmbjs2").style.background="url('themes/default/images/defaulbg.jpg')";
document.getElementById("picmbjs3").style.background="url('themes/default/images/rightout.jpg')";
document.getElementById("picmbjs"+n).style.background="url('themes/default/images/picover.jpg')";


if(n==3)document.getElementById("picmbjs3").style.background="url('themes/default/images/rightoverd.jpg')";

document.getElementById("mshows"+n).style.display="";
document.getElementById("menuovers"+n).className="color_zhhei";

}
}

function showtype(n){
	
if(n){
document.getElementById("lefttopmain2").style.display="none";
document.getElementById("lefttopmain1").style.display="none";

document.getElementById("lefttop1").style.background="url('themes/default/images/index_r28_c1.jpg')";
document.getElementById("lefttop2").style.background="url('themes/default/images/index_r28_c27.jpg')";

document.getElementById("lefttopmain"+n).style.display="";
if(n==2){
document.getElementById("lefttop1").style.background="url('themes/default/images/index_r28_c1.jpg')";
document.getElementById("lefttop2").style.background="url('themes/default/images/index_r28_c27.jpg')";

	}
	
	if(n==1){
document.getElementById("lefttop1").style.background="url('themes/default/images/overpp.jpg')";
document.getElementById("lefttop2").style.background="url('themes/default/images/outcs.jpg')";

	}



}
	}
	
	
function showclass(n){
	
if(n){
document.getElementById("lefttopmain1").style.display="none";
document.getElementById("lefttopmain2").style.display="none";

document.getElementById("m_l_t_l").style.background="url('themes/default/images/typeleft.jpg')";
document.getElementById("m_l_t_r").style.background="url('themes/default/images/typeright.jpg')";

document.getElementById("lefttopmain"+n).style.display="";
if(n==2){
document.getElementById("m_l_t_l").style.background="url('themes/default/images/typeleft.jpg')";
document.getElementById("m_l_t_r").style.background="url('themes/default/images/typeright.jpg')";

	}
	
	if(n==1){
document.getElementById("m_l_t_l").style.background="url('themes/default/images/classtypeover.jpg')";
document.getElementById("m_l_t_r").style.background="url('themes/default/images/typeleft.jpg')";

	}

}
	}
	
function showmenu(){

	document.getElementById("hide").style.display="block";

	}



function hidemenu(){

	document.getElementById("hide").style.display="none";

	}


function detail(n){
	
	if(n){
document.getElementById("detail1").style.display="none";
document.getElementById("detail2").style.display="none";
document.getElementById("detail3").style.display="none";
document.getElementById("detail4").style.display="none";
document.getElementById("detail5").style.display="none";


document.getElementById("detail"+n).style.display="";
//document.getElementById("menuovers1").className="color_qianfei";
//document.getElementById("menuovers2").className="color_qianfei";


		}
	}
	

