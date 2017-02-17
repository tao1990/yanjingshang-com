//ajax����
    $("#default").click(function(){
        $(this).addClass("xz").siblings().removeClass('xz');
        ajax_get_goods(1);
    })
    
    $("#sales").click(function(){
        $(this).addClass("xz").siblings().removeClass('xz');
    }).toggle(
    function(){ 
        ajax_get_goods(2);
        $(this).addClass("down"); 
        $(this).removeClass("up"); 
    },function(){ 
        ajax_get_goods(3);
        $(this).addClass("up"); 
        $(this).removeClass("down"); 
    })

    $("#price").click(function(){
        $(this).addClass("xz").siblings().removeClass('xz');
    }).toggle(function(){ 
        ajax_get_goods(4);
        $(this).addClass("up"); 
        $(this).removeClass("down"); 
    },function(){ 
        ajax_get_goods(5);
        $(this).addClass("down"); 
        $(this).removeClass("up"); 
    })      

//ajax�������»�ȡ��Ʒ
function ajax_get_goods(sort){
    $("#sort").val(sort);
    $("#pageStie").val(2);
    $("#nomore").val(0);
    $.ajax({
        type : "get",
        async:false,
        url : "lab.php",
        data:{sort:sort,lab_id:$("#lab_id").val(),st:$("#st").val(),keyword:$("#keyword").val()},
		beforeSend :function(msg){
			
		},
        success : function(msg){ 
			$("#Scroll").html(msg);
        }
    });
}

//ajax������ȡ��Ʒ
$(window).scroll(function(e){
    if($('#nomore').val()==0){
        if ($(window).scrollTop() + $(window).height() > $("#lookMore").offset().top){  
            $.ajax({
            type : "get",
            async:false,
            url : "lab.php?act=more",
            data:{page:$("#pageStie").val(),lab_id:$("#lab_id").val(),sort:$("#sort").val(),st:$("#st").val(),keyword:$("#keyword").val()},
    		beforeSend :function(msg){
    	           $('#loading').fadeIn(500);
    		},
            success : function(msg){ 
                    $('#loading').fadeOut(500);
                if(msg){
                    $("#Scroll").append(msg);//�滻Ϊ�첽���� 
                    var  pageStie= parseInt($('#pageStie').val())+parseInt(1); 
                    $('#pageStie').val(pageStie);
                }else{
                    $('#nomore').val(1);
                }
            }
        });
        }
    }else{
        $('#nomoreresults').fadeIn(1000);
    }
     
}); 