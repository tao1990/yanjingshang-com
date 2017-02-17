/*
//焦点图
Qfast.add('widgets', { path: "js/terminator2.2.min.js", type: "js", requires: ['fx'] });  
Qfast(false, 'widgets', function () {
        		K.tabs({
        			id: 'fsD1',   //焦点图包裹id  
        			conId: "D1pic1",  //** 大图域包裹id  
        			tabId:"D1fBt",  
        			tabTn:"a",
        			conCn: '.fcon', //** 大图域配置class       
        			auto: 0,   //自动播放 1或0
        			effect: 'fade',   //效果配置
        			eType: 'click', //** 鼠标事件
        			pageBt:true,//是否有按钮切换页码
        			bns: ['.prev', '.next'],//** 前后按钮配置class                          
        			interval: 3000  //** 停顿时间  
        		}) 
})  
*/
//导航切换
$("#jbxx").click(function(){
  $(this).addClass('xz').siblings().removeClass('xz');
  $(".jbxx").show();
  $(".cpxq").hide();
  $(".yhpl").hide();
})
$("#cpxq").click(function(){
  $(this).addClass('xz').siblings().removeClass('xz');
  $(".jbxx").hide();
  $(".cpxq").show();
  $(".yhpl").hide();
})
$("#yhpl").click(function(){
  $(this).addClass('xz').siblings().removeClass('xz');
  $(".jbxx").hide();
  $(".cpxq").hide();
  $(".yhpl").show();
})


//更改数量
function change_count(act){
    if(act=='+'){
            $('#count').val(parseInt($('#count').val())+parseInt(1));
        }else if(act=='-' && $('#count').val()>1){
            $('#count').val(parseInt($('#count').val())-parseInt(1));
        }
}

//顶部跟随
   // 顶部跟随和头部的顶部跟随冲突，注释了   zhang：150824
$(document).ready(function(){
    try{
        t = $('.flowbox').offset().top;

        fh = $('.flowbox').height();

        $(window).scroll(function(e){
            s = $(document).scrollTop();
            if(s > t + fh){
            $('.flowbox').addClass("scrollCls");
            }else{
            $('.flowbox').removeClass("scrollCls");
            }
        })
    }catch(ex){
        //
    }
	
})

//折叠
$(".fold").each(function(){
    $(this).css('background','url("images/jt_up.png") 90% 50% no-repeat');
    $(this).next().css('display','block');
});
$("#tips_c").hide();
$(".fold").toggle(
    function(){ 
       $(this).css('background','url("images/jt_down.png") 90% 50% no-repeat');
       $(this).next().css('display','none');
    },function(){ 
       $(this).css('background','url("images/jt_up.png") 90% 50% no-repeat');
       $(this).next().css('display','block');
    })
// 促销信息折叠显示小标签
$("#tips").toggle(
    function(){
        $("#tips_c").show();
    },function(){
        $("#tips_c").hide();
    }
)
//度数选择
$(".ds_radio_list input").click(function(){
    $(this).prev().addClass('on').end()
            .parent().siblings('.ds_radio_div')
            .find('label').removeClass('on');
    /*
    $(".ds_radio_list label").removeClass('on');
    $(this).prev().addClass('on');
    */
})
//散光选择
$("#sg_radio_list input").click(function(){
    $("#sg_radio_list label").removeClass('on');
    $(this).prev().addClass('on');
})
//轴位选择
$("#zw_radio_list input").click(function(){
    $("#zw_radio_list label").removeClass('on');
    $(this).prev().addClass('on');
})


/* *
 * 收藏
 */
function collect_ajax(goodsId)
{
  //Ajax.call('user.php?act=collect', 'id=' + goodsId, collectResponse, 'GET', 'JSON');
  $.ajax({
			type:'GET',
			url:'user.php?act=collect',
            dataType:'json',
			data:"id="+goodsId,
			success:function(result){
				alert(result.message);
                if(result.error==1){
                    //window.location.href="user.php"; 
                    if(confirm('是否返回个人中心页面?')){
                        window.location.href="user.php"; 
                    }
                }
			}
		});
  
}

/**
  * 商品加入购物车(前端验证参数)
  * @param goods_id 商品ID
  * @param n 商品种类
  */
