<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black"/>
<meta content="telephone=no" name="format-detection"/>
<title><?php echo $this->_var['page_title']; ?></title>
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<link rel="stylesheet" type="text/css" href="css/common.css"/>
<link rel="stylesheet" type="text/css" href="css/category.css"/>
<style>

    #filters-box{font-family:"Microsoft Yahei";background:#f2f2f2;position:fixed;width:100%;height:100%;left:0;top:0;z-index:99;overflow-y:scroll;}
    .filter-header{color:#333;background:#fff;position:fixed;left:0;top:0;width:100%;border-bottom:1px solid #dfdfdf;}
    .filter-header h2{line-height:4rem;font-size:1.5rem;}
    .filter-header h2 span{font-size:1.5rem;color:#999;}
    .filter-container{padding-top:5rem;}
    .filter-option,.filter-item{padding:0 1rem;line-height:4rem;border-bottom:1px solid #dfdfdf;font-size:1.2rem;background:#fff;}
    .filter-option span{color:#999;padding-right:1rem;background:url(http://file.easeeyes.com/wap/images/select_arrow.png) no-repeat right center;background-size:auto 1rem;}
    .filter-clearbtn{font-size:0;position:fixed;bottom:0;left:0;width:100%;}
    .filter-clearbtn button{font-size:1.3rem;background:#000;width:50%;line-height:5rem;color:#fff;text-align:center;}
    #submit{background:#2BBDD6;}
    .filter-item{display:block;}
    .filter-back{width:1rem;height:4rem;background: transparent url("http://file.easeeyes.com/wap/images/arrow_left.png") no-repeat scroll 1rem center;background-size:1rem auto;padding:0 1rem;}
</style>
</head>
<body>
<?php echo $this->fetch('library/header.lbi'); ?>
<script type="text/javascript" src="<?php echo $this->_var['file_url']; ?>js/scrollpagination.js"></script>
<div class="content">
<div class="content sort">
<a id="default" class="xz">默认</a>
<a id="sales" class="down">销量</a>
<a id="price" class="up">价格</a>
<a id="nowChoose">筛选 <img src="http://file.easeeyes.com/wap/images/filter.png" style="height:1.3rem" /></a>
<a class="st" onclick="changeSt();" style="border-right:0px;width:10%;background: url('images/st.png') no-repeat 70% 50%;"></a>
    <!--<?php if ($this->_var['st'] == 1): ?>
    <a class="st" href="category.php?cat_id=<?php echo $this->_var['cat_id']; ?>" style="border-right:0px;width:10%;background: url('images/st.png') no-repeat 70% 50%;"></a>
    <?php else: ?>
    <a class="st" href="category.php?cat_id=<?php echo $this->_var['cat_id']; ?>&st=1" style="border-right:0px;width:10%;background: url('images/st.png') no-repeat 70% 50%;"></a>
    <?php endif; ?>-->
</div>
    <?php if ($this->_var['st'] != 1): ?>
    	<ul id="Scroll" class="content goods_list">
        <?php if ($this->_var['goods_list']): ?>
        <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'v');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['v']):
?>
        <li <?php if ($this->_var['v']['saving'] > 0): ?> class="active11_badge"<?php endif; ?>>
            <?php if ($this->_var['v']['is_by']): ?>
            <span class="icon-chris"></span>
            <?php endif; ?>
            <a class="list_a" href="goods.php?id=<?php echo $this->_var['v']['goods_id']; ?>">
                <div class="goods_list_thumb pull-left">
                    <img src="<?php echo $this->_var['img_url']; ?><?php echo $this->_var['v']['b2b_goods_thumb']; ?>"/>
                </div>
                <div class="goods_list_main">
                    <h2><?php echo $this->_var['v']['goods_name']; ?></h2>
                    <div class="goods_list_price">
                        ￥<?php echo $this->_var['v']['b2b_shop_price']; ?>
                    </div>
                </div>
            </a>
        </li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <?php else: ?>
        没有此类商品
        <?php endif; ?>
        </ul>
        <input id="st" value="0"  type="hidden"/>
    <?php else: ?>
        <div id="Scroll" class="content list_zong">
            <?php if ($this->_var['goods_list']): ?>
            <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'v');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['v']):
?>
            <div class="list_zong_left">
                <div <?php if ($this->_var['v']['saving'] > 0): ?> class="active11_badge_2"<?php endif; ?>>
                    <?php if ($this->_var['v']['is_by']): ?>
                    <span class="icon-chris"></span>
                    <?php endif; ?>
                    <a href="goods.php?id=<?php echo $this->_var['v']['goods_id']; ?>"><img src="<?php echo $this->_var['img_url']; ?><?php echo $this->_var['v']['b2b_goods_thumb']; ?>"/></a>
                </div>
                <div><a href="goods.php?id=<?php echo $this->_var['v']['goods_id']; ?>"><?php echo $this->_var['v']['goods_name']; ?></a></div>
                <div class="goods_list_price">
                        ￥<?php echo $this->_var['v']['b2b_shop_price']; ?>
                </div>
            </div>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <?php else: ?>
            没有此类商品
            <?php endif; ?>
        </div>
        <input id="st" value="1"  type="hidden"/>
    <?php endif; ?>
    <div id="lookMore"></div> 
    <div  class="loading" id="loading" style="display: none;"></div>
    <div class="loading" id="nomoreresults" style="display: none;">没有更多了.</div>
    <input type="hidden" id="pageStie" value="2" />
    <input type="hidden" id="cat_id" value="<?php echo $this->_var['cat_id']; ?>" />
    <input type="hidden" id="nomore" value="0" />
    <input type="hidden" id="sort" value="" />
    <input type="hidden" id="keyword" value="<?php echo $this->_var['keyword']; ?>" />
</div>

<div id="filters-box" class="filter filter-right" style="display:none;">
    <div id="filter-option-box">
        <div class="filter-header">
            <h2 class="text-center">
                <span class="pull-left filter-back" onclick="pop1.close();"></span>
                
                筛选
            </h2>
        </div>
        <div class="filter-container">
            <div class="filter-option" id="filter-option_001" data-key="brand"><span class="pull-right"><?php if ($this->_var['cur']['brand_id'] > 0): ?><?php echo $this->_var['cur']['brand_name']; ?><?php else: ?>全部<?php endif; ?></span>品牌</div>
            <?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'val');$this->_foreach['categories'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['categories']['total'] > 0):
    foreach ($_from AS $this->_var['val']):
        $this->_foreach['categories']['iteration']++;
