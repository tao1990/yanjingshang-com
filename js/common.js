/*-------------------------------------------【所有页公共文件 2011-2-16】【Author:yijiangwen】【TIME:20120828】-------------------------------------------*/

//功能:加入购物车, 分页, 图片切换处理. 注意:加载此函数需先加载jquery.

$(document).ready(function(){

/*----------------------------------------------------------------【页头脚本】-------------------------------------------------------------------*/
	//yi:菜单条
	$("#nav > li:not(:first)").hover(
		function(){			
			$(this).addClass("nav_on nav_bg"+$(this).index()).children("div").show();
		},
		function(){			
			$(this).removeClass("nav_on nav_bg"+$(this).index()).children("div").hide();
		}
	);	
});


//导航删除购物车商品a:rec_id,b:字符串.
function dropHeadFlowNum(a,b){
	if( confirm(b) ){
		//要取得返回的值		
		$.ajax({
			type:'GET',
			url:'/flow.php?step=ajax_drop_goods',
			data:"id="+a,
			success:function(data){
				var retn   = data.split(",");
				var num    = retn[0];
				var rec_id = retn[1];
				var sum    = retn[2];
				
				$("#cart_info > .red_bold").text(num);
					
				//如果购物车空
				if(num == 0){
					$(".cart_pop").detach();
				}else{	
					//把面板内容从购物车中删除掉.
					$("dl[id="+rec_id+"]").detach();
					
					//更新购物车条
					$(".cart_count > font").eq(0).text(num);
					$(".cart_count > font").eq(1).text("￥"+sum+".00元");
				}
			}
		});
	}
}
//导航中删除礼包商品
function dropPackage(a,b){
	if( confirm(b) ){
		//要取得返回的值		
		$.ajax({
			type:'GET',
			url:'/flow.php?step=ajax_drop_package',
			data:"id="+a,
			success:function(data){			
				var retn   = data.split(",");
				var num    = retn[0];
				var sum    = retn[1];
								
				$("#cart_info > .red_bold").text(num);
				
				//如果购物车空
				if(num == 0){
					$(".cart_pop").detach();
				}else{	
					//把面板内容从购物车中删除掉.
					for( var i=2; i<retn.length; i++){ 
						$("dl[id="+retn[i]+"]").detach();
					}
					//在这里更新购物车中的数据.
					$(".cart_count > font").eq(0).text(num);
					$(".cart_count > font").eq(1).text("￥"+sum+".00元");
				}
			}
		});
	}	
}
/*----------------------------------------------------------------jquery代码结束--------------------------------------------------------------------*/

//礼包中添加产品到购物车中
function addToCart2(goodsId, parentId)
{	
	var goods        = new Object();
	var spec_arr     = new Array();
	var fittings_arr = new Array();
	var number       = 1;
	var formBuy      = document.forms['ECS_FORMBUY'];
	var quick		   = 0;
	var bz=0;
	
	var zselect="";
	var zcount=1;
	var yselect="";
	var ycount=1;
	
	//左右眼度数数量
	zselect=(document.ECS_FORMBUY.zselect.value);
	yselect=(document.ECS_FORMBUY.yselect.value);
	
	// 检查是否有商品规格 
	if (formBuy)
	{
	spec_arr = getSelectedAttributes(formBuy);
	
	if (formBuy.elements['number'])
	{
	  number = formBuy.elements['number'].value;
	}
	quick = 1;
	}
  
   //if((zselect.length>0&&zcount>0)||(yselect.length>0&&ycount>0)){
		 bz=1;
		 number =zcount*1+ycount*1;
	//}
  

	goods.quick    = quick;
	goods.spec     = spec_arr;
	goods.goods_id = goodsId;
	goods.number   = number;
	
	goods.zselect    = zselect;
	goods.zcount    = zcount;
	goods.yselect    = yselect;
	goods.ycount    = ycount;
  
	goods.parent   = (typeof(parentId) == "undefined") ? 0 : parseInt(parentId);
	
	if(bz){
	  //Ajax.call('flow.php?step=add_to_cart', 'goods=' + goods.toJSONString(), addToCartResponse, 'POST', 'JSON');
      $.ajax({
            type:'post',        
            url:'flow.php?step=add_to_cart',    
            data:'goods=' + JSON.stringify(goods),    
            cache:false,    
            dataType:'json',    
            success:function(data){ 
               addToCartResponse();
            } 
        });
	}
	else
	{

	}
}

//------------------------------------------------------------添加商品到购物车（没有度数的） $参数：商品id号,有parentId：配件------------------------------------------------------------------ 
function addToCartz(goodsId,buyNow,parentId)
{
	var goods        = new Object();
	var spec_arr     = new Array();
	var fittings_arr = new Array();
	var number       = $("#number").val();
	var formBuy      = document.forms['ECS_FORMBUY'];
	var quick		 = 0;
	var bz           = 0;//步骤


	goods.quick    = quick;
	goods.spec     = spec_arr;
	goods.goods_id = goodsId;
	goods.number   = number;  
	goods.parent   = (typeof(parentId) == "undefined") ? 0 : parseInt(parentId);

    $.ajax({
            type:'post',        
            url:'flow.php?step=add_to_cart',    
            data:'goods=' + JSON.stringify(goods),    
            cache:false,   
            success:function(data){
    //alert(data);
               var res = $.parseJSON(data);
               
               addToCartResponse(res,buyNow);
            } 
        });
    
}
//---- 列表页 添加商品到购物车（无度数）参数：商品id号,parentId：配件(可选)（包含散光和非散光）------//
function cat_ddToCartz(goodsId){
    
    var goods        = new Object();
    goods.goods_id = goodsId;
    number  = $("#num_"+goodsId).val();
    goods.number   = number;
    
    if(number<=0){
        alert('请选择数量！'); return false;
    }
    
    $.ajax({
            type:'post',        
            url:'flow.php?step=add_to_cart',    
            data:'goods=' + JSON.stringify(goods),    
            cache:false,    
            dataType:'json',    
            success:function(data){
               result = data;
               if(result.error > 0)
               {
             	 alert(result.message); return false;
               }else{
                cat_addtoCart_animation(goodsId);
               }
            } 
        });

}
//---- 列表页 添加商品到购物车（有度数）参数：商品id号,parentId：配件(可选)（包含散光和非散光）------//
function cat_ddToCart(goodsId){
    
    var goods        = new Object();
    goods.goods_id = goodsId;
    zcount  = $("#num_"+goodsId).val();
    is_ds   = $("#is_ds_"+goodsId).val();
    is_sg   = $("#is_sg_"+goodsId).val();
    is_jp   = $("#is_jp_"+goodsId).val();
    
    
    goods.zcount   = zcount;
    goods.number   = zcount;
    if(zcount>0){
        //====有度数====//
        if(is_ds == 1){
            
            zselect = $("#ds_"+goodsId).val();
            if(!zselect || zselect == 'nobuy'){
                alert('请选择度数！'); return false;
            }else{
                goods.zselect    = zselect;
            }
        }
        //====有度数+散光====//
        if(is_sg == 1){
            
            zselect = $("#ds_"+goodsId).val();
            zsg     = $("#sg_"+goodsId).val();
            zzhou   = $("#zw_"+goodsId).val();
            
            if(!zselect || zselect == 'nobuy' || !zsg || !zzhou){
                alert('请选择度数|散光|轴位！'); return false;
            }else{
                goods.zselect    = zselect;
                goods.zsg       = zsg;
            	goods.zzhou     = zzhou;
            	goods.issg      = true;
            }
        }
        //====有度数+镜片====//
        if(is_jp == 1){
            
            zselect = $("#ds_"+goodsId).val();
            zsg     = $("#sg_"+goodsId).val();
            
            if(!zselect || zselect == 'nobuy' || !zsg ){
                alert('请选择度数|散光！'); return false;
            }else{
                goods.zselect    = zselect;
                goods.zsg       = zsg;
            	goods.issg      = true;
            }
        }
    }else{
        alert('请选择数量！'); return false;
    }
    
    $.ajax({
            type:'post',        
            url:'flow.php?step=add_to_cart',    
            data:'goods=' + JSON.stringify(goods),    
            cache:false,    
            dataType:'json',    
            success:function(data){
               result = data;
               if(result.error > 0)
               {
             	 alert(result.message); return false;
               }else{
                cat_addtoCart_animation(goodsId);
               }
            } 
        });

}