function add_to_cart(goods_id, n) {
    var goods        = new Object();
    var spec_arr     = new Array();
    var fittings_arr = new Array();
    var number       = 1;
    var formBuy      = document.forms['ECS_FORMBUY'];
    var quick        = 0;
    var bz           = 0;
    var tishi        = true;

	var msg_cart = '';
    var goods_number = $("input[name='goods_number']").val();	//商品数量
	var right_eye_ds = $("input[name='right_eye_ds']:checked").val();	//右眼度数
	var right_eye_num = $("#right_eye_num").val();	//右眼数量
	var left_eye_ds = $("input[name='left_eye_ds']:checked").val();		//左眼度数
	var left_eye_num = $("input[name='left_eye_num']").val();	//左眼数量
	var right_eye_sg = $("#right_eye_sg").val();	//右眼散光
	var right_eye_zw = $("#right_eye_zw").val();	//右眼轴位
	var left_eye_sg = $("input[name='left_eye_sg']:checked").val();			//左眼散光
	var left_eye_zw = $("input[name='left_eye_zw']:checked").val();			//左眼轴位
	var kj_tongju = $("#kj_tongju").val();			//框架瞳距

    if (n == 1) {
		//A.正常隐形眼镜
		if (! left_eye_ds) {
			//1.未选度数
			msg_cart = '请选择眼镜度数!';
		} else if (left_eye_ds) {
			//2.选择两个度数
			if (!left_eye_num) {
				msg_cart = '请选择眼镜数量!';
			}
		}
	} else if (n == 2) {
		//B.散光片
		if (! left_eye_ds) {
			//1.未选度数
			msg_cart = '请选择眼镜度数!';
		} else if (left_eye_ds) {
			//2.选择两个度数
			if (! (left_eye_num)) {
				msg_cart = '请选择眼镜数量!';
			}
			if (! left_eye_sg || ! left_eye_zw) {
				msg_cart = '请选择散光度数和轴位!';
			}
		} 	
	} else if (n == 3) {
		//C.框架和镜片
        right_eye_ds = $("#right_eye_ds").val();
        left_eye_ds = $("#left_eye_ds").val();	
		if ( ! right_eye_ds || ! left_eye_ds || ! kj_tongju) {
			msg_cart = '请选择眼镜度数和瞳距!';
		} else {
			//选择散光,必须选择轴位
			if (right_eye_sg && ! right_eye_zw) {
				msg_cart = '请选择右眼散光轴位!';
			}
			if (left_eye_sg && ! left_eye_zw) {
				msg_cart = '请选择左眼散光轴位!';
			}
		}
		
	}else if(n == 4){
        if (! left_eye_ds) {
            //1.未选度数
            msg_cart = '请选择眼镜度数!';
        } else if (left_eye_ds) {
            //2.选择两个度数
            if (!left_eye_num) {
                msg_cart = '请选择眼镜数量!';
            }
        }
        var lhj = 1;
    } else {
		//D.无度数属性商品和单买镜架
		if ( ! (goods_number)) {
			msg_cart = '请选择商品数量!';
		}
	}
	//alert(msg_cart);return false;
	if (msg_cart == '') {
        if(!left_eye_num){left_eye_num=0;}
        if(!right_eye_num){right_eye_num=0;}
        goods.quick    = quick;
        goods.spec     = spec_arr;
        goods.goods_id = goods_id;
        goods.number   = parseInt(left_eye_num)+parseInt(right_eye_num);
        goods.zselect  = !left_eye_ds ? "" : left_eye_ds ;
        goods.zcount   = left_eye_num;
        goods.yselect  = !right_eye_ds ? "" : right_eye_ds;
        goods.ycount   = right_eye_num;
        goods.zsg      = left_eye_sg;
        goods.zzhou    = left_eye_zw;
        goods.ysg      = right_eye_sg;
        goods.yzhou    = right_eye_zw;
        goods.parent   = 0;
        lhj == 1 ? goods.lhj = lhj : goods.lhj = 0;
        n == 2 ? goods.issg = 1 : goods.issg = "";
        //var resss = goods.toJSONString();
        //print_array(resss);return false;
        //Ajax.call('ajax_goods.php?act=add_to_cart', 'goods=' + goods.toJSONString(), addToCartResponse, 'POST', 'JSON');
        //return true;
		$.ajax({
			type:'POST',
			url:'ajax_goods.php?act=add_to_cart',
			data:'&goods=' + goods.toJSONString() + '&m='+Math.random(),
			cache:false,
			success:
				function(d){
					/*var dd = eval('('+dd+')');
					var code = dd['info_code'];
					var msg  = dd['info_msg'];

					if(code==1){
						alert('恭喜您获得易视网优惠券，已存入您的易视账户中！');
					}else{
						alert(msg);
					}*/
                    //document.write(d);return false;
                    var dd = JSON.parse(d);
                    if(dd.error==1){
                        alert(dd.message)
                    }else{
                        $(".pg-cars-num").html(d);
                        $("#mydiv").show();
                    }
				}
		});
		
	} else {
		alert(msg_cart);
	}
}
//------------------------------------------------------------添加商品到购物车（没有度数的） $参数：商品id号,有parentId：配件------------------------------------------------------------------
function add_to_cart_z(goodsId, parentId)
{
    var goods        = new Object();
    var spec_arr     = new Array();
    var fittings_arr = new Array();
    var number       = 1;
    var formBuy      = document.forms['ECS_FORMBUY'];
    var quick		 = 0;
    var bz           = 0;//步骤

    // 检查是否有商品规格
    if(formBuy)
    {
        spec_arr = getSelectedAttributes(formBuy);
        /* zhang: 150906  注释
        if(formBuy.elements['number'])
        {
            number = formBuy.elements['number'].value;

            //更改页面头部购物车数量
            var show_goods_num = parseInt(document.getElementById("head_cart_num").innerHTML);
            show_times = 0; //cart.js页面的变量,等于0表示再次读取数据
            document.getElementById("head_cart_num").innerHTML = show_goods_num + parseInt(number);
            document.getElementById("head_cart_num2").innerHTML = show_goods_num + parseInt(number);
        }*/
        quick = 1;
    }
    goods.quick    = quick;
    goods.spec     = spec_arr;
    goods.goods_id = goodsId;
    goods.number   = number;
    goods.parent   = (typeof(parentId) == "undefined") ? 0 : parseInt(parentId);
    //print_array(goods);return false;
    //Ajax.call('ajax_goods.php?act=add_to_cart', 'goods=' + goods.toJSONString(), addToCartResponse, 'POST', 'JSON');
    $.ajax({
        type:'POST',
        url:'ajax_goods.php?act=add_to_cart',
        data:'&goods=' + goods.toJSONString() + '&m='+Math.random(),
        cache:false,
        success:
            function(d){
                //document.write(d);return false;
                //alert(d);
                var dd = JSON.parse(d);
                if(dd.error==1){
                    if(dd.pageJump){
                        alert(dd.message);
                        window.location.href=dd.pageJump; 
                        //window.location.href=dd.pageJump; 
                    }else{
                        alert(dd.message)
                    }
                }else{
                        $(".pg-cars-num").html(d);
                        $("#mydiv").show();
                }
            }
    });
}
//yi:同时购买框架和镜片
function add_to_cart_kj(gid)
{
    var glasses = document.getElementById("get_glasses_type").value;
    var zselect = document.getElementById("left_eye_ds").value;
    var yselect = document.getElementById("right_eye_ds").value;
    var kj_tongju = document.getElementById("kj_tongju").value;

    if(zselect == '' || yselect == '' || kj_tongju == '')
    {
        alert('请先选择好眼镜的度数和瞳距！');
        return false;
    }
    else
    {
        if(glasses == '' || glasses<1)
        {
            glasses = 1;
        }

        kj_add_cart(gid, glasses);
    }
}

