function setTab(num) {
	for (var id=1; id<=5; id++) {
		var select_tab = "zzjs"+id;
		var select_block = "con_zzjs_"+id;
		if (id==num) {
			document.getElementById(select_tab).className = "fhover";
			document.getElementById(select_block).style.display="block";
		} else {
			document.getElementById(select_block).style.display="none";
			document.getElementById(select_tab).className = "recommend_top_li";
		}
	}
}