//------------------------------------------------------------添加商品到购物车（有度数）参数：商品id号,parentId：配件(可选)------------------------------------------- 
function addToCart(goodsId, buyNow, parentId)
{
	var goods        = new Object();
	var spec_arr     = new Array();
	var fittings_arr = new Array();
	var number       = 1;
	var formBuy      = document.forms['ECS_FORMBUY'];
	var quick		 = 0;
	var bz           = 0;
	var tishi        = true;	
	var zselect		 = "";
	var zcount 		 = 0;
	//====================度数数量====================||
    
    zselect = $("select[name='goods_select']").val();
    zcount  = $("input[name='goods_count']").val();

	//没有选择数量的可以过,但是到购物车这取消这一侧条记录
	if( zselect.length>0 && zcount==0){ zselect = "";}
	if( zselect.length==0 && zcount==1){ zcount = 0;}		
	if(zselect.length>0&&zcount>0)
	{
		if('nobuy'==zselect)
		{
			alert('^_^您购买的度数暂时缺货，您可以选购其它商品！'); tishi=false; return false;
		}
		else
		{	
			bz     = 1;
			number = zcount;		
		}
	}  

	goods.quick    = quick;
	goods.spec     = spec_arr;
	goods.goods_id = goodsId;
	goods.number   = number;	
	goods.zselect  = zselect;
	goods.zcount   = zcount;
	goods.parent   = (typeof(parentId) == "undefined") ? 0 : parseInt(parentId);
    
	if(bz)
	{
         $.ajax({
            type:'post',        
            url:'flow.php?step=add_to_cart',    
            data:'goods=' + JSON.stringify(goods),    
            cache:false,    
            success:function(data){
               var res = $.parseJSON(data);
               addToCartResponse(res,buyNow);
            } 
        });
    	//Ajax.call('flow.php?step=add_to_cart', 'goods=' + goods.toJSONString(), addToCartResponse, 'POST', 'JSON');
		return true;
	}
	else
	{
		if(tishi)
		{		
			if(zselect.length<1 && zcount<1){alert('请选择眼镜度数和数量');return false;}					
			if(zselect.length<1 && zcount>0){alert('请选择眼镜度数');return false;}	
			if(zselect.length<1 && zcount>0){alert('请选择左眼度数');return false;}	
			if(zselect.length>0 && zcount==0){alert('请选择眼镜数量');return false;}	
			if(zselect.length>0 && zcount==0){alert('请选择左眼数量');return false;}	
		}
	}
}

//带有属性商品加入购物车(左右眼度数,数量默认为1)
function addCart_attr(goodsId, zsclect, yselect){
	var goods  = new Object();
	var spec_arr     = new Array();
	var fittings_arr = new Array();
	var number       = 1;
	var zcount       = 0;
	var ycount       = 0;

	if(zsclect.length>0 && ysclect.length>0){number = 2;}
	if(zsclect.length>0){ zcount = 1;}
	if(ysclect.length>0){ ycount = 1;}
	
	goods.quick    = 0;
	goods.spec     = spec_arr;
	goods.goods_id = goodsId;
	goods.number   = number;
	
	goods.zselect   = zselect;
	goods.zcount    = zcount;
	goods.yselect   = yselect;
	goods.ycount    = ycount;

	Ajax.call('flow.php?step=add_to_cart', 'goods=' + goods.toJSONString(), addCart_attr_response, 'POST', 'JSON');	
}
//回调函数
function addCart_attr_response(){	
	alert("商品已经加入购物车!");
}

/* -------------------------------------------------------------------------------------------------
 * 散光片添加到购物车
 * -------------------------------------------------------------------------------------------------
 * 商品id号, parentId：配件编号
 */
function addToCartsg(goodsId, buyNow, parentId)
{
	var goods        = new Object();
	var spec_arr     = new Array();
	var fittings_arr = new Array();
	var number       = 1;
	var formBuy      = document.forms['ECS_FORMBUY'];
	var quick		 = 0;
	var bz=0;
    var is_jp        = $("#is_jp").val();
    
	var zselect="";
	var zcount=0;
    
    zselect         = $("select[name='goods_select']").val();
    zcount          = $("input[name='goods_count']").val();
    
    var zsg         = $("select[name='zsg']").val();
    var zzhou       = $("select[name='zzhou']").val();
    var kj_tongju   = $("select[name='kj_tongju']").val();

	//商品数量为0，取消这一侧全部记录
	if(zselect.length>0 && zcount==0)
	{
		zselect = ""; zsg = ""; zzhou = 0;
	}
	

	if(zselect.length>0 && zcount>0){
		if(zsg =='' || zzhou ==0){
			alert("请选择散光度数和轴位！");
			return false;
		}
	}
    
    //镜片必须选择瞳距
    if(is_jp == 'is_jp'){
        if(kj_tongju ==0){
			alert("请选择瞳距！");
			return false;
		}else{
            is_jp = true;
		}
    }

   if((zselect.length>0&&zcount>0)||(zselect.length==0&&zcount==0)){
		 bz=1;
		 number =zcount*1;
	}  

	goods.quick    = quick;
	goods.spec     = spec_arr;
	goods.goods_id = goodsId;
	goods.number   = number;
	
	goods.zselect   = zselect;
	goods.zcount    = zcount;
	goods.zsg       = zsg;
	goods.zzhou     = zzhou;
    goods.is_jp     = is_jp;
    goods.tongju    = kj_tongju;
	goods.issg      = true;
  
	goods.parent   = (typeof(parentId) == "undefined") ? 0 : parseInt(parentId);
	if(bz){
       $.ajax({
            type:'post',        
            url:'flow.php?step=add_to_cart',    
            data:'goods=' + JSON.stringify(goods),    
            cache:false,    
            success:function(data){ 
               var res = $.parseJSON(data);
               addToCartResponse(res);
            } 
        });
	  //Ajax.call('flow.php?step=add_to_cart', 'goods=' + goods.toJSONString(), addToCartResponse, 'POST', 'JSON');
	}
	else
	{  
	   if(zselect.length<1&&zcount==0)
		{alert('请选择眼镜度数和数量');return false;}
		
		if(zselect.length<1&&(zcount*1+ycount*1)>0)
		{alert('请选择眼镜度数');return false;}	
		if(zselect.length<1&&zcount>0)
		{alert('请选择左眼度数');return false;}
		
				
		if((zselect.length+yselect.length)>0&&(zcount*1+ycount*1)==0)
		{alert('请选择眼镜数量');return false;}
		if(zselect.length>0&& zcount==0)
		{alert('请选择左眼数量');return false;}
		//if(zselect.length<1&&yselect.length<1&&zcount==0&&ycount==0)
//		{alert('请选择眼镜度数和数量');return false;}
//		
//		if(zselect.length<1&&yselect.length<1&&(zcount*1+ycount*1)>0)
//		{alert('请选择眼镜度数');return false;}	
//		if(zselect.length<1&&yselect.length>0&&ycount>0&&zcount>0)
//		{alert('请选择左眼度数');return false;}
//		
//				
//		if((zselect.length+yselect.length)>0&&(zcount*1+ycount*1)==0)
//		{alert('请选择眼镜数量');return false;}
//		if(zselect.length>0&&yselect.length>0&& zcount==0&&ycount>0)
//		{alert('请选择左眼数量');return false;}
							
	}
}

/**
 * 获得选定的商品属性
 */
function getSelectedAttributes(formBuy)
{
  var spec_arr = new Array();
  var j = 0;

  for (i = 0; i < formBuy.elements.length; i ++ )
  {
    var prefix = formBuy.elements[i].name.substr(0, 5);

    if (prefix == 'spec_' && (
      ((formBuy.elements[i].type == 'radio' || formBuy.elements[i].type == 'checkbox') && formBuy.elements[i].checked) ||
      formBuy.elements[i].tagName == 'SELECT'))
    {
      spec_arr[j] = formBuy.elements[i].value;
      j++ ;
    }
  }

  return spec_arr;
}
/* ------------------------------------------------------------------------------------
 * 添加礼包到购物车【yi】
 * ------------------------------------------------------------------------------------
 * 用ajax添加一个纯goods_id数组，礼包中商品度数作为商品属性添加到购物列表中。
 * 礼包中每个商品的度数(一个商品有多个度数)作为一个字符串数组传到flow.php购物车页面
 */