//yi:框架加入购物车    //glasses_type:镜片类型
function kj_add_cart(goods_id, glasses_type)
{
    //==================================眼镜数据==================================//
    var zselect =(document.ECS_FORMBUY.zselect.value);
    var yselect =(document.ECS_FORMBUY.yselect.value);
    var goods_number =(document.ECS_FORMBUY.goods_number.value);
    var kj_tongju    = (document.ECS_FORMBUY.kj_tongju.value);
    var zsg   = (document.ECS_FORMBUY.zsg.value);
    var ysg   = (document.ECS_FORMBUY.ysg.value);
    var zzhou = (document.ECS_FORMBUY.zzhou.value);
    var yzhou = (document.ECS_FORMBUY.yzhou.value);
/*alert(zselect);
alert(yselect);
alert(goods_number);
alert(kj_tongju);
alert(zsg);
alert(ysg);
alert(zzhou);
alert(yzhou);
alert(glasses_type);
    return false;*/
    $.ajax({
        type:'post',
        url: 'ajax_goods.php?act=kuangjia_buy',
        data:'&goods_id='+goods_id+'&glasses_type='+glasses_type+'&goods_number='+goods_number+'&zselect='+zselect+'&yselect='+yselect+'&kj_tongju='+kj_tongju+'&zsg='+zsg+'&ysg='+ysg+'&zzhou='+zzhou+'&yzhou='+yzhou+'&m='+Math.random(),
        cache:false,
        success:
            function(da)
            {
                var dr = Array();
                dr = da.split('_');
                //print_array(dr);return false;
                if(dr[0] == 'ok')
                {
                    //---------------------------------弹出div提示----------------------------------//
                    $("#mydiv").show();
                    //---------------------------------弹出div提示END-------------------------------//
                }
                else if(da == 'fail')
                {
                    alert('很抱歉，加入购物车失败，请联系客服！');
                }
                else{}
            }
    });
}

//打印数组
function print_array(arr){
    for(var key in arr){
        if(typeof(arr[key])=='array'||typeof(arr[key])=='object'){//递归调用
            print_array(arr[key]);
        }else{
            document.write(key + ' = ' + arr[key] + '<br>');
        }
    }
}

