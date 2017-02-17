/*-----------------------------------------------------------------------------changediv-----------------------------------------------------------*/

//改变有子的故事的页面--n:当前的页码--
function story(n){
	if(n>=1 && n<=4){
		for(var i=1; i<=4; i++){
			document.getElementById('story'+i).style.display='none';
		}
		document.getElementById('story'+n).style.display='';	
	}else{
		return;
	}
}