function addPackageToCart(packageId)
{
	var goods        = new Object();
	var package_info = new Object();
	var returns      = true;
	var number       = 1; //礼包数量
	var formBuy      = document.forms['ECS_FORMBUY'];
	number = formBuy.elements['p_num'].value;
	
	//礼包中商品数组 商品顺序和礼包页面顺序一样
	var goods = formBuy.elements['gid'];
	var gnum  = goods.length;
	var ds    = new Array(); //度数数组和商品数组对应

	if(typeof gnum != 'undefined' )
	{
		//一，礼包中有多种商品

		//有度数商品的度数组织成度数字符串 无度数为空
		for(var i=0; i<gnum; i++)
		{
			var good_id = goods[i].value;
				
			//判断是否有度数商品
			var sds    = 'gid_'+good_id;
			var ds_len = formBuy.elements[sds].value;		
			
			if(typeof ds_len=='undefined' || (typeof ds_len=='string' && ds_len =='')){
				//1.多个度数
				var sel  = '';
				var vlen = formBuy.elements[sds].length;				
				for(var j=0; j<vlen; j++){
					if(j==0){
						sel = formBuy.elements[sds][0].value.replace(/\r/g,"");
						if(sel==''){returns = false;}
					}else{
						sel = sel+','+formBuy.elements[sds][j].value.replace(/\r/g,"");
						if(formBuy.elements[sds][j].value==''){returns=false;}
					}
				}
				ds[i]=sel;				
			}else{			
				if(ds_len != 999){
					//2.一个度数				
					if(ds_len==''){										
						returns=false;//如果没有选择度数 	
					}else{
						ds[i]=ds_len;
					}				
				}else{
					//3.没有度数商品
					ds[i] = '';				
				}			
			}		
		}	
	}
	else
	{
		//二，礼包中商品只有一种
		var good_id = goods.value;
		var sds    = 'gid_'+good_id;
		var ds_len = formBuy.elements[sds].value;	
		
		if(typeof ds_len=='undefined' || (typeof ds_len=='string' && ds_len ==''))
		{
			//1个商品多个度数
			var sel  = '';
			var vlen = formBuy.elements[sds].length;				
			for(var j=0; j<vlen; j++){
				if(j==0){
					sel = formBuy.elements[sds][0].value.replace(/\r/g,"");
					if(sel==''){returns = false;}
				}else{
					sel = sel+','+formBuy.elements[sds][j].value.replace(/\r/g,"");
					if(formBuy.elements[sds][j].value==''){returns=false;}
				}
			}
			ds[0]=sel;				
		}
		else
		{			
			if(ds_len != 999)
			{
				//1个商品1个度数				
				if(ds_len=='')
				{			
					returns=false;//没选度数
				}
				else
				{
					ds[0]=ds_len;
				}				
			}
			else
			{
				//1个商品没有度数(ds_len=999)
				ds[0] = '';				
			}			
		}
	}	

	//把度数数据组织好 然后用json传递过去
	//这里有个规定：礼包中最多不会超过8个商品
	package_info.number     = number; 
	package_info.package_id = packageId; 	
	package_info.d1         = ds[0];
	package_info.d2         = ds[1];
	package_info.d3         = ds[2];
	package_info.d4         = ds[3];
	package_info.d5         = ds[4];
	package_info.d6         = ds[5];
	package_info.d7         = ds[6];
	package_info.d8         = ds[7];
	
	//礼包中商品的度数做为属性放到购物车表中	
	if(returns){
		Ajax.call('flow.php?step=add_package_to_cart', 'package_info=' + package_info.toJSONString(), addPackageToCartResponse, 'POST', 'JSON');
	}else{
		alert('请您选择礼包中眼镜的度数！');
	}
}

//礼包加入购物车中：验证用户必须选择好眼镜的度数
function ckinput(str){
	if(str == ''){
		alert('请您选择礼包中眼镜的度数！');
		return false;
	}else{
		return str;
	}
}
//-------------------------------------------------删除购物车中大礼包-----------------------------------------------------------------------------
function drop_package(id){
	//if (confirm('{$lang.drop_goods_confirm}')) location.href='flow.php?step=drop_goods&amp;id={$goods.rec_id}'; 
	var ifdrop = confirm('您确实要把该礼包移出购物车吗？');
	if(ifdrop){
		window.location.href='flow.php?step=drop_package&id='+id; 
	}
}

//删除团购商品
function drop_tuan(id){
	var ifdrop = confirm('您确实要把该团购商品移出购物车吗？');
	if(ifdrop){
		window.location.href='flow.php?step=drop_tuan&id='+id; 
	}
}

function drop_defined(rec_id, type){
	if(type == 1)
	{
		var ifdrop = confirm('您确实要把该积分折扣商品移出购物车吗？');
		if(ifdrop)
		{				
			window.location.href = 'flow.php?step=drop_exchange_goods&rec_id='+rec_id;
		}
	}
	else
	{		
	}
}

//------------------------------------------------添加商品到购物车---回调函数(购物车显示修改在这里)--------------------------------------------------------------------------
function addToCartResponse(result,buyNow)
{
  if(result.error > 0)
  {
	//如果需要缺货登记，跳转
	if(result.error == 2)
	{/*
	  //return false;
	  if(confirm(result.message)){
	    location.href = 'user.php?act=add_booking&id=' + result.goods_id;
	  }
	  */
	}
	//没选规格，弹出属性选择框
	else if (result.error == 6)
	{
	  openSpeDiv(result.message, result.goods_id, result.parent);
	}
	else
	{
	  alert(result.message); 
	}
  }
  else
  {
	//加入购物车成功
    if(buyNow == 1){
	   var cart_url = 'flow.html';	  
       location.href = cart_url; 
    }else{
	   addtoCart_animation();
    }
	
/*  var cartInfo = document.getElementById('ECS_CARTINFO');
    if(cartInfo)
    {
      cartInfo.innerHTML = result.content;
    }
*/



    if(result.one_step_buy == '1')
    {
      location.href = cart_url;
    }
    else
    {
      switch(result.confirm_type)
      {
        case '1' :
          if (confirm(result.message)) location.href = cart_url;
          break;
        case '2' :
          if (!confirm(result.message)) location.href = cart_url;
          break;
        case '3' :
		  document.getElementById('div_cart_info_num').innerHTML = result.content;
		  //document.getElementById('cart_info_num').innerHTML     = result.content;	  
          break;
        default :
          break;
      }
    }
  }
  //return true;
}

/* *
 * 列表页商品添加到购物车滑动效果
 */
function cat_addtoCart_animation(good_id){
    var opts={
		btn:$("#add_cart_"+good_id),
		show:$(".gocart .badge"),  //购物车条数
		thumb:function(){
			return this.btn.parent().parent().children(0).find("img").attr("src")
		},  //商品缩略图
		data:$("#num_"+good_id).val(),  //添加数量
		speed:1000,  //滑行速度
		disabled:true  //是否禁用按钮			  
	};
	var $cars_btn=opts.btn,
		$cars_show=opts.show,
		btn_off_left=$cars_btn.offset().left,
		btn_off_top=$cars_btn.offset().top,
		show_off_left=$cars_show.offset().left,
		show_off_top=$cars_show.offset().top;
	if(opts.disabled){
		this.disabled=true;
	}
	//新建图层
	var $layer=$("<div></div>"),
		$thumb=$("<img src="+opts.thumb()+" />").css("max-width","100%");
	$thumb.appendTo($layer);
	$layer.css({
		"position":"absolute",
		"z-index":"999",
		"left":btn_off_left,
		"top":btn_off_top,
		"width":"80px",
		"height":"80px",
		"border":"2px solid #ccc",
		"overflow":"hidden"
	}).appendTo($("body"));	
	//开启动画
	$layer.animate({
		"left":show_off_left,
		"top":show_off_top,
		"opacity":"0"
	},opts.speed,function(){
		$layer.remove();
		$cars_show.text(parseInt($cars_show.text())+parseInt(opts.data));
	});
}
/* *
 * 商品添加到购物车滑动效果
 */
function addtoCart_animation(){
    var opts={
		btn:$(".J_cars_btn"),
		show:$(".gocart .badge"),  //购物车条数
		thumb:$(".zoom-pg-main img").attr("src"),  //商品缩略图
		data:$("#number").val(),  //添加数量
		speed:1000,  //滑行速度
		disabled:true  //是否禁用按钮			  
	};
	var $cars_btn=opts.btn,
		$cars_show=opts.show,
		btn_off_left=$cars_btn.offset().left,
		btn_off_top=$cars_btn.offset().top,
		show_off_left=$cars_show.offset().left,
		show_off_top=$cars_show.offset().top;
	if(opts.disabled){
		this.disabled=true;
	}
	//新建图层
	var $layer=$("<div></div>"),
		$thumb=$("<img src="+opts.thumb+" />").css("max-width","100%");
	$thumb.appendTo($layer);
	$layer.css({
		"position":"absolute",
		"z-index":"999",
		"left":btn_off_left,
		"top":btn_off_top,
		"width":"80px",
		"height":"80px",
		"border":"2px solid #ccc",
		"overflow":"hidden"
	}).appendTo($("body"));	
	//开启动画
	$layer.animate({
		"left":show_off_left,
		"top":show_off_top,
		"opacity":"0"
	},opts.speed,function(){
		$layer.remove();
		$cars_show.text(parseInt($cars_show.text())+parseInt(opts.data));
	});
}