?>
            <div class="filter-option" id="filter-option_<?php echo $this->_var['val']['id']; ?>" data-key="<?php echo $this->_var['val']['id']; ?>"><span class="pull-right">全部</span><?php echo $this->_var['val']['name']; ?></div>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <div class="filter-option" id="filter-option_002" data-key="price"><span class="pull-right">全部</span>价格</div>
        </div>
        <div class="filter-clearbtn text-center">
            <button id="clearfilter">重置</button>
            <button id="submit">确定</span>
        </div>
    </div>
    <div id="filter-detail-tpl-001" style="display:none">
        <div class="filter-header">
            <h2 class="text-center">
                品牌
            </h2>
        </div>
        <div class="filter-container">
            <div class="filter-item" data-item='{"key":"brand","value":"","name":"全部"}'>全部</div>
            <?php $_from = $this->_var['y_brands']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'val');$this->_foreach['brand'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['brand']['total'] > 0):
    foreach ($_from AS $this->_var['val']):
        $this->_foreach['brand']['iteration']++;
?>
            <div class="filter-item" data-item='{"key":"brand","value":"<?php echo $this->_var['val']['brand_id']; ?>","name":"<?php echo $this->_var['val']['brand_name']; ?>"}'><?php echo $this->_var['val']['brand_name']; ?></div>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </div>
    </div>
    <?php $_from = $this->_var['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'val');$this->_foreach['categories'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['categories']['total'] > 0):
    foreach ($_from AS $this->_var['val']):
        $this->_foreach['categories']['iteration']++;
?>
    <div id="filter-detail-tpl-<?php echo $this->_var['val']['id']; ?>" style="display:none">
        <div class="filter-header">
            <h2 class="text-center">
                <?php echo $this->_var['val']['name']; ?>
            </h2>
        </div>
        <div class="filter-container">
            <div class="filter-item" data-item='{"key":"<?php echo $this->_var['val']['id']; ?>","value":"","name":"全部"}'>全部</div>
            <?php $_from = $this->_var['val']['attr_values']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'v');$this->_foreach['attr'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['attr']['total'] > 0):
    foreach ($_from AS $this->_var['v']):
        $this->_foreach['attr']['iteration']++;
?>
            <div class="filter-item" data-item='{"key":"<?php echo $this->_var['val']['id']; ?>","value":"<?php echo $this->_var['v']; ?>","name":"<?php echo $this->_var['v']; ?>"}'><?php echo $this->_var['v']; ?></div>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </div>
    </div>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    <div id="filter-detail-tpl-002" style="display:none">
        <div class="filter-header">
            <h2 class="text-center">
                品牌
            </h2>
        </div>
        <div class="filter-container">
            <div class="filter-item" data-item='{"key":"price","value":"","name":"全部"}'>全部</div>
            <?php $_from = $this->_var['price']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'val');$this->_foreach['price'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['price']['total'] > 0):
    foreach ($_from AS $this->_var['val']):
        $this->_foreach['price']['iteration']++;
