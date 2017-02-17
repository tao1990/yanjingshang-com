/* =======================================================================================================================
 * 商城大型活动页面脚本 【20120817】【Author:yijiangwen】【同步TIME:20120820】
 * =======================================================================================================================
 */
$(document).ready(function(){		 
	 $("#float_cart").smartFloat();//浮动
            
	//选好全部商品后，全部商品加入购物车【功能】
	$(".add_to_cart").click(function(){
		
		var ch_goods_num = $("#ch_goods_num").val();//已选商品数		
		if(ch_goods_num==2)
		{		
			var goods_id1 = $("#cart_goods_1").val();//选择商品
			var goods_id2 = $("#cart_goods_2").val();
			var ds1 = $("#add_good_ds1").val();      //眼镜度数
			var ds2 = $("#add_good_ds2").val();			

			//商品加入购物车【ajax功能】
			if(goods_id1!='' && goods_id2!='')
			{
				$.ajax({
						type:'post',
						url: 'ajax_step.php?act=add_at_to_cart',	
						data:'&goods_id1='+goods_id1+'&ds1='+ds1+'&goods_id2='+goods_id2+'&ds2='+ds2+'&m='+Math.random(),		
						cache:false,
						success:
						function(da)
						{
							if(da == 'ok')
							{
								window.location.href = 'flow.html';
							}																					
						}
				});
			}
			else
			{
				alert('ˇˍˇ 由于网络原因导致操作失败，请稍后再试或联系易视客服。');
			}			
		}
		else if(ch_goods_num==1)
		{
			alert('^_^ 还需再选一副眼镜，即可加入购物车！');
		}
		else
		{
			alert('^_^ 请选择好2副眼镜后，才可加入购物车！');
		}		
	});	
});


//=============================================================================【函数】=============================================================================//

//随心配商品加入临时购物车【功能】
function add_this_goods(goods_id, athis)
{
	var zs = $('#zs_'+goods_id).val();
	var ys = $('#ys_'+goods_id).val();
	var ch_goods_num = $("#ch_goods_num").val();//已选商品数
	var gg_pan = $(athis).parent('li').next('li');//公告面板
	
	if(ch_goods_num==2)
	{		
		gg_pan.text('^_^ 您已选好2副眼镜，可以放入购物车了！').fadeIn(200).delay(1600).fadeOut(1200);
		return ;
	}
	
	if(zs=='' && ys=='')
	{
		gg_pan.text('^_^ 亲，您忘了选眼镜度数了！').fadeIn(200).delay(1200).fadeOut(1200);
	}
	else
	{
		if(zs=='')
		{
			gg_pan.text('^_^ 请选择左眼度数！').fadeIn(200).delay(1200).fadeOut(1200);
		}
		else if(ys=='')
		{
			gg_pan.text('^_^ 请选择右眼度数！').fadeIn(200).delay(1200).fadeOut(1200);
		}
		else
		{	
			if(ch_goods_num<2){ch_goods_num = ch_goods_num*1+1;}
			$("#ch_goods_num").val(ch_goods_num);	
				
			//1.更新临时购物车中商品信息【界面】
			if(ch_goods_num==1)
			{
				$(".add_t").html('已选<span class="f_b_red">1</span>副，还需再选<span class="f_b_red">1</span>副');				
			}
			else if(ch_goods_num==2)
			{
				$(".add_t").html('已选<span class="f_b_red">2</span>副，可以加入购物车了！');
				$(".add_to_cart a").removeClass('add_bt1').addClass('add_bt2');				
			}		
			
			var ds = zs+','+ys;//度数字符串
		
			//2.用ajax获取及时的商品信息
			$.ajax({
				   type: "post",
				   url:  "ajax_step.php?act=get_at_goods_info",
				   data: "&goods_id="+goods_id+'&m='+Math.random(),
				   cache:false,
				   success:function(da)
				   {
				   		da = eval('('+da+')'); 		
				   		gg_pan.text('^_^ 您已成功选择商品!').fadeIn(100).delay(1200).fadeOut(800);
				   		if(ch_goods_num==1)
						{
							//更新临时购物车中第一个位置的商品信息
							var app_html = $('<ul><li class="add_li1"><a href="javascript:;" title="'+da.goods_name+'">'+da.goods_name+'</a></li>'+
							'<li>数量:<span class="f_b_red"> 1 </span>副</li>'+
							"<li class='add_li2'>易视价<span class='f_red'>￥"+da.goods_price+"</span></li>"+
							'<li><a href="javascript:remove_at_goods('+da.goods_id+', 1)">移除</a></li></ul>');
							
							$("#add_goods1 .add_img").find('img').attr('src', da.goods_img).attr('alt', da.goods_name);							
							$("#add_goods1 .add_txt").find('ul').html(app_html);
							
							//添加隐藏表单 保存数据
							$("#add_goods1").append("<input type='hidden' id='cart_goods_1' value='"+da.goods_id+"'/><input type='hidden' id='add_good_ds1' value='"+ds+"'/>");
						}
						else if(ch_goods_num==2)
						{							
							//更新临时购物车中第二个位置的商品信息
							var app_html = $('<ul><li class="add_li1"><a href="javascript:;" title="'+da.goods_name+'">'+da.goods_name+'</a></li>'+
							'<li>数量:<span class="f_b_red"> 1 </span>副</li>'+
							"<li class='add_li2'>易视价<span class='f_red'>￥"+da.goods_price+"</span></li>"+
							'<li><a href="javascript:remove_at_goods('+da.goods_id+', 2)">移除</a></li></ul>');							
							
							$("#add_goods2 .add_img").find('img').attr('src', da.goods_img).attr('alt', da.goods_name);							
							$("#add_goods2 .add_txt").find('ul').html(app_html);
							$("#add_goods2").append("<input type='hidden' id='cart_goods_2' value='"+da.goods_id+"'/><input type='hidden' id='add_good_ds2' value='"+ds+"'/>");	
						}
						else
						{
							//TODO
						}						
				   }
			});	
		}
	}
}