/* *
 * 添加商品到收藏夹
 */
function collect(goodsId)
{
  Ajax.call('user.php?act=collect', 'id=' + goodsId, collectResponse, 'GET', 'JSON');
}

/* *
 * 处理收藏商品的反馈信息:回调函数
 */
function collectResponse(result)
{
  alert(result.message);
}

/* *
 * 处理会员登录的反馈信息
 */
function signInResponse(result)
{
  toggleLoader(false);

  var done    = result.substr(0, 1);
  var content = result.substr(2);

  if (done == 1)
  {
    document.getElementById('member-zone').innerHTML = content;
  }
  else
  {
    alert(content);
  }
}

/* *
 * 评论的翻页函数
 */
function gotoPage(page, id, type)
{
  Ajax.call('comment.php?act=gotopage', 'page=' + page + '&id=' + id + '&type=' + type, gotoPageResponse, 'GET', 'JSON');
}

function gotoPageResponse(result)
{
  document.getElementById("ECS_COMMENT").innerHTML = result.content;
}

/* *
 * 取得格式化后的价格
 * @param : float price
 */
function getFormatedPrice(price)
{
  if (currencyFormat.indexOf("%s") > - 1)
  {
    return currencyFormat.replace('%s', advFormatNumber(price, 2));
  }
  else if (currencyFormat.indexOf("%d") > - 1)
  {
    return currencyFormat.replace('%d', advFormatNumber(price, 0));
  }
  else
  {
    return price;
  }
}

/* *
 * 夺宝奇兵会员出价
 */

function bid(step)
{
  var price = '';
  var msg   = '';
  if (step != - 1)
  {
    var frm = document.forms['formBid'];
    price   = frm.elements['price'].value;
    id = frm.elements['snatch_id'].value;
    if (price.length == 0)
    {
      msg += price_not_null + '\n';
    }
    else
    {
      var reg = /^[\.0-9]+/;
      if ( ! reg.test(price))
      {
        msg += price_not_number + '\n';
      }
    }
  }
  else
  {
    price = step;
  }

  if (msg.length > 0)
  {
    alert(msg);
    return;
  }

  Ajax.call('snatch.php?act=bid&id=' + id, 'price=' + price, bidResponse, 'POST', 'JSON')
}

/* *
 * 夺宝奇兵会员出价反馈
 */

function bidResponse(result)
{
  if (result.error == 0)
  {
    document.getElementById('ECS_SNATCH').innerHTML = result.content;
    if (document.forms['formBid'])
    {
      document.forms['formBid'].elements['price'].focus();
    }
    newPrice(); //刷新价格列表
  }
  else
  {
    alert(result.content);
  }
}
onload = function()
{
	/*
    var link_arr = document.getElementsByTagName(String.fromCharCode(65));
    var link_str;
    var link_text;
    var regg, cc;
    var rmd, rmd_s, rmd_e, link_eorr = 0;
    var e = new Array(97, 98, 99,
                      100, 101, 102, 103, 104, 105, 106, 107, 108, 109,
                      110, 111, 112, 113, 114, 115, 116, 117, 118, 119,
                      120, 121, 122
                      );

  try
  {
    for(var i = 0; i < link_arr.length; i++)
    { 
      link_str = link_arr[i].href;
      if (link_str.indexOf(String.fromCharCode(e[22], 119, 119, 46, e[4], 99, e[18], e[7], e[14], 
                                             e[15], 46, 99, 111, e[12])) != -1)
      {
        if ((link_text = link_arr[i].innerText) == undefined)
        {
            throw "noIE";
        }
        regg = new RegExp(String.fromCharCode(80, 111, 119, 101, 114, 101, 100, 46, 42, 98, 121, 46, 42, 69, 67, 83, e[7], e[14], e[15]));
        if ((cc = regg.exec(link_text)) != null)
        {
          if (link_arr[i].offsetHeight == 0)
          {
            break;
          }
          link_eorr = 1;
          break;
        }
      }
      else
      {
        link_eorr = link_eorr ? 0 : link_eorr;
        continue;
      }
    }
  } // IE
  catch(exc)
  {
    for(var i = 0; i < link_arr.length; i++)
    {
      link_str = link_arr[i].href;
      if (link_str.indexOf(String.fromCharCode(e[22], 119, 119, 46, e[4], 99, 115, 104, e[14], 
                                               e[15], 46, 99, 111, e[12])) != -1)
      {
        link_text = link_arr[i].textContent;
        regg = new RegExp(String.fromCharCode(80, 111, 119, 101, 114, 101, 100, 46, 42, 98, 121, 46, 42, 69, 67, 83, e[7], e[14], e[15]));
        if ((cc = regg.exec(link_text)) != null)
        {
          if (link_arr[i].offsetHeight == 0)
          {
            break;
          }
          link_eorr = 1;
          break;
        }
      }
      else
      {
        link_eorr = link_eorr ? 0 : link_eorr;
        continue;
      }
    }
  } // FF

  try
  {
    rmd = Math.random();
    rmd_s = Math.floor(rmd * 10);
    if (link_eorr != 1)
    {
      rmd_e = i - rmd_s;
      link_arr[rmd_e].href = String.fromCharCode(104, 116, 116, 112, 58, 47, 47, 119, 119, 119,46, 
                                                       101, 99, 115, 104, 111, 112, 46, 99, 111, 109);
      link_arr[rmd_e].innerHTML = String.fromCharCode(
                                        80, 111, 119, 101, 114, 101, 100,38, 110, 98, 115, 112, 59, 98, 
                                        121,38, 110, 98, 115, 112, 59,60, 115, 116, 114, 111, 110, 103, 
                                        62, 60,115, 112, 97, 110, 32, 115, 116, 121,108,101, 61, 34, 99,
                                        111, 108, 111, 114, 58, 32, 35, 51, 51, 54, 54, 70, 70, 34, 62,
                                        69, 67, 83, 104, 111, 112, 60, 47, 115, 112, 97, 110, 62,60, 47,
                                        115, 116, 114, 111, 110, 103, 62);
    }
  }
  catch(ex)
  {
  }*/
}

/* *
 * 夺宝奇兵最新出价
 */
function newPrice(id)
{
  Ajax.call('snatch.php?act=new_price_list&id=' + id, '', newPriceResponse, 'GET', 'TEXT');
}

/* *
 * 夺宝奇兵最新出价反馈
 */

function newPriceResponse(result)
{
  document.getElementById('ECS_PRICE_LIST').innerHTML = result;
}

/* *
 *  返回属性列表
 */
function getAttr(cat_id)
{
  var tbodies = document.getElementsByTagName('tbody');
  for (i = 0; i < tbodies.length; i ++ )
  {
    if (tbodies[i].id.substr(0, 10) == 'goods_type')tbodies[i].style.display = 'none';
  }

  var type_body = 'goods_type_' + cat_id;
  try
  {
    document.getElementById(type_body).style.display = '';
  }
  catch (e)
  {
  }
}

/* *
 * 截取小数位数
 */
function advFormatNumber(value, num) // 四舍五入
{
  var a_str = formatNumber(value, num);
  var a_int = parseFloat(a_str);
  if (value.toString().length > a_str.length)
  {
    var b_str = value.toString().substring(a_str.length, a_str.length + 1);
    var b_int = parseFloat(b_str);
    if (b_int < 5)
    {
      return a_str;
    }
    else
    {
      var bonus_str, bonus_int;
      if (num == 0)
      {
        bonus_int = 1;
      }
      else
      {
        bonus_str = "0."
        for (var i = 1; i < num; i ++ )
        bonus_str += "0";
        bonus_str += "1";
        bonus_int = parseFloat(bonus_str);
      }
      a_str = formatNumber(a_int + bonus_int, num)
    }
  }
  return a_str;
}

function formatNumber(value, num) // 直接去尾
{
  var a, b, c, i;
  a = value.toString();
  b = a.indexOf('.');
  c = a.length;
  if (num == 0)
  {
    if (b != - 1)
    {
      a = a.substring(0, b);
    }
  }
  else
  {
    if (b == - 1)
    {
      a = a + ".";
      for (i = 1; i <= num; i ++ )
      {
        a = a + "0";
      }
    }
    else
    {
      a = a.substring(0, b + num + 1);
      for (i = c; i <= b + num; i ++ )
      {
        a = a + "0";
      }
    }
  }
  return a;
}

/* *
 * 根据当前shiping_id设置当前配送的的保价费用，如果保价费用为0，则隐藏保价费用
 *
 * return       void
 */