// 旧版添加购物车
function add_to_cart_old(goods_id, n) {
	var msg_cart = '';
    var goods_number = $("input[name='goods_number']").val();	//商品数量
	var right_eye_ds = $("input[name='right_eye_ds']:checked").val();	//右眼度数
	var right_eye_num = $("#right_eye_num").val();	//右眼数量
	var left_eye_ds = $("input[name='left_eye_ds']:checked").val();		//左眼度数
	var left_eye_num = $("input[name='left_eye_num']").val();	//左眼数量
	var right_eye_sg = $("#right_eye_sg").val();	//右眼散光
	var right_eye_zw = $("#right_eye_zw").val();	//右眼轴位
	var left_eye_sg = $("#left_eye_sg").val();		//左眼散光
	var left_eye_zw = $("#left_eye_zw").val();		//左眼轴位
	var kj_tongju = $("#kj_tongju").val();			//框架瞳距

	if (n == 1) {
		//A.正常隐形眼镜
		if (! left_eye_ds) {
			//1.未选度数
			msg_cart = '请选择眼镜度数!';
		} else if (left_eye_ds) {
			//2.选择两个度数
			if (!left_eye_num) {
				msg_cart = '请选择眼镜数量!';
			}
		}
	} else if (n == 2) {
		//B.散光片
		if (! left_eye_ds) {
			//1.未选度数
			msg_cart = '请选择眼镜度数!';
		} else if (left_eye_ds) {
			//2.选择两个度数
			if (! (left_eye_num)) {
				msg_cart = '请选择眼镜数量!';
			}
			if (! left_eye_sg || ! left_eye_zw) {
				msg_cart = '请选择散光度数和轴位!';
			}
		}
	} else if (n == 3) {
		//C.框架和镜片
        right_eye_ds = $("#right_eye_ds").val();
        left_eye_ds = $("#left_eye_ds").val();
		if ( ! right_eye_ds || ! left_eye_ds || ! kj_tongju) {
			msg_cart = '请选择眼镜度数和瞳距!';
		} else {
			//选择散光,必须选择轴位
			if (right_eye_sg && ! right_eye_zw) {
				msg_cart = '请选择右眼散光轴位!';
			}
			if (left_eye_sg && ! left_eye_zw) {
				msg_cart = '请选择左眼散光轴位!';
			}
		}

	} else {
		//D.无度数属性商品和单买镜架
		if ( ! (goods_number)) {
			msg_cart = '请选择商品数量!';
		}
	}

	if (msg_cart == '') {
		//右眼度数|右眼数量|左眼度数|左眼数量|右眼散光|右眼轴位|左眼散光|左眼轴位|框架瞳距
		if ( ! right_eye_ds) right_eye_num = 0;
		if ( ! left_eye_ds) left_eye_num = 0;
		var degree_str = right_eye_ds+'|'+right_eye_num+'|'+left_eye_ds+'|'+left_eye_num+'|'+right_eye_sg+'|'+right_eye_zw+'|'+left_eye_sg+'|'+left_eye_zw+'|'+kj_tongju;
		//alert(degree_str); return false;
		var kj_glass_type = $("#get_glasses_type").val();

		$.ajax({
			type:'POST',
			url:'ajax_goods.php?act=add_to_cart',
			data:'&goods_id='+goods_id+'&goods_type='+n+'&goods_number='+goods_number+'&degree_str='+degree_str+'&glass_type='+kj_glass_type+'&m='+Math.random(),
			cache:false,
			success:
				function(d){
					/*var dd = eval('('+dd+')');
					var code = dd['info_code'];
					var msg  = dd['info_msg'];

					if(code==1){
						alert('恭喜您获得易视网优惠券，已存入您的易视账户中！');
					}else{
						alert(msg);
					}*/
					//alert(d);
                    $("#mydiv").show();
				}
		});

	} else {
		alert(msg_cart);
	}
}

//选择框架的镜片
$("#choose_glass img").click(function(){
     //移除所有图片选中效果
    $("#choose_glass img").removeClass("selected_glass");
    $("#choose_glass label").removeClass("selected_icon");
    //设置当前选中效果
    $(this).addClass("selected_glass");
    $(this).next().addClass("selected_icon");
    //获取属性值,判别不同的镜片
    $("#get_glasses_type").val($(this).attr("v"));
    var glass_type = parseInt($(this).attr("v"));
    var shop_price = $("#shop_price").val();
    var glass_price = 0;
    switch(glass_type)
    {
        case 2:
            glass_price = 50;break;
        case 3:
            glass_price = 100;break;
        case 4:
            glass_price = 320;break;
        case 5:
            glass_price = 560;break;
        case 6:
            glass_price = 780;break;
        default:
            glass_price = 0;break;
    }
    var kjjp_price = parseFloat(shop_price) + parseFloat(glass_price);

    $('#kj_price').text(kjjp_price+'.00');
});