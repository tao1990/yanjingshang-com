window.onload=function(){//ѡ�
	var oUl1	=	document.getElementById('quex_ul1');
	var oLi1	=	oUl1.getElementsByTagName('li');
	var oUl2	= 	document.getElementById('quex_ul2');
	var oLi2	=	oUl2.getElementsByTagName('li');
	
	var i 		=	0;
	
	for(i=0;i<oLi1.length;i++){
		oLi1[i].index = i;
		oLi1[i].onmousemove = function(){
			for(i=0;i<oLi1.length;i++){
				oLi1[i].className = '';
				oLi2[i].style.display = 'none';
			}
			this.className = 'quex_currr';
			oLi2[this.index].style.display = 'block';
		}
	}	
}