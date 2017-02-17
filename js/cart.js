//页面头部购物车下拉菜单

//显示、隐藏购物车
var show_times = 0;
function show_cart() {
	if (show_times == 0) {
		//初次读取
		$(document).ready(function() {
			$.ajax({
				type: 'POST',
				url: 'ajax_step.php?act=cart_info',
				success:function(d){
					$("#head_cart2").html(d);
				},
				error:function(d){
					alert('error:'+d.toSource());
				} 
			});
		});
		show_times = 1;
	}
	document.getElementById("head_cart").style.display='block';
	document.getElementById("head_cart2").style.display='block';
}

function hidden_cart() {
	document.getElementById("head_cart").style.display='none';
	document.getElementById("head_cart2").style.display='none';
}


//删除头部导航的购物车商品
var total_head_rec_count = 0; //购物车列表条数(非商品数量)
function head_drop_goods(rec_id, total_count, goods_num, goods_money) {
	if (total_head_rec_count == 0) {
		total_head_rec_count = total_count; //第一次删除，将参数赋值给变量，之后删除就不再赋值了
	}
	//alert(total_head_rec_count);
	
	if (rec_id > 0) {
		//删除表中数据
		$(document).ready(function() {
			$.ajax({
				type: 'POST',
				url: 'flow.php?step=drop_head_cart_goods&id=' + rec_id,
				success:function(d){
					//
				},
				error:function(d){
					alert('error:'+d.toSource());
				} 
			});
		});
		
		total_head_rec_count = total_head_rec_count - 1; //商品列表条数减1
		//alert(total_head_rec_count);
		
		document.getElementById("head_cart2_goods_" + rec_id).style.display='none'; //不显示本条信息
		
		//减去商品总数和总金额
		var show_goods_num = parseInt(document.getElementById("head_cart_goods_num").innerHTML);
		var show_goods_money = parseFloat(document.getElementById("head_cart_goods_money").innerHTML);
		document.getElementById("head_cart_num").innerHTML = show_goods_num - goods_num;
		document.getElementById("head_cart_num2").innerHTML = show_goods_num - goods_num;
		document.getElementById("head_cart_goods_num").innerHTML = show_goods_num - goods_num;
		document.getElementById("head_cart_goods_money").innerHTML = (show_goods_money - goods_money).toFixed(2); //保留2位小数
		
		if (total_head_rec_count == 0) {
			//已全部清空
			document.getElementById("head_cart").style.display = "none";
			document.getElementById("head_cart2").style.display = "none";
			document.getElementById("head_cart2").innerHTML = "";
			show_times = 0;
			//show_cart();
			return false;
		}
		
		//改变容器高度
		if (total_head_rec_count >= 3) {
			//信息列表的高度不变
		} else if (total_head_rec_count == 0) {
			//已删除所有购物车商品,隐藏下来菜单
			document.getElementById("head_cart").style.display = "none";
			document.getElementById("head_cart2").style.display = "none";
		} else {
			var h = total_head_rec_count * 57 + total_head_rec_count;
			document.getElementById("head_cart2_box").style.height = h + 'px';
		}
	}
}