function set_insure_status()
{
  // 取得保价费用，取不到默认为0
  var shippingId = getRadioValue('shipping');
  var insure_fee = 0;
  if (shippingId > 0)
  {
    if (document.forms['theForm'].elements['insure_' + shippingId])
    {
      insure_fee = document.forms['theForm'].elements['insure_' + shippingId].value;
    }
    // 每次取消保价选择
    if (document.forms['theForm'].elements['need_insure'])
    {
      document.forms['theForm'].elements['need_insure'].checked = false;
    }

    // 设置配送保价，为0隐藏
    if (document.getElementById("ecs_insure_cell"))
    {
      if (insure_fee > 0)
      {
        document.getElementById("ecs_insure_cell").style.display = '';
        setValue(document.getElementById("ecs_insure_fee_cell"), getFormatedPrice(insure_fee));
      }
      else
      {
        document.getElementById("ecs_insure_cell").style.display = "none";
        setValue(document.getElementById("ecs_insure_fee_cell"), '');
      }
    }
  }
}

/* *
 * 当支付方式改变时出发该事件
 * @param       pay_id      支付方式的id
 * return       void
 */
function changePayment(pay_id)
{
  // 计算订单费用
  calculateOrderFee();
}

function getCoordinate(obj)
{
  var pos =
  {
    "x" : 0, "y" : 0
  }

  pos.x = document.body.offsetLeft;
  pos.y = document.body.offsetTop;

  do
  {
    pos.x += obj.offsetLeft;
    pos.y += obj.offsetTop;

    obj = obj.offsetParent;
  }
  while (obj.tagName.toUpperCase() != 'BODY')

  return pos;
}

function showCatalog(obj)
{
  var pos = getCoordinate(obj);
  var div = document.getElementById('ECS_CATALOG');

  if (div && div.style.display != 'block')
  {
    div.style.display = 'block';
    div.style.left = pos.x + "px";
    div.style.top = (pos.y + obj.offsetHeight - 1) + "px";
  }
}

function hideCatalog(obj)
{
  var div = document.getElementById('ECS_CATALOG');

  if (div && div.style.display != 'none') div.style.display = "none";
}

//发送邮箱验证
function sendHashMail()
{
  Ajax.call('user.php?act=send_hash_mail', '', sendHashMailResponse, 'GET', 'JSON');
}

function sendHashMailResponse(result)
{
  //alert(result.message);
  //提示用户去邮箱
  if(result.error ==0)
  {
	  alert("验证邮件发送成功!请到您的邮箱确认.");
  }else{
	  alert(result.message);
  }
}

/* 订单查询 */
function orderQuery()
{
  var order_sn = document.forms['ecsOrderQuery']['order_sn'].value;

  var reg = /^[\.0-9]+/;
  if (order_sn.length < 10 || ! reg.test(order_sn))
  {
    alert(invalid_order_sn);
    return;
  }
  Ajax.call('user.php?act=order_query&order_sn=s' + order_sn, '', orderQueryResponse, 'GET', 'JSON');
}

function orderQueryResponse(result)
{
  if (result.message.length > 0)
  {
    alert(result.message);
  }
  if (result.error == 0)
  {
    var div = document.getElementById('ECS_ORDER_QUERY');
    div.innerHTML = result.content;
  }
}

function display_mode(str)
{
    document.getElementById('display').value = str;
    setTimeout(doSubmit, 0);
    function doSubmit() {document.forms['listform'].submit();}
}

function display_mode_wholesale(str)
{
    document.getElementById('display').value = str;
    setTimeout(doSubmit, 0);
    function doSubmit() 
    {
        document.forms['wholesale_goods'].action = "wholesale.php";
        document.forms['wholesale_goods'].submit();
    }
}

//----------------------------------------------ecshop官方的解决ie6中png图片不透明问题---------------------------------------//
/* 修复IE6以下版本PNG图片Alpha */
function fixpng()
{
  var arVersion = navigator.appVersion.split("MSIE")
  var version = parseFloat(arVersion[1])

  if ((version >= 5.5) && (document.body.filters))
  {
     for(var i=0; i<document.images.length; i++)
     {
        var img = document.images[i]
        var imgName = img.src.toUpperCase()
        if (imgName.substring(imgName.length-3, imgName.length) == "PNG")
        {
           var imgID = (img.id) ? "id='" + img.id + "' " : ""
           var imgClass = (img.className) ? "class='" + img.className + "' " : ""
           var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' "
           var imgStyle = "display:inline-block;" + img.style.cssText
           if (img.align == "left") imgStyle = "float:left;" + imgStyle
           if (img.align == "right") imgStyle = "float:right;" + imgStyle
           if (img.parentElement.href) imgStyle = "cursor:hand;" + imgStyle
           var strNewHTML = "<span " + imgID + imgClass + imgTitle
           + " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
           + "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
           + "(src=\'" + img.src + "\', sizingMethod='scale');\"></span>"
           img.outerHTML = strNewHTML
           i = i-1
        }
     }
  }
}

function hash(string, length)
{
  var length = length ? length : 32;
  var start = 0;
  var i = 0;
  var result = '';
  filllen = length - string.length % length;
  for(i = 0; i < filllen; i++)
  {
    string += "0";
  }
  while(start < string.length)
  {
    result = stringxor(result, string.substr(start, length));
    start += length;
  }
  return result;
}

function stringxor(s1, s2)
{
  var s = '';
  var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  var max = Math.max(s1.length, s2.length);
  for(var i=0; i<max; i++)
  {
    var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
    s += hash.charAt(k % 52);
  }
  return s;
}

var evalscripts = new Array();
function evalscript(s)
{
  if(s.indexOf('<script') == -1) return s;
  var p = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/ig;
  var arr = new Array();
  while(arr = p.exec(s)) appendscript(arr[1], '', arr[2], arr[3]);
  return s;
}

function $$(id)
{
    return document.getElementById(id);
}

function appendscript(src, text, reload, charset)
{
  var id = hash(src + text);
  if(!reload && in_array(id, evalscripts)) return;
  if(reload && $$(id))
  {
    $$(id).parentNode.removeChild($$(id));
  }
  evalscripts.push(id);
  var scriptNode = document.createElement("script");
  scriptNode.type = "text/javascript";
  scriptNode.id = id;
  //scriptNode.charset = charset;
  try
  {
    if(src)
    {
      scriptNode.src = src;
    }
    else if(text)
    {
      scriptNode.text = text;
    }
    $$('append_parent').appendChild(scriptNode);
  }
  catch(e)
  {}
}

function in_array(needle, haystack)
{
  if(typeof needle == 'string' || typeof needle == 'number')
  {
    for(var i in haystack)
    {
      if(haystack[i] == needle)
      {
        return true;
      }
    }
  }
  return false;
}

var pmwinposition = new Array();

var userAgent = navigator.userAgent.toLowerCase();
var is_opera = userAgent.indexOf('opera') != -1 && opera.version();
var is_moz = (navigator.product == 'Gecko') && userAgent.substr(userAgent.indexOf('firefox') + 8, 3);
var is_ie = (userAgent.indexOf('msie') != -1 && !is_opera) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);
function pmwin(action, param)
{
  var objs = document.getElementsByTagName("OBJECT");
  if(action == 'open')
  {
    for(i = 0;i < objs.length; i ++)
    {
      if(objs[i].style.visibility != 'hidden')
      {
        objs[i].setAttribute("oldvisibility", objs[i].style.visibility);
        objs[i].style.visibility = 'hidden';
      }
    }
    var clientWidth = document.body.clientWidth;
    var clientHeight = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
    var scrollTop = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop;
    var pmwidth = 800;
    var pmheight = clientHeight * 0.9;
    if(!$$('pmlayer'))
    {
      div = document.createElement('div');div.id = 'pmlayer';
      div.style.width = pmwidth + 'px';
      div.style.height = pmheight + 'px';
      div.style.left = ((clientWidth - pmwidth) / 2) + 'px';
      div.style.position = 'absolute';
      div.style.zIndex = '999';
      $$('append_parent').appendChild(div);
      $$('pmlayer').innerHTML = '<div style="width: 800px; background: #666666; margin: 5px auto; text-align: left">' +
        '<div style="width: 800px; height: ' + pmheight + 'px; padding: 1px; background: #FFFFFF; border: 1px solid #7597B8; position: relative; left: -6px; top: -3px">' +
        '<div onmousedown="pmwindrag(event, 1)" onmousemove="pmwindrag(event, 2)" onmouseup="pmwindrag(event, 3)" style="cursor: move; position: relative; left: 0px; top: 0px; width: 800px; height: 30px; margin-bottom: -30px;"></div>' +
        '<a href="###" onclick="pmwin(\'close\')"><img style="position: absolute; right: 20px; top: 15px" src="images/close.gif" title="关闭" /></a>' +
        '<iframe id="pmframe" name="pmframe" style="width:' + pmwidth + 'px;height:100%" allowTransparency="true" frameborder="0"></iframe></div></div>';
    }
    $$('pmlayer').style.display = '';
    $$('pmlayer').style.top = ((clientHeight - pmheight) / 2 + scrollTop) + 'px';
    if(!param)
    {
        pmframe.location = 'pm.php';
    }
    else
    {
        pmframe.location = 'pm.php?' + param;
    }
  }
  else if(action == 'close')
  {
    for(i = 0;i < objs.length; i ++)
    {
      if(objs[i].attributes['oldvisibility'])
      {
        objs[i].style.visibility = objs[i].attributes['oldvisibility'].nodeValue;
        objs[i].removeAttribute('oldvisibility');
      }
    }
    hiddenobj = new Array();
    $$('pmlayer').style.display = 'none';
  }
}