//移除加入临时购物车的商品【功能】
//chg：位置代码。
function remove_at_goods(goods_id, chg)
{
	var ch_goods_num = $("#ch_goods_num").val();//已选商品数

	if(ch_goods_num==1)
	{
		if(chg==1 || chg==2)
		{
			$("#add_goods1 .add_img").find('img').attr('src', 'themes/default/images/active/20120707/chg1.jpg').attr('alt', '请从左侧选择商品');
			$("#add_goods1 .add_txt").find('ul').html('<ul><li>请从左侧选商品</li></ul>');	
			$("#ch_goods_num").val(0);	
			$(".add_t").html('您还未选择商品');	
			//清空表单数据
			$("#cart_goods_1").remove();
			$("#add_good_ds1").remove();	
			$("#cart_goods_2").remove();
			$("#add_good_ds2").remove();					
		}
		else
		{
			return;
		}		
	}	
	else if(ch_goods_num==2)
	{
		$("#ch_goods_num").val(1);
		$(".add_t").html('已选<span class="f_b_red">1</span>副，还需再选<span class="f_b_red">1</span>副');
		$(".add_to_cart a").removeClass('add_bt2').addClass('add_bt1');			
	
		if(chg==1)//位置1的商品删除
		{
			var tb2 = $("#add_goods2").children('dl').first().detach();
			var ad1 = $("#add_goods1");			
			ad1.children('dl').first().remove();			
			ad1.append(tb2);			
			$("#cart_goods_1").val($("#cart_goods_2").val());//变更表单数据
			$("#add_good_ds1").val($("#add_good_ds2").val());
			
			//更新删除方法位置
			var s_html = '<a href="javascript:remove_at_goods('+$("#cart_goods_2").val()+', 1)">移除</a>';
			$("#add_goods1 .add_txt").find('ul').children('li').eq(3).children('a').html(s_html);			
			
			$("#add_goods2").append('<dl class="add_dl"><dd class="add_img"><a href="javascript:;">'+
			'<img src="themes/default/images/active/20120707/chg2.jpg" width="78" height="78" alt="请从左侧选择商品"/></a></dd>'+
			'<dd class="add_txt"><ul><li>请从左侧选商品</li></ul></dd></dl>');
						
			$("#cart_goods_2").remove();
			$("#add_good_ds2").remove();						
		}
		else if(chg==2)
		{
			$("#add_goods2 .add_img").find('img').attr('src', 'themes/default/images/active/20120707/chg2.jpg').attr('alt', '请从左侧选择商品');
			$("#add_goods2 .add_txt").find('ul').html('<ul><li>请从左侧选商品</li></ul>');	
			$("#cart_goods_2").remove();
			$("#add_good_ds2").remove();						
		}
		else
		{
			//TODO
		}	
	}
}