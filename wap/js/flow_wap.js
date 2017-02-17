
//优惠券展示
$("#use_youhui").toggle(function(){
		$(".order_youhui_quan").show();
		},function(){
		$(".order_youhui_quan").hide();
})


//显示/隐藏列表
$("#choose_address").click(function(){
    $(".order_info").hide();
    $("#address_list").show();
})

//列表选择地址
function choose_address(id,cons,pro,city,dist,addr){
    var replace_html = $("#address_"+id+" div:first").html();
    $.ajax({
        type: "POST",
        url: "flow_ajax.php",
        data: {
            action:'save_address',
            address_id:id,
            consignee:cons,
            province:pro,
            city:city,
            district:dist,
            address:addr
        },
        success:function(d){
            $(".replace #choose_address").html(replace_html);
            $("#address_id").val(d);
            $("#pro_choose").val(pro);
            $("#city_choose").val(city);
            $("#dist_choose").val(dist);
            change_shipping();
            $(".order_info").show();
            $("#address_list").hide();
        },
        error:function(d){
            alert('error:'+d.toSource());
        }
    });
    /*$(".replace #choose_address").html(replace_html);
    $("#address_id").val(id);
    $("#pro_choose").val(pro);
    $("#city_choose").val(city);
    $("#dist_choose").val(dist);
    change_shipping();
    $(".order_info").show();
    $("#address_list").hide();*/
}
//展开新增地址
function add_new_show(){
    $(".order_info").hide();
    $("#address_list").hide();
    $(".append_address").show();
}
//判断是否已选支付方式
$("#kuaidi_select").click(function(){
    if($('#choose_address').text().trim() == '点击添加收货地址'){
        alert('请保存您的收货人信息!');
        $("#choose_address").focus();
        return false;

    }else if( $('#payment').val() == ''){
        alert('请保存您的支付方式!');
        $("#payment").focus();
        return false;

    }else{
        $(".order_info").hide();
        $(".select_kuaidi").show();
    }
})

//根据上级地区,获取下级地区
function getDistrict(level, parent) {
	if (parseInt(level) > 0 && parseInt(parent) > 0) {
		$.ajax({
			type: "POST",
			url: "ajax_util.php?act=get_district_option",
			data: {region_type:level, parent_id:parent},
			success:function(d){
				//alert(d);
				if (level == 2) {
					$("#selCities").html("<option value='0'>请选择城市</option>" + d);
					$("#selDistricts").html("<option value='0'>请选择区县</option>");
				} else {
					$("#selDistricts").html("<option value='0'>请选择区县</option>" + d);
				}
			},
			error:function(d){
				alert('error:'+d.toSource());
			}
		});
	}
}

//ajax提交表单: 新建收货人地址数据
		function checkSubmitJ(){
            if ($("#consignee").val() =="" )
            {
            alert("请输入收货人姓名！");
            $("#consignee").select();
            return false;
            }
			if( $('#selCountries').val() == 0 || $('#selProvinces').val() == 0 ||$('#selCities').val() == 0 ||$('#selDistricts').val() == 0){
                alert("请选择好配送区域!");
				return false;				
			}
			if( $('#address').val() == '' ){
                alert("请输入街道地址!");
                $("#address").select();
				return false;
			}
			//联系电话
			if( $('#tel').val() == ''){
                alert("请输入电话或手机!");
                $("#tel").select();
				return false;
			}
			//邮箱
			if( $('#email').val() == ''){
                alert("请输入电子邮箱!");
                $("#email").select();
				return false;
			}
            
            $.ajax({
    			type: "POST",
    			url: "flow_ajax.php",
    			data: {
    			 action:'add_new_address', 
                 consignee:$("#consignee").val(),
                 province:$("#selProvinces").val(),
                 city:$("#selCities").val(),
                 district:$("#selDistricts").val(),
                 address:$("#address").val(),
                 tel:$("#tel").val(),
                 email:$("#email").val(),
                 },
    			success:function(d){
    				if(d){
                       $("#address_id").val(d); 
    				   //替换选中地址
                       var replace_html_choose=
                       '<div class="order_name">'+$("#consignee").val()+'<span>'+$("#tel").val()+'</span></div><div>'+$("#selProvinces").val()+$("#selCities").val()+$("#selDistricts").val()+$("#address").val()+'</div>';
                       //增加地址列表
                       var replace_html_list =
                       '<div class="address" id="address_'+d+'" onclick="choose_address('+d+','+$("#selProvinces").val()+','+$("#selCities").val()+','+$("#selDistricts").val()+');"><div class="order_add_left"><div class="order_name">'+$("#consignee").val()+'<span style="float:right">'+$("#tel").val()+'</span></div><div>'+$("#selProvinces").val()+$("#selCities").val()+$("#selDistricts").val()+$("#address").val()+'</div></div></div></div>';
                       
                        $(".replace #choose_address").html(replace_html_choose);
                        $("#address_list").append(replace_html_list);
                        
                        $("#pro_choose").val($("#selProvinces").val());
                        $("#city_choose").val($("#selCities").val());
                        $("#dist_choose").val($("#selDistricts").val());
                        change_shipping();
                        $(".order_info").show();
                        $(".append_address").hide();
                    }
    			}
    		});

		}