var pmwindragstart = new Array();
function pmwindrag(e, op)
{
  if(op == 1)
  {
    pmwindragstart = is_ie ? [event.clientX, event.clientY] : [e.clientX, e.clientY];
    pmwindragstart[2] = parseInt($$('pmlayer').style.left);
    pmwindragstart[3] = parseInt($$('pmlayer').style.top);
    doane(e);
  }
  else if(op == 2 && pmwindragstart[0])
  {
    var pmwindragnow = is_ie ? [event.clientX, event.clientY] : [e.clientX, e.clientY];
    $$('pmlayer').style.left = (pmwindragstart[2] + pmwindragnow[0] - pmwindragstart[0]) + 'px';
    $$('pmlayer').style.top = (pmwindragstart[3] + pmwindragnow[1] - pmwindragstart[1]) + 'px';
    doane(e);
  }
  else if(op == 3)
  {
    pmwindragstart = [];
    doane(e);
  }
}

function doane(event)
{
  e = event ? event : window.event;
  if(is_ie)
  {
    e.returnValue = false;
    e.cancelBubble = true;
  }
  else if(e)
  {
    e.stopPropagation();
    e.preventDefault();
  }
}


/* *
 * 处理添加礼包到购物车的反馈信息
 */
function addPackageToCartResponse(result)
{
  if(result.error > 0)
  {
    if (result.error == 2)
    {
      if (confirm(result.message))
      {
        location.href = 'user.php?act=add_booking&id=' + result.goods_id;
      }
    }
    else
    {
      alert(result.message);    
    }
  }
  else
  {
    var cartInfo = document.getElementById('ECS_CARTINFO');
    var cart_url = 'flow.php?step=cart';
    if (cartInfo)
    {
      cartInfo.innerHTML = result.content;
    }

    if (result.one_step_buy == '1')
    {
      location.href = cart_url;
    }
    else
    {
      switch(result.confirm_type)
      {
        case '1' :
          if (confirm(result.message)) location.href = cart_url;
          break;
        case '2' :
          if (!confirm(result.message)) location.href = cart_url;
          break;
        case '3' :
          location.href = cart_url;
          break;
        default :
          break;
      }
    }
  }
}

function setSuitShow(suitId)
{
    var suit    = document.getElementById('suit_'+suitId);

    if(suit == null)
    {
        return;
    }
    if(suit.style.display=='none')
    {
        suit.style.display='';
    }
    else
    {
        suit.style.display='none';
    }
}


/* 以下四个函数为属性选择弹出框的功能函数部分 */
//检测层是否已经存在
function docEle() 
{
  return document.getElementById(arguments[0]) || false;
}