?>
            <div class="filter-item" data-item='{"key":"price","value":"<?php echo $this->_var['val']['id']; ?>","name":"<?php echo $this->_var['val']['name']; ?>"}'><?php echo $this->_var['val']['name']; ?></div>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </div>
    </div>
</div>
<script src="http://file.easeeyes.com/wap/js/wappop2.js"></script>
<script>
    //自适应代码
    (function(){
        var win=window||{},
                doc=document,
                root=doc.documentElement;
        function changeSize(){
            root.style.fontSize=parseInt(root.clientWidth)*20/640>20?"20px":parseInt(root.clientWidth)*20/640+"px";
        }
        if(doc.addEventListener){
            var evt="orientationchange" in win?"orientationchange":"resize";
            doc.addEventListener(evt,changeSize,false);
            doc.addEventListener("DOMContentLoaded",changeSize,false);
        }
    })();

    // 执行
    var pop1=new Pop({id:"filters-box"}),
            pop2=new Pop({id:"filter-option-box"});
    document.getElementById("nowChoose").addEventListener("click",function(){
        pop1.open();
        pop2.open();
    },false);

    /*
     *	key 为键值
     *	value 为值
     *	name 为显示数据
     */
    var options=Array.prototype.slice.call(document.querySelectorAll(".filter-option"),0),	// 筛选条件
            items=Array.prototype.slice.call(document.querySelectorAll(".filter-item"),0),	// 选项
            num,
            bn = "<?php echo $this->_var['cur']['brand_id']; ?>",
            filter={brand:bn};	// 筛选结果
    /*绑定条件和选项*/
    options.forEach(function(option){
        option.addEventListener("click",function(){
            num=this.id.split("_")[1];
            pop2.close();
            document.getElementById("filter-detail-tpl-"+num).style.display="block";
        },false);
    });
    /*保存数据和dom控制*/
    items.forEach(function(item){
        item.addEventListener("click",function(){
            var itemData=JSON.parse(this.dataset.item);
            filter[itemData.key]=itemData.value;	// 保存结果
            //console.log(filter)
            refresh(itemData);  // 选中数据前台显示
            document.getElementById("filter-detail-tpl-"+num).style.display="none";
            pop2.open();
        },false);
    })

    function refresh(item){
        options.forEach(function(option){
            if(option.dataset.key==item.key){
                option.getElementsByTagName("span")[0].innerHTML=item.name;
            }
        });
    }
    // 点击清除选项
    document.getElementById("clearfilter").addEventListener("click",function(){
        filter={};
        options.forEach(function(option){
            option.getElementsByTagName("span")[0].innerHTML="全部";
        });
    },false);
    // 点击确定按钮，filter为所有条件
    document.getElementById("submit").addEventListener("click",function(){
        //console.log(filter)
        $('#nomore').val(0);
        $('#nomoreresults').hide();
        $.ajax({
            type : "get",
            async:false,
            url : "category.php?act=filter",
            data:{cat_id:<?php echo $this->_var['fcat_ids']; ?>,attr:JSON.stringify(filter),st:$("#st").val()},
            success : function(msg){
                //alert(msg);return false;
                if(msg != ''){
                    $("#Scroll").html(msg);
                }else{
                    $("#Scroll").html("没有此类产品");
                }
                pop1.close();
                $('#cat_id').val(<?php echo $this->_var['fcat_ids']; ?>);
            }
        });
    },false);

    // 切换展示方式
    function changeSt(){
        $("#nomore").val(0);
        $('#nomoreresults').hide();
        var st = $("#st").val();
        if(st != 1){
            st =1;
        }else{
            st =0;
        }
        $("#st").val(st);
        $.ajax({
            type : "get",
            async:false,
            url : "category.php?act=stp",
            data:{st:st,sort:$("#sort").val(),cat_id:$("#cat_id").val(),keyword:$("#keyword").val(),attr:$("#filter").val()},
            beforeSend :function(msg){

            },
            success : function(msg){
                $("#Scroll").html(msg);
            }
        });
    }
</script>
<?php echo $this->fetch('library/footer.lbi'); ?>
<script type="text/javascript" src="/wap/js/category.js"></script>
<script>
//自适应代码
(function(){
    var win=window||{},
        doc=document,
        root=doc.documentElement;
    function changeSize(){
        root.style.fontSize=parseInt(root.clientWidth)*20/640>20?"20px":parseInt(root.clientWidth)*20/640+"px";
    }
    if(doc.addEventListener){
        var evt="orientationchange" in win?"orientationchange":"resize"; 
        doc.addEventListener(evt,changeSize,false);
        doc.addEventListener("DOMContentLoaded",changeSize,false);
    }
})();    
</script>
</body>
</html>