//更变配送方式和费用
function change_shipping(){
    $.ajax({
        url:'flow_ajax.php?action=shipping',
        data:'&pro='+$("#pro_choose").val()+'&city='+$("#city_choose").val()+'&dist='+$("#dist_choose").val()+'&flow_type='+$("#flow_type").val()+'&m='+Math.random(),
        cache:false,
        success:function(dd){
            //【dd】服务器端返回新的配送方式数据
            dd = eval('('+dd+')');
            var len = dd.length;
            //清空以前的配送方式
            $(".select_kuaidi div").detach();
            //根据返回结果创建新的配送方式
            $.each(dd,function(i){
               if($("#payment").val() != 3 && dd[i].shipping_id != 9){  // 不是货到付款的支付方式只显示快递配送方式
                   var a = '<div style="display: none"><input type="radio" name="shipping" id="kd'+dd[i].shipping_id+'" value="'+dd[i].shipping_id+'" supportCod="'+dd[i].support_cod+'" onclick="getRadio(this)"><label for="kd'+dd[i].shipping_id+'">'+dd[i].shipping_name+' '+dd[i].shipping_desc+'</label></div>';
               }else if($("#payment").val() == 3 && dd[i].shipping_id != 8){  // 货到付款只显示宅急送配送方式
                   var a = '<div style="display: none"><input type="radio" name="shipping" id="kd'+dd[i].shipping_id+'" value="'+dd[i].shipping_id+'" supportCod="'+dd[i].support_cod+'" onclick="getRadio(this)"><label for="kd'+dd[i].shipping_id+'">'+dd[i].shipping_name+' '+dd[i].shipping_desc+'</label></div>';
               }else{
                   var a = '<div><input type="radio" name="shipping" id="kd'+dd[i].shipping_id+'" value="'+dd[i].shipping_id+'" supportCod="'+dd[i].support_cod+'" onclick="getRadio(this)"><label for="kd'+dd[i].shipping_id+'">'+dd[i].shipping_name+' '+dd[i].shipping_desc+'</label></div>';
               }
              $(".select_kuaidi").append(a);
            });

        }
    });
}

//选择支付方式时改变配送方式
$("#payment").change(function(){
    $("input:radio[name=shipping]").attr("checked",false);
    $("#kuaidi_select div:first").html('请重新选择配送方式');

   var ps_id = $(this).val();
   if(ps_id=='3'){
       $("#kd8").parent().css('display','block');
       $("#kd9").parent().css('display','none');
       $("#kd12").parent().css('display','none');
       changeByPayment(8);
   }else{
       $("#kd8").parent().css('display','none');
       $("#kd9").parent().css('display','block');
       $("#kd12").parent().css('display','none');
       changeByPayment(9);
   } 
})

function changeByPayment(shipping_id){
    var text = $("#kd" +shipping_id).next().text();
    var a = '<input type="hidden" name="shipping" value="' + shipping_id + '" />';
    $("#kuaidi_select div:first").html(text);
    $("#kuaidi_select div:first").append(a);
    Ajax.call('flow.php?step=select_shipping', 'shipping=' + shipping_id, orderShippingSelectedResponse, 'GET', 'JSON');
}

function getRadio(obj){
    var choose_shipping = $("input[name=shipping]:checked").attr("id");
    var shipping_id = $("input[name=shipping]:checked").val();
    var text1 = $("#"+choose_shipping).next().text();
    var shipping = obj.value;
    var a = '<input type="hidden" name="shipping" value="' + shipping_id + '" />';

    $("#kuaidi_select div:first").html(text1);
    $("#kuaidi_select div:first").append(a);
    $(".order_info").show();
    $(".select_kuaidi").hide();
    //var supportCod = obj.attributes['supportCod'].value + 0;
    //var now = new Date();
    Ajax.call('flow.php?step=select_shipping', 'shipping=' + shipping, orderShippingSelectedResponse, 'GET', 'JSON');

}
/**
 *
 */
function orderShippingSelectedResponse(result)
{
    if(result.need_insure)
    {
        try
        {
            document.getElementById('ECS_NEEDINSURE').checked = true;
        }
        catch (ex)
        {
            alert(ex.message);
        }
    }

    try
    {
        if (document.getElementById('ECS_CODFEE') != undefined)
        {
            document.getElementById('ECS_CODFEE').innerHTML = result.cod_fee;
        }
    }
    catch (ex)
    {
        alert(ex.message);
    }

    orderSelectedResponse(result);
}
/* *
 * 回调函数
 */
function orderSelectedResponse(result)
{
    if (result.error)
    {
        alert(result.error);
        location.href = './';
    }

    try
    {
        var layer = document.getElementById("ECS_ORDERTOTAL");

        layer.innerHTML = (typeof result == "object") ? result.content : result;

        if(result.payment != undefined)
        {
            var surplusObj = document.forms['theForm'].elements['surplus'];
            if(surplusObj != undefined)
            {
                surplusObj.disabled = result.pay_code == 'balance';
            }
        }
    }
    catch (ex) { }
}

//-------------------------单击去结算按钮提交表单【防止重复提交】---------------------------------||
$('#form_cart').submit(function(){
    var kuaidi_select = $("#kuaidi_select div:first").text();
    //alert(kuaidi_select);
    if($('#choose_address').text().trim() == '点击添加收货地址'){
        alert('请保存您的收货人信息!');
        $("#choose_address").focus();
        return false;

    }else if( $('#payment').val() == ''){
        alert('请保存您的支付方式!');
        $("#payment").focus();
        return false;

    }else if(kuaidi_select == "请重新选择配送方式" || kuaidi_select == "请选择配送方式"){
        alert('请保存您的配送方式!');
        $("#payment").focus();
        return false;

    }else{
        return true;
    }
    //更换提交按钮图片，并设置按钮不可用
    $('#cart_submit').attr({src:"themes/default/images/cart/submit_wait.gif",disabled:"true"});

});