//生成属性选择层
function openSpeDiv(message, goods_id, parent) 
{
  var _id = "speDiv";
  var m = "mask";
  if (docEle(_id)) document.removeChild(docEle(_id));
  if (docEle(m)) document.removeChild(docEle(m));
  //计算上卷元素值
  var scrollPos; 
  if (typeof window.pageYOffset != 'undefined') 
  { 
    scrollPos = window.pageYOffset; 
  } 
  else if (typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat') 
  { 
    scrollPos = document.documentElement.scrollTop; 
  } 
  else if (typeof document.body != 'undefined') 
  { 
    scrollPos = document.body.scrollTop; 
  }

  var i = 0;
  var sel_obj = document.getElementsByTagName('select');
  while (sel_obj[i])
  {
    sel_obj[i].style.visibility = "hidden";
    i++;
  }

  // 新激活图层
  var newDiv = document.createElement("div");
  newDiv.id = _id;
  newDiv.style.position = "absolute";
  newDiv.style.zIndex = "10000";
  newDiv.style.width = "300px";
  newDiv.style.height = "260px";
  newDiv.style.top = (parseInt(scrollPos + 200)) + "px";
  newDiv.style.left = (parseInt(document.body.offsetWidth) - 200) / 2 + "px"; // 屏幕居中
  newDiv.style.overflow = "auto"; 
  newDiv.style.background = "#FFF";
  newDiv.style.border = "3px solid #59B0FF";
  newDiv.style.padding = "5px";

  //生成层内内容
  newDiv.innerHTML = '<h4 style="font-size:14; margin:15 0 0 15;">' + select_spe + "</h4>";

  for (var spec = 0; spec < message.length; spec++)
  {
      newDiv.innerHTML += '<hr style="color: #EBEBED; height:1px;"><h6 style="text-align:left; background:#ffffff; margin-left:15px;">' +  message[spec]['name'] + '</h6>';

      if (message[spec]['attr_type'] == 1)
      {
        for (var val_arr = 0; val_arr < message[spec]['values'].length; val_arr++)
        {
          if (val_arr == 0)
          {
            newDiv.innerHTML += "<input style='margin-left:15px;' type='radio' name='spec_" + message[spec]['attr_id'] + "' value='" + message[spec]['values'][val_arr]['id'] + "' id='spec_value_" + message[spec]['values'][val_arr]['id'] + "' checked /><font color=#555555>" + message[spec]['values'][val_arr]['label'] + '</font> [' + message[spec]['values'][val_arr]['format_price'] + ']</font><br />';      
          }
          else
          {
            newDiv.innerHTML += "<input style='margin-left:15px;' type='radio' name='spec_" + message[spec]['attr_id'] + "' value='" + message[spec]['values'][val_arr]['id'] + "' id='spec_value_" + message[spec]['values'][val_arr]['id'] + "' /><font color=#555555>" + message[spec]['values'][val_arr]['label'] + '</font> [' + message[spec]['values'][val_arr]['format_price'] + ']</font><br />';      
          }
        } 
        newDiv.innerHTML += "<input type='hidden' name='spec_list' value='" + val_arr + "' />";
      }
      else
      {
        for (var val_arr = 0; val_arr < message[spec]['values'].length; val_arr++)
        {
          newDiv.innerHTML += "<input style='margin-left:15px;' type='checkbox' name='spec_" + message[spec]['attr_id'] + "' value='" + message[spec]['values'][val_arr]['id'] + "' id='spec_value_" + message[spec]['values'][val_arr]['id'] + "' /><font color=#555555>" + message[spec]['values'][val_arr]['label'] + ' [' + message[spec]['values'][val_arr]['format_price'] + ']</font><br />';     
        }
        newDiv.innerHTML += "<input type='hidden' name='spec_list' value='" + val_arr + "' />";
      }
  }
  newDiv.innerHTML += "<br /><center>[<a href='javascript:submit_div(" + goods_id + "," + parent + ")' class='f6' >" + btn_buy + "</a>]&nbsp;&nbsp;[<a href='javascript:cancel_div()' class='f6' >" + is_cancel + "</a>]</center>";
  document.body.appendChild(newDiv);


  // mask图层
  var newMask = document.createElement("div");
  newMask.id = m;
  newMask.style.position = "absolute";
  newMask.style.zIndex = "9999";
  newMask.style.width = document.body.scrollWidth + "px";
  newMask.style.height = document.body.scrollHeight + "px";
  newMask.style.top = "0px";
  newMask.style.left = "0px";
  newMask.style.background = "#FFF";
  newMask.style.filter = "alpha(opacity=30)";
  newMask.style.opacity = "0.40";
  document.body.appendChild(newMask);
} 

//获取选择属性后，再次提交到购物车
function submit_div(goods_id, parentId) 
{
  var goods        = new Object();
  var spec_arr     = new Array();
  var fittings_arr = new Array();
  var number       = 1;
  var input_arr      = document.getElementsByTagName('input'); 
  var quick		   = 1;

  var spec_arr = new Array();
  var j = 0;

  for (i = 0; i < input_arr.length; i ++ )
  {
    var prefix = input_arr[i].name.substr(0, 5);

    if (prefix == 'spec_' && (
      ((input_arr[i].type == 'radio' || input_arr[i].type == 'checkbox') && input_arr[i].checked)))
    {
      spec_arr[j] = input_arr[i].value;
      j++ ;
    }
  }

  goods.quick    = quick;
  goods.spec     = spec_arr;
  goods.goods_id = goods_id;
  goods.number   = number;
  goods.parent   = (typeof(parentId) == "undefined") ? 0 : parseInt(parentId);

  Ajax.call('flow.php?step=add_to_cart', 'goods=' + goods.toJSONString(), addToCartResponse, 'POST', 'JSON');

  document.body.removeChild(docEle('speDiv'));
  document.body.removeChild(docEle('mask'));

  var i = 0;
  var sel_obj = document.getElementsByTagName('select');
  while (sel_obj[i])
  {
    sel_obj[i].style.visibility = "";
    i++;
  }

}

// 关闭mask和新图层
function cancel_div() 
{
  document.body.removeChild(docEle('speDiv'));
  document.body.removeChild(docEle('mask'));

  var i = 0;
  var sel_obj = document.getElementsByTagName('select');
  while (sel_obj[i])
  {
    sel_obj[i].style.visibility = "";
    i++;
  }
}



/*-------------------------------首页:透明片切换-------------------------------------------*/
function show(n){
	if(n){
		document.getElementById("mshow1").style.display="none";
		document.getElementById("mshow2").style.display="none";
		document.getElementById("mshow3").style.display="none";
		document.getElementById("mshow4").style.display="none";
		/*
		document.getElementById("menuover1").className="color_qianfei";
		document.getElementById("menuover2").className="color_qianfei";
		document.getElementById("menuover3").className="color_qianfei";
		*/
		
		/*显示灰色*/
		document.getElementById("picmbj1").style.background="url('themes/default/images/index/a11.gif') no-repeat";
		document.getElementById("picmbj2").style.background="url('themes/default/images/index/a21.gif') no-repeat";
		document.getElementById("picmbj3").style.background="url('themes/default/images/index/a31.gif') no-repeat";
		document.getElementById("picmbj4").style.background="url('themes/default/images/index/a41.gif') no-repeat";
		
		/*处理中的那个图片*/
		var urls = 'themes/default/images/index/a'+n+'.gif';
		document.getElementById("picmbj"+n).style.background="url("+urls+") no-repeat";

		/*第一张的默认情况*/
		if(n==1)document.getElementById("picmbj1").style.background="url('themes/default/images/index/a1.gif') no-repeat";		
		document.getElementById("mshow"+n).style.display="";
	}
}

/*-彩色片切换-*/
function shows(n){
	if(n){
		document.getElementById("mshows1").style.display="none";
		document.getElementById("mshows2").style.display="none";
		document.getElementById("mshows3").style.display="none";
		document.getElementById("mshows4").style.display="none";
		/*
		document.getElementById("menuovers1").className="color_qianfei";
		document.getElementById("menuovers2").className="color_qianfei";
		document.getElementById("menuovers3").className="color_qianfei";
		*/
				
		document.getElementById("picmbjs1").style.background="url('themes/default/images/index/b11.gif') no-repeat";
		document.getElementById("picmbjs2").style.background="url('themes/default/images/index/b21.gif') no-repeat";
		document.getElementById("picmbjs3").style.background="url('themes/default/images/index/b31.gif') no-repeat";
		document.getElementById("picmbjs4").style.background="url('themes/default/images/index/b41.gif') no-repeat";
		
		/*处理中的那个图片*/
		var urls = 'themes/default/images/index/b'+n+'.gif';
		document.getElementById("picmbjs"+n).style.background="url("+urls+") no-repeat";
		
		
		/*第一张的默认情况*/
		if(n==1)document.getElementById("picmbjs1").style.background="url('themes/default/images/index/b1.gif') no-repeat";		
		document.getElementById("mshows"+n).style.display="";	
	}
}

function showtype(n){
	
	if(n){
		document.getElementById("lefttopmain2").style.display="none";
		document.getElementById("lefttopmain1").style.display="none";
		
		document.getElementById("lefttop1").style.background="url('themes/default/images/yi_dao/1b.jpg') repeat-x";//灰色
		document.getElementById("lefttop2").style.background="url('themes/default/images/yi_dao/2j.gif') no-repeat 9px 8px";//白色
		
		document.getElementById("lefttopmain"+n).style.display="";
		
		if(n==2){
			document.getElementById("lefttop1").style.background="url('themes/default/images/yi_dao/1b.jpg') repeat-x";
			/*字体变红色*/
			document.getElementById("toptxt1").style.color="#333";
			document.getElementById("toptxt2").style.color="#c30000";
									
			document.getElementById("lefttop2").style.background="url('themes/default/images/yi_dao/2j.gif') no-repeat 9px 8px";
			
		}
		
		if(n==1){
			document.getElementById("lefttop1").style.background="url('themes/default/images/yi_dao/2j.gif') no-repeat 9px 8px";
			/*字体变红色*/
			document.getElementById("toptxt1").style.color="#c30000";
			document.getElementById("toptxt2").style.color="#333";		
				
			document.getElementById("lefttop2").style.background="url('themes/default/images/yi_dao/1b.jpg') repeat-x";
		
			//修改小箭头 然后用个线条	
		}
	
	}
}
//yi_showtype
function showtype1(n){
	
	if(n){
		document.getElementById("lefttopmain2").style.display="none";
		document.getElementById("lefttopmain1").style.display="none";
		
		document.getElementById("lefttop11").style.background="url('themes/default/images/yi_dao/1b.jpg') repeat-x";//灰色
		document.getElementById("lefttop21").style.background="url('themes/default/images/yi_dao/2j.gif') no-repeat 9px 8px";//白色
		
		document.getElementById("lefttopmain"+n).style.display="";
		
		if(n==2){
			document.getElementById("lefttop11").style.background="url('themes/default/images/yi_dao/1b.jpg') repeat-x";
			/*字体变红色*/
			document.getElementById("toptxt11").style.color="#333";
			document.getElementById("toptxt21").style.color="#c30000";
									
			document.getElementById("lefttop21").style.background="url('themes/default/images/yi_dao/2j.gif') no-repeat 9px 8px";
			
		}
		
		if(n==1){
			document.getElementById("lefttop11").style.background="url('themes/default/images/yi_dao/2j.gif') no-repeat 9px 8px";
			/*字体变红色*/
			document.getElementById("toptxt11").style.color="#c30000";
			document.getElementById("toptxt21").style.color="#333";		
				
			document.getElementById("lefttop21").style.background="url('themes/default/images/yi_dao/1b.jpg') repeat-x";
		
		//修改小箭头 然后用个线条
		
		}
	
	}
}
//yi_showtype
function showtype2(n){
	
	if(n){
		document.getElementById("lefttopmain2").style.display="none";
		document.getElementById("lefttopmain1").style.display="none";
		
		document.getElementById("lefttop12").style.background="url('themes/default/images/yi_dao/1b.jpg') repeat-x";//灰色
		document.getElementById("lefttop22").style.background="url('themes/default/images/yi_dao/2j.gif') no-repeat 9px 8px";//白色
		
		document.getElementById("lefttopmain"+n).style.display="";
		
		if(n==2){
			document.getElementById("lefttop12").style.background="url('themes/default/images/yi_dao/1b.jpg') repeat-x";
			/*字体变红色*/
			document.getElementById("toptxt12").style.color="#333";
			document.getElementById("toptxt22").style.color="#c30000";
									
			document.getElementById("lefttop22").style.background="url('themes/default/images/yi_dao/2j.gif') no-repeat 9px 8px";
			
		}
		
		if(n==1){
			document.getElementById("lefttop12").style.background="url('themes/default/images/yi_dao/2j.gif') no-repeat 9px 8px";
			/*字体变红色*/
			document.getElementById("toptxt12").style.color="#c30000";
			document.getElementById("toptxt22").style.color="#333";		
				
			document.getElementById("lefttop22").style.background="url('themes/default/images/yi_dao/1b.jpg') repeat-x";
		
		//修改小箭头 然后用个线条
		
		}
	
	}
}	
	
function showclass(n){
//注意两个函数的细小差别	
if(n){
document.getElementById("lefttopmain1").style.display="none";
document.getElementById("lefttopmain2").style.display="none";

document.getElementById("m_l_t_l").style.background="url('themes/default/images/typeleftx.jpg')";
document.getElementById("m_l_t_r").style.background="url('themes/default/images/typerightx.jpg')";

document.getElementById("lefttopmain"+n).style.display="";
if(n==2){

document.getElementById("m_l_t_l").style.background="url('themes/default/images/typeleftx.jpg')";
document.getElementById("m_l_t_r").style.background="url('themes/default/images/typerightx.jpg')";

	}
	
	if(n==1){
document.getElementById("m_l_t_l").style.background="url('themes/default/images/classtypeover.jpg')";
document.getElementById("m_l_t_r").style.background="url('themes/default/images/typeleft.jpg')";

	}
}
}

/*kefu菜单切换--可删除*/	
function showmenu(){
	document.getElementById("hide").style.display="block";
	//更换图片
	document.getElementById('kefu').style.background="url('themes/default/images/index/kefu2.gif')";
}
function hidemenu(){
	document.getElementById("hide").style.display="none";
	//更换图片
	document.getElementById('kefu').style.background="url('themes/default/images/index/kefu.gif')";
}

//--------------------------------------------------------------------产品页----选项卡切换------------------------------------------------------------
function detail(n){		
	//--采用一起换的方式进行----
	if(n){
		document.getElementById("detail1").style.display="none";
		document.getElementById("detail4").style.display="none";
		document.getElementById("detail5").style.display="none";	
		document.getElementById("detail"+n).style.display="";	
	}
}

function jiansh(n){
	if(document.formCart("goods_number["+n+"]").value>1)
	document.formCart("goods_number["+n+"]").value=document.formCart("goods_number["+n+"]").value-1;
	}
	
	function jiash(n){
	if(document.formCart("goods_number["+n+"]").value>0)
	document.formCart("goods_number["+n+"]").value=document.formCart("goods_number["+n+"]").value*1+1;
	}
//-----------------------------减号-----------------------------------	
function jianzbsh(){
	alert("-");
	if(document.formCart("sszb["+n+"]").value>1)
	document.formCart("sszb["+n+"]").value=document.formCart("sszb["+n+"]").value-1;
}
//-----------------------------加号-----------------------------------	
function jiazbsh(){
	if(document.formCart("sszb["+n+"]").value>0)
	document.formCart("sszb["+n+"]").value=document.formCart("sszb["+n+"]").value*1+1;
}
	
	
	function jianybsh(n){
	if(document.formCart("ssyb["+n+"]").value>1)
	document.formCart("ssyb["+n+"]").value=document.formCart("ssyb["+n+"]").value-1;
	}
	
	function jiaybsh(n){
	if(document.formCart("sszb["+n+"]").value>0)
	document.formCart("ssyb["+n+"]").value=document.formCart("ssyb["+n+"]").value*1+1;
	}
	
function showqg(n)
{
	document.getElementById("tjcontent").style.display="none";
	document.getElementById("xiancontent").style.display="none";
	if(n==1){document.getElementById("tjcontent").style.display="";}
	if(n==2){document.getElementById("xiancontent").style.display="";}
}
		
function showgood(n){
	document.getElementById("newgood1").style.display="none";
	document.getElementById("newgood2").style.display="none";
	document.getElementById("newgood3").style.display="none";
	document.getElementById("newgood4").style.display="none";
	document.getElementById("newgood5").style.display="none";
	document.getElementById("newgood6").style.display="none";
	
	document.getElementById("newgood"+n).style.display="";
}
	
	
function showmjx(n){
	document.getElementById("newmjx1").style.display="none";
	document.getElementById("newmjx2").style.display="none";
	document.getElementById("newmjx3").style.display="none";
	document.getElementById("newmjx4").style.display="none";
	document.getElementById("newmjx5").style.display="none";
	document.getElementById("newmjx6").style.display="none";
	
	document.getElementById("newmjx"+n).style.display="";
}

/* ----------------------------------------------------------------------------------------------------------------------
 * yi:商品加入购物车
 * ----------------------------------------------------------------------------------------------------------------------
 */
function showDiv(id,gid,buyNow)
{
	var is_sg = document.getElementById('is_sg').value;
   
	var ret   = '';
	if( is_sg =='')
	{	
		ret = addToCart(gid,buyNow);//普通商品	
	}
	else
	{		
		ret = addToCartsg(gid,buyNow);	//散光片	
	}
	//如果验证没通过则跳出
	if(ret == false)
	{
		return false;
	}
}



function showClose(id){
	document.getElementById(id).style.display = "none";	
	//ie6bug.
	if($.browser.msie && $.browser.version == '6.0'){
		$("#framediv").css("display","none");
	}
}
//护理液加入购物车
function showDivz(id,gid,buyNow)
{
	//---------------------------------真正加入购物车---------------------------------
	addToCartz(gid,buyNow);
	//---------------------------------加入购物车之前验证：是否有货(此处未完善)-----------
	var obj = document.getElementById(id);	
	obj.style.display = "block";	
	//obj.style.position = "relative";
	//obj.style.top = "-112px";
	//obj.style.left= "-61px";
}
//-------------------------------------------------------------------------------------------------------

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:商品直接加入购物车（立即购买功能）
 * ----------------------------------------------------------------------------------------------------------------------
 */
function direct_to_cart(id,gid)
{
	var is_sg = document.getElementById('is_sg').value;
	var ret = '';
	
	if( is_sg ==''){
		//普通商品
		ret = addToCart(gid);	
	}else{
		//散光片
		ret = addToCartsg(gid);		
	}
	
	if(ret == false){
		return false;//如果验证没通过则跳出
	}else{	
		//window.location.reload(true);
		//$("#mydiv").show();
		window.location.href = 'flow.html';
		//window.location.replace('flow.php?step=cart&ttt=1');
	}
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:（没有度数商品）直接加入购物车（立即购买功能）
 * ----------------------------------------------------------------------------------------------------------------------
 */
function direct_to_cart2(gid)
{	
	addToCartz(gid);
	location.href = 'flow.html';
	//window.location.replace('flow.html');
}


//logo_tr切换
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
/*====================================================会员中心===========================================================*/

/*======================跟踪包裹弹窗===========================*/
function follow_pack(iframeSrc,iframeWidth,iframeHeight){
	
	//获取客户端页面宽高
	var _client_width  = document.body.clientWidth;
	var _client_height = document.documentElement.scrollHeight;
	
	//创建遮罩
	if(typeof($("#jd_shadow")[0]) == "undefined"){
		//前置遮罩
		$("body").prepend("<div id='jd_shadow'>&nbsp;</div>");
		var _jd_shadow = $("#jd_shadow");
		_jd_shadow.css("width", _client_width + "px");
		_jd_shadow.css("height", _client_height + "px");
	}
	
	//创建白色弹窗面板
	if(typeof($("#jd_dialog")[0]) != "undefined"){
		$("#jd_dialog").remove();
	}
	$("body").prepend("<div id='jd_dialog'></div>");
	//白色弹窗定位
	var _jd_dialog = $("#jd_dialog");
	var _left = (_client_width - iframeWidth) / 2;
	_jd_dialog.css("left", (_left < 0 ? 0 : _left) + document.documentElement.scrollLeft + "px");
	
	var _top = (document.documentElement.clientHeight - iframeHeight) / 2;
	_jd_dialog.css("top", (_top < 0 ? 0 : _top) + document.documentElement.scrollTop + "px");
	
	//白色弹窗主体
	_jd_dialog.append("<div id='jd_dialog_m'></div>");
	var _jd_dialog_m = $("#jd_dialog_m");
	_jd_dialog_m.css("border","0px solid");
	_jd_dialog_m.css("width", iframeWidth + "px");
	_jd_dialog_m.css("height", iframeHeight + "px");
	_jd_dialog_m.css("background-color","#fff");
	
	//修改高度 留40px给关闭
	iframeHeight =432;
	//iframe 容器
	_jd_dialog_m.append("<div id='jd_dialog_m_b_2'></div>");
	//iframe
	$("#jd_dialog_m_b_2").append("<iframe id='jd_iframe' src='"+iframeSrc+"' scrolling='no' frameborder='0' width='"+iframeWidth+"' height='"+iframeHeight+"' />");
	
	//添加关闭按钮
	_jd_dialog_m.append("<div id='jd_close' title='关闭' onclick='jd_close()'>关闭</div>");	
}

/*=============关闭模态窗口===============*/
function jd_close(){
	$("#jd_shadow").remove();
	$("#jd_dialog").remove();
}

/* ----------------------------------------------------------------------------------------------------------------------
 * 函数 yi:加入收藏夹
 * ----------------------------------------------------------------------------------------------------------------------
 */
function add_book_mark(txt, url){
    if((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function")){
        window.sidebar.addPanel(txt, url, "");
    }else{
        window.external.AddFavorite(url, txt);
    }
}

