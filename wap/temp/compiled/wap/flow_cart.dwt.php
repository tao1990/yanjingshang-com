<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black"/>
<meta content="telephone=no" name="format-detection"/>
<title><?php echo $this->_var['page_title']; ?></title>
<meta name="Keywords" content="<?php echo $this->_var['keywords']; ?>" />
<meta name="Description" content="<?php echo $this->_var['description']; ?>" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
<link rel="icon" href="animated_favicon.gif" type="image/gif" />
<link href="<?php echo $this->_var['image_url']; ?>wap/css/common.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->_var['image_url']; ?>wap/css/car.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->_var['ecs_css_path']; ?>" rel="stylesheet" type="text/css" />

<style>
.block{width:32rem;margin:auto;font-family:"Microsoft Yahei Light","Microsoft Yahei",arial;font-size:1.1rem;background:#fff;}
/****折叠style***/
.fold-main{display:block;}
.fold-bar[data-fold="true"]+.fold-main{display:none;}
</style>
</head>
<body>
<div class="pop_shadow"></div>
<?php echo $this->fetch('library/header.lbi'); ?>
<script type="text/javascript" src="<?php echo $this->_var['image_url']; ?>wap/js/jquery.form.js"></script>
<?php echo $this->smarty_insert_scripts(array('files'=>'common.js,region.js,shopping_flow.js,flow.js')); ?>

<?php if (count ( $this->_var['goods_list'] ) == 0): ?>
<div class="container page-cart">
    
    <?php if ($_SESSION['user_id'] == 0): ?>
    <div class="p-cart-title">
        <p>登陆后可同步电脑与手机购物车中的商品　<a href="user.php" class="p-btn">登 陆</a></p>
    </div>
    <?php endif; ?>
    
    <div class="p-cart-main">
        <div class="cart-image">
            <img src="http://file.easeeyes.com/wap/images/cart_null.jpg" />
        </div>
        <h3>购物车肚子空空</h3>
        <p>购物车还是空的，去挑几件中意的商品吧！</p>
        <a href="category.php" class="p-btn">去逛逛</a>
    </div>
    
    <div class="p-cart-foot">购物满68元包邮 <a href="category.php" class="">去凑凑看</a></div>
    
    <div class="p-cart-tj">
        <h2 class="tj-title">热销推荐</h2>
        <div class="tj-main" id="cart_slider_box_01">
            <ul class="tj-main-ulist clearfix" id="cart_slider_01">
                <?php $_from = $this->_var['hot_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['goods']):
        $this->_foreach['foo']['iteration']++;
?>
                <li>
                    <a href="<?php echo $this->_var['goods']['url']; ?>">
                        <div class="tj-images"><img src="<?php echo $this->_var['img_url']; ?><?php echo $this->_var['goods']['original_img']; ?>" /></div>
                        <p><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></p>
                        <span>￥<?php echo $this->_var['goods']['shop_price']; ?> <small>￥<del><?php echo $this->_var['goods']['market_price']; ?></del></small></span>
                        <button>立即购买</button>
                    </a>
                </li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </ul>
        </div>
    </div>
</div>
<?php else: ?>
<div id="main" class="md-jjg">
    
    <div id="outdiv">
        <ul class="th_h"><li class="th_h_p">你还可换购以下商品：</li><?php if ($this->_var['cart_fav_goods'] == 0): ?><li class="th_h_txt"><?php echo $this->_var['gift_list']['0']['act_name']; ?></li><?php endif; ?></ul>
        <?php if ($this->_var['cart_fav_goods'] > 0): ?>
        <?php $_from = $this->_var['gift_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'g');$this->_foreach['fn'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fn']['total'] > 0):
    foreach ($_from AS $this->_var['g']):
        $this->_foreach['fn']['iteration']++;
?>
        <div id="div<?php echo $this->_var['g']['act_id']; ?><?php echo $this->_var['g']['id']; ?>" class="th_pan">
            <ul class="th_g_h fold-bar"><li class="th_g_pri">+<?php echo $this->_var['g']['0']['price']; ?>元赠</li><li class="th_g_name"><span class="th_g_sp"><?php echo $this->_var['g']['0']['act_name']; ?></span></li></ul>
            
            <div class="fold-main">
                <?php $_from = $this->_var['g']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'go');$this->_foreach['gfn'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['gfn']['total'] > 0):
    foreach ($_from AS $this->_var['go']):
        $this->_foreach['gfn']['iteration']++;
?>
                <div class="th_g_con clearfix">
                    <div class="th_g_a pull-left"><a href="goods.php?id=<?php echo $this->_var['go']['id']; ?>" title="<?php echo $this->_var['go']['name']; ?>"><img src="<?php echo $this->_var['img_url']; ?><?php echo $this->_var['go']['goods_img']; ?>" width="100" height="100"/></a></div>
                    <div style="float:left; background-color:#f9f9f9;">
                        <div style="width:100%;line-height:2rem; text-align:left; margin-top:5px; overflow:hidden;">
                            <span style="display:inline-block;color:#999; "><?php echo $this->_var['go']['name']; ?></span>
                            <input type="hidden" id="gift_len" value="<?php echo $this->_var['gift_len']; ?>" />
                            <input type="hidden" id="cart_len" value="<?php echo $this->_var['cart_len']; ?>" />
                            <input type="hidden" id="gift_number<?php echo $this->_var['go']['id']; ?>" value="<?php echo $this->_var['go']['number']; ?>" />
                            <input type="hidden" id="fav_can_add" value="<?php echo $this->_var['fav_can_add']; ?>"/>
                            <input type="hidden" id="fav_can_add2" value="<?php echo $this->_var['fav_can_add2']; ?>"/>
                        </div>
                        <div style="width:100%;">
                            <div style="text-align:left;">
                                <?php if ($this->_var['go']['goods_ds']): ?>
                                <input type="hidden" id="kind<?php echo $this->_var['go']['id']; ?>" value="0" />
                                <?php if ($this->_var['go']['number'] == 1): ?>
                                <div style="">眼镜度数：<select id="ds<?php echo $this->_var['go']['act_id']; ?><?php echo $this->_var['go']['id']; ?>" class="gift_ds_0"><option value="">请选择</option>
                                    <?php $_from = $this->_var['go']['goods_ds']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'li');if (count($_from)):
    foreach ($_from AS $this->_var['li']):
?><option value="<?php if ($this->_var['li']['canbuy']): ?><?php echo $this->_var['li']['val']; ?><?php else: ?><?php endif; ?>"><?php echo $this->_var['li']['val']; ?><?php echo $this->_var['li']['status']; ?></option><?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                </select></div>
                                <?php else: ?>
                                <div style="">左眼度数：<select id="zselect<?php echo $this->_var['go']['act_id']; ?><?php echo $this->_var['go']['id']; ?>" class="gift_ds_1"><option value="">请选择</option>
                                    <?php $_from = $this->_var['go']['goods_ds']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'li');if (count($_from)):
    foreach ($_from AS $this->_var['li']):
?><option value="<?php if ($this->_var['li']['canbuy']): ?><?php echo $this->_var['li']['val']; ?><?php else: ?><?php endif; ?>"><?php echo $this->_var['li']['val']; ?><?php echo $this->_var['li']['status']; ?></option><?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                </select></div>
                                <div style="">右眼度数：<select id="yselect<?php echo $this->_var['go']['act_id']; ?><?php echo $this->_var['go']['id']; ?>" class="gift_ds_2"><option value="">请选择</option>
                                    <?php $_from = $this->_var['go']['goods_ds']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'li');if (count($_from)):
    foreach ($_from AS $this->_var['li']):
?><option value="<?php if ($this->_var['li']['canbuy']): ?><?php echo $this->_var['li']['val']; ?><?php else: ?><?php endif; ?>"><?php echo $this->_var['li']['val']; ?><?php echo $this->_var['li']['status']; ?></option><?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                </select></div>
                                <?php endif; ?>
                                <?php elseif ($this->_var['go']['id'] == 1542): ?>
                                <input type="hidden" id="kind<?php echo $this->_var['go']['id']; ?>" value="0" />
                                <div style="">眼镜度数：<select id="ds<?php echo $this->_var['go']['act_id']; ?><?php echo $this->_var['go']['id']; ?>" class="gift_ds_0">
                                    <option value="">请选择</option>
                                    <option value="+1.00">+1.00</option>
                                    <option value="+1.50">+1.50</option>
                                    <option value="+2.00">+2.00</option>
                                    <option value="+2.50">+2.50</option>
                                    <option value="+3.00">+3.00</option>
                                    <option value="+3.50">+3.50</option>
                                    <option value="+4.00">+4.00</option>
                                </select></div>
                                <?php else: ?>
                                <input type="hidden" id="kind<?php echo $this->_var['go']['id']; ?>" value="1" />
                                <?php endif; ?>
                            </div>
                            <div style="">
                                <div style="">易视价：￥<?php echo $this->_var['go']['shop_price']; ?></div>
                                <div style="">特惠价：￥<font class="red"><?php echo $this->_var['go']['price']; ?></font></div>
                                <input type="hidden" id="gift_price<?php echo $this->_var['go']['id']; ?>" value="<?php echo $this->_var['go']['price']; ?>" />
                            </div>
                            <div style="">
                                <a href="javascript:add_fav(<?php echo $this->_var['go']['act_id']; ?>,<?php echo $this->_var['go']['id']; ?>)"><img src="templates/images/add_tocart.gif" width="100" height="22" alt="" style="margin-top:15px;"/></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </div>
        </div>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <?php endif; ?>
    </div>
    <div class="space45"></div>

    <?php if ($this->_var['favourable_list']): ?>
    <div class="block" style="display:none">
        <div class="flowBox">
            <h6><span><?php echo $this->_var['lang']['label_favourable']; ?></span></h6>
            <?php $_from = $this->_var['favourable_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'favourable');if (count($_from)):
    foreach ($_from AS $this->_var['favourable']):
?>
            <form action="flow.php" method="post">
                <table width="99%" align="center" border="0" cellpadding="5" cellspacing="1" bgcolor="#dddddd">
                    <tr>
                        <td align="right" bgcolor="#ffffff"><?php echo $this->_var['lang']['favourable_name']; ?></td>
                        <td bgcolor="#ffffff"><strong><?php echo $this->_var['favourable']['act_name']; ?></strong></td>
                    </tr>
                    <tr>
                        <td align="right" bgcolor="#ffffff"><?php echo $this->_var['lang']['favourable_period']; ?></td>
                        <td bgcolor="#ffffff"><?php echo $this->_var['favourable']['start_time']; ?> --- <?php echo $this->_var['favourable']['end_time']; ?></td>
                    </tr>
                    <tr>
                        <td align="right" bgcolor="#ffffff"><?php echo $this->_var['lang']['favourable_range']; ?></td>
                        <td bgcolor="#ffffff"><?php echo $this->_var['lang']['far_ext'][$this->_var['favourable']['act_range']]; ?><br />
                            <?php echo $this->_var['favourable']['act_range_desc']; ?></td>
                    </tr>
                    <tr>
                        <td align="right" bgcolor="#ffffff"><?php echo $this->_var['lang']['favourable_amount']; ?></td>
                        <td bgcolor="#ffffff"><?php echo $this->_var['favourable']['formated_min_amount']; ?> --- <?php echo $this->_var['favourable']['formated_max_amount']; ?></td>
                    </tr>
                    <tr>
                        <td align="right" bgcolor="#ffffff"><?php echo $this->_var['lang']['favourable_type']; ?></td>
                        <td bgcolor="#ffffff">
                            <span class="STYLE1"><?php echo $this->_var['favourable']['act_type_desc']; ?></span>
                            <?php if ($this->_var['favourable']['act_type'] == 0): ?>
                            <?php $_from = $this->_var['favourable']['gift']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'gift');if (count($_from)):
    foreach ($_from AS $this->_var['gift']):
?><br />
                            <input type="checkbox" value="<?php echo $this->_var['gift']['id']; ?>" name="gift[]" />
                            <a href="goods.php?id=<?php echo $this->_var['gift']['id']; ?>" class="f6"><?php echo $this->_var['gift']['name']; ?></a> [<?php echo $this->_var['gift']['formated_price']; ?>]
                            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                            <?php endif; ?>          </td>
                    </tr>
                    <?php if ($this->_var['favourable']['available']): ?>
                    <tr>
                        <td align="right" bgcolor="#ffffff">&nbsp;</td>
                        <td align="center" bgcolor="#ffffff"><input type="image" src="themes/default/images/bnt_cat.gif" alt="Add to cart"  border="0" /></td>
                    </tr>
                    <?php endif; ?>
                </table>
                <input type="hidden" name="act_id" value="<?php echo $this->_var['favourable']['act_id']; ?>" />
                <input type="hidden" name="step" value="add_favourable" />
            </form>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </div>
    </div>
    <?php endif; ?>
    
</div>
<div class="block mt10 mb20">

    <!--<div class="go_jiesuan">
       <div class="go_jiesuan_left">
          <div>商品总价（不含运费）<span id="cart_total"><?php echo $this->_var['shopping_moneyn']; ?></span></div>
          <div class="cart1_end">
            <span id="freepx"><?php if ($this->_var['goods_pricex'] != - 1): ?>&lt;!&ndash;<?php if ($this->_var['goods_pricex']): ?>&ndash;&gt;<span class="note1">您还差<font class="red"><?php echo $this->_var['goods_pricex']; ?>元</font>就可以得到免费配送</span>
            &lt;!&ndash;<?php else: ?>&ndash;&gt;<span class="note1">购物已超过<?php echo $this->_var['base_line']; ?>元，您可以享受免费快递。</span>&lt;!&ndash;<?php endif; ?>&ndash;&gt;<?php endif; ?></span>
          </div>
       </div>
       <div class="go_jiesuan_right"><a href="flow.php?step=checkout">去结算 >></a></div>
       <div class="clear"></div>
    </div>-->
    <div class="cart1_end">
        <?php if ($this->_var['discount'] > 0): ?><span style="display:inline-block; text-align:left; padding: 0 10px"><?php echo $this->_var['your_discount']; ?>元</span><?php endif; ?>
    </div>
    <div class="go_content">
    <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
       <div class="go_content_one" id="datatb">
          <div class="go_content_one_left">
               <div class="go_content_top">
                  <div class="detail_img">
    
                   <a href="goods.php?id=<?php echo $this->_var['goods']['goods_id']; ?>">
                    <img src="<?php echo $this->_var['img_url']; ?><?php echo $this->_var['goods']['goods_thumb']; ?>" alt="<?php echo $this->_var['goods']['goods_name']; ?>" width="80" height="80"/>
                   </a>
                  </div>
                  <div class="detail_intro">
                      <?php echo $this->_var['goods']['promotion_type']; ?>
    
                      <?php if ($this->_var['show_goods_attribute'] == 1 && $this->_var['goods']['extension_code'] != 'package_buy' && $this->_var['goods']['extension_code'] != 'tuan_buy' && $this->_var['goods']['extension_code'] != 'miaosha_buy'): ?><?php endif; ?> <!--$goods.goods_attr|nl2br}<br/>-->
                      <?php if ($this->_var['goods']['extension_code'] != 'package_buy' && $this->_var['goods']['extension_code'] != 'group_buy' && $this->_var['goods']['extension_code'] != 'exchange_buy' && $this->_var['goods']['extension_code'] != 'miaosha_buy' && $this->_var['goods']['extension_code'] != 'tuan_buy'): ?>
                      <?php if ($this->_var['goods']['is_gift'] > 0 && $this->_var['goods']['goods_price'] == '0.00'): ?><span class="redf">（赠品）</span><?php endif; ?>
                      <?php if ($this->_var['goods']['is_gift'] > 0 && $this->_var['goods']['goods_price'] != '0.00'): ?><span class="redf">（特惠商品）</span><?php endif; ?>
                      <?php if ($this->_var['goods']['extension_code'] == 'exchange'): ?><span class="redf">（积分兑换商品）</span><?php endif; ?>
                      <a href="goods.php?id=<?php echo $this->_var['goods']['goods_id']; ?>" class="f6"><?php echo $this->_var['goods']['goods_name']; ?></a>
                      <?php elseif ($this->_var['goods']['extension_code'] == 'package_buy'): ?>
                      <a href="goods.php?id=<?php echo $this->_var['goods']['goods_id']; ?>" onclick="setSuitShow(<?php echo $this->_var['goods']['goods_id']; ?>)" class="f6"><span class="redf">（礼包）</span><?php echo sub_str($this->_var['goods']['goods_name'],15); ?></a>
                      <?php elseif ($this->_var['goods']['extension_code'] == 'group_buy'): ?>
                      <a href="goods.php?id=<?php echo $this->_var['goods']['goods_id']; ?>" class="f6"><span class="redf">（组合购买）</span><?php echo sub_str($this->_var['goods']['goods_name'],15); ?></a>
                      <?php elseif ($this->_var['goods']['extension_code'] == 'exchange_buy'): ?>
                      <a href="goods.php?id=<?php echo $this->_var['goods']['goods_id']; ?>" class="f6"><span class="redf">（积分折扣购买）</span><?php echo sub_str($this->_var['goods']['goods_name'],15); ?></a>
                      <?php elseif ($this->_var['goods']['extension_code'] == 'miaosha_buy'): ?>
                      <a href="snatchs.php?id=<?php echo $this->_var['goods']['goods_id']; ?>" class="f6"><span class="redf">（秒杀）</span><?php echo sub_str($this->_var['goods']['goods_name'],15); ?></a>
                      <?php elseif ($this->_var['goods']['extension_code'] == 'tuan_buy'): ?>
                      <a href="goods.php?id=<?php echo $this->_var['goods']['goods_id']; ?>" class="f6"><span class="redf">（团购）</span><?php echo sub_str($this->_var['goods']['goods_name'],15); ?></a>
                      <?php endif; ?>
        

                <?php if ($this->_var['goods']['goods_id'] > 0 && $this->_var['goods']['promotion_type'] == '1'): ?>
                    <?php if ($this->_var['goods']['parent_id'] == 0): ?>
                    <p>团购价：<span><?php echo $this->_var['goods']['goods_price']; ?></span></p>
                    <p>小计：  <span id="sum_<?php echo $this->_var['goods']['rec_id']; ?>"><?php echo $this->_var['goods']['subtotal']; ?></span></p>
                    <?php else: ?>
                    <p>单价：  <span><?php echo $this->_var['goods']['goods_price']; ?></span></p>
                    <p>小计：  <span id="sum_<?php echo $this->_var['goods']['rec_id']; ?>"><?php echo $this->_var['goods']['subtotal']; ?></span></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p>单价：  <span><?php echo $this->_var['goods']['goods_price']; ?></span></p>
                    <p>小计：  <span id="sum_<?php echo $this->_var['goods']['rec_id']; ?>"><?php echo $this->_var['goods']['subtotal']; ?></span></p>
                <?php endif; ?>
                  </div>
                  <div class="clear"></div>
               </div>
               <div class="go_content_bottom">
               
               <?php if ($this->_var['goods']['extension_code'] != 'exchange' && $this->_var['goods']['extension_code'] != 'exchange_buy' && $this->_var['goods']['extension_code'] != 'group_buy' && $this->_var['goods']['extension_code'] != 'miaosha_buy' && $this->_var['goods']['extension_code'] != 'unchange' && $this->_var['goods']['is_kj'] != 1 && $this->_var['goods']['extension_code'] != 'source_buy'): ?>
                   <?php if ($this->_var['goods']['is_gift'] == 0 && $this->_var['goods']['zcount'] == 0 && $this->_var['goods']['ycount'] == 0): ?>
                   <?php if ($this->_var['goods']['extension_code'] == 'package_buy' && $this->_var['goods']['goods_attr_id'] != ''): ?>
                   <div class="go_con_bottom_one">
                       <div class="go_con_bott_one_left">
                           <?php if ($this->_var['goods']['goods_attr']): ?><p>眼镜度数：<?php echo $this->_var['goods']['goods_attr']; ?></p><?php endif; ?>
                       </div>
                       <?php if ($this->_var['goods']['goods_sn'] == 1): ?>
                       <div class="go_con_bott_one_right">
                           <a onclick="reducep(<?php echo $this->_var['goods']['rec_id']; ?>)"><img src="<?php echo $this->_var['image_url']; ?>wap/images/-.png"/></a>
                           <input type="text" onchange="changep(<?php echo $this->_var['goods']['rec_id']; ?>)" readonly="readonly" name="package[<?php echo $this->_var['goods']['rec_id']; ?>]" id="goods_number_<?php echo $this->_var['goods']['rec_id']; ?>" value="<?php echo $this->_var['goods']['goods_number']; ?>" />
                           <a onclick="addp(<?php echo $this->_var['goods']['rec_id']; ?>)"><img src="<?php echo $this->_var['image_url']; ?>wap/images/+.png"/></a>
                       </div>
                       <?php else: ?>
                       <div class="go_con_bott_one_right">
                           <img src="<?php echo $this->_var['image_url']; ?>wap/images/-.png"/>
                           <input type="text" onchange="changep(<?php echo $this->_var['goods']['rec_id']; ?>)" readonly="readonly" name="package[<?php echo $this->_var['goods']['rec_id']; ?>]" id="goods_number_<?php echo $this->_var['goods']['rec_id']; ?>" value="<?php echo $this->_var['goods']['goods_number']; ?>" style="border:1px solid #999999;width:20px;text-align:center; color:#999;" />
                           <img src="<?php echo $this->_var['image_url']; ?>wap/images/+.png"/>
                       </div>
                       <?php endif; ?>
                       <div class="clear"></div>
                   </div>
                   <?php elseif ($this->_var['goods']['extension_code'] == 'tuan_buy'): ?>
                   <div class="go_con_bottom_one">
                       <div class="go_con_bott_one_left">
                           <?php if ($this->_var['goods']['is_gift'] > 0 && $this->_var['goods']['goods_price'] == '0.00' && $this->_var['goods']['eye_id'] > 0 && $this->_var['goods']['zselect'] == ''): ?>
                           <p><span class="red"><!--度数请填在<br/>订单附言中--></span></p>
                           <?php else: ?>
                           <?php if ($this->_var['goods']['goods_attr']): ?>
                           <p>眼镜度数：<?php echo $this->_var['goods']['goods_attr']; ?></p>
                           <?php else: ?>
                           <p><?php if ($this->_var['goods']['zselect']): ?>度数：<?php echo $this->_var['goods']['zselect']; ?><?php endif; ?></p>
                           <p><?php if ($this->_var['goods']['yselect']): ?>度数：<?php echo $this->_var['goods']['yselect']; ?><?php endif; ?></p>
                           <?php endif; ?>
                           <?php endif; ?>
                       </div>
                       <div class="go_con_bott_one_right">
                           <?php if ($this->_var['goods']['goods_sn'] == 1): ?>
                           <a onclick="reduce_tuan(<?php echo $this->_var['goods']['rec_id']; ?>)"><img src="<?php echo $this->_var['image_url']; ?>wap/images/-.png"/></a>
                           <input type="text" onchange="change_tuan(<?php echo $this->_var['goods']['rec_id']; ?>)" readonly="readonly" name="package[<?php echo $this->_var['goods']['rec_id']; ?>]" id="package_<?php echo $this->_var['goods']['rec_id']; ?>" value="<?php echo $this->_var['goods']['goods_number']; ?>" />
                           <a onclick="add_tuan(<?php echo $this->_var['goods']['rec_id']; ?>)"><img src="<?php echo $this->_var['image_url']; ?>wap/images/+.png"/></a>
                           <?php else: ?>
                           <a ><img src="<?php echo $this->_var['image_url']; ?>wap/images/-.png"/></a>
                           <input type="text" style="color:#999" onchange="changez(<?php echo $this->_var['goods']['rec_id']; ?>)" name="sszb[<?php echo $this->_var['goods']['rec_id']; ?>]" readonly="readonly" id="goods_number_<?php echo $this->_var['goods']['rec_id']; ?>" value="<?php echo $this->_var['goods']['goods_number']; ?>"/>
                           <a ><img src="<?php echo $this->_var['image_url']; ?>wap/images/+.png"/></a>
                           <?php endif; ?>
                       </div>
                       <div class="clear"></div>
                   </div>
                   <?php else: ?>
                   <div class="go_con_bottom_one">
                       <div class="go_con_bott_one_left">
                           <?php if ($this->_var['goods']['goods_attr']): ?><p>眼镜度数：<?php echo $this->_var['goods']['goods_attr']; ?></p><?php endif; ?>
                       </div>
                       <div class="go_con_bott_one_right">
                           <a onclick="reduce(<?php echo $this->_var['goods']['rec_id']; ?>)"><img src="<?php echo $this->_var['image_url']; ?>wap/images/-.png"/></a>
                           <input type="text" onchange="change(<?php echo $this->_var['goods']['rec_id']; ?>)" readonly="readonly" name="package[<?php echo $this->_var['goods']['rec_id']; ?>]" id="goods_number_<?php echo $this->_var['goods']['rec_id']; ?>" value="<?php echo $this->_var['goods']['goods_number']; ?>" />
                           <a onclick="add(<?php echo $this->_var['goods']['rec_id']; ?>)"><img src="<?php echo $this->_var['image_url']; ?>wap/images/+.png"/></a>
                       </div>
                       <div class="clear"></div>
                   </div>
                   <?php endif; ?>
                   <?php else: ?>
                   <?php if ($this->_var['goods']['zcount'] + $this->_var['goods']['ycount'] > 0): ?>
                   <?php if ($this->_var['goods']['is_gift'] > 0 && $this->_var['goods']['goods_price'] != '￥0.00'): ?>
                   <div class="go_con_bottom_one">
                       <div class="go_con_bott_one_left">
                           <?php if ($this->_var['goods']['is_gift'] > 0 && $this->_var['goods']['goods_price'] == '0.00' && $this->_var['goods']['eye_id'] > 0 && $this->_var['goods']['zselect'] == ''): ?>
                           <p><span class="red"><!--度数请填在<br/>订单附言中--></span></p>
                           <?php else: ?>
                           <?php if ($this->_var['goods']['goods_attr']): ?>
                           <p>眼镜度数：<?php echo $this->_var['goods']['goods_attr']; ?></p>
                           <?php else: ?>
                           <p><?php if ($this->_var['goods']['zselect']): ?>度数：<?php echo $this->_var['goods']['zselect']; ?><?php endif; ?></p>
                           <p><?php if ($this->_var['goods']['yselect']): ?>度数：<?php echo $this->_var['goods']['yselect']; ?><?php endif; ?></p>
                           <?php endif; ?>
                           <?php endif; ?>
                       </div>
                       <div class="go_con_bott_one_right">
                           <a ><img src="<?php echo $this->_var['image_url']; ?>wap/images/-.png"/></a>
                           <input type="text" style="color:#999" onchange="changez(<?php echo $this->_var['goods']['rec_id']; ?>)" readonly="readonly" name="package[<?php echo $this->_var['goods']['rec_id']; ?>]" id="goods_number_<?php echo $this->_var['goods']['rec_id']; ?>" value="<?php echo $this->_var['goods']['goods_number']; ?>" />
                           <a ><img src="<?php echo $this->_var['image_url']; ?>wap/images/+.png"/></a>
                       </div>
                       <div class="clear"></div>
                   </div>
                   <?php else: ?>
                   <div class="go_con_bottom_one">
                       <div class="go_con_bott_one_left">
                           <?php if ($this->_var['goods']['is_gift'] > 0 && $this->_var['goods']['goods_price'] == '0.00' && $this->_var['goods']['eye_id'] > 0 && $this->_var['goods']['zselect'] == ''): ?>
                           <p><span class="red"><!--度数请填在<br/>订单附言中--></span></p>
                           <?php else: ?>
                           <?php if ($this->_var['goods']['goods_attr']): ?>
                           <p>眼镜度数：<?php echo $this->_var['goods']['goods_attr']; ?></p>
                           <?php else: ?>
                           <p><?php if ($this->_var['goods']['zselect']): ?>度数：<?php echo $this->_var['goods']['zselect']; ?><?php endif; ?></p>
                           <p><?php if ($this->_var['goods']['yselect']): ?>度数：<?php echo $this->_var['goods']['yselect']; ?><?php endif; ?></p>
                           <?php endif; ?>
                           <?php endif; ?>
                       </div>
                       <div class="go_con_bott_one_right">
                           <a onclick="reducez(<?php echo $this->_var['goods']['rec_id']; ?>)"><img src="<?php echo $this->_var['image_url']; ?>wap/images/-.png"/></a>
                           <input type="text" onchange="changez(<?php echo $this->_var['goods']['rec_id']; ?>)" readonly="readonly" name="package[<?php echo $this->_var['goods']['rec_id']; ?>]" id="goods_number_<?php echo $this->_var['goods']['rec_id']; ?>" value="<?php echo $this->_var['goods']['goods_number']; ?>" />
                           <a onclick="addz(<?php echo $this->_var['goods']['rec_id']; ?>)"><img src="<?php echo $this->_var['image_url']; ?>wap/images/+.png"/></a>
                       </div>
                       <div class="clear"></div>
                   </div>
                   <?php endif; ?>
                   <?php endif; ?>
                   <?php endif; ?>
               <?php else: ?>
                   <?php if ($this->_var['goods']['is_kj'] == 1): ?>
                   <div class="go_con_bottom_one">
                       <div class="go_con_bott_one_left">
                           <p><?php if ($this->_var['goods']['ds_extention']): ?>左：<?php echo $this->_var['goods']['zselect']; ?> 右：<?php echo $this->_var['goods']['yselect']; ?><?php endif; ?></p>
                           <?php if ($this->_var['goods']['ds_extention']): ?><p>瞳距：<?php echo $this->_var['goods']['ds_extention']; ?></p><?php endif; ?>
                           <p>单位：副</p>
                       </div>
                       <div class="go_con_bott_one_right">
                           <a ><img src="<?php echo $this->_var['image_url']; ?>wap/images/-.png"/></a>
                           <input type="text" style="color:#999" onchange="changez(<?php echo $this->_var['goods']['rec_id']; ?>)" name="sszb[<?php echo $this->_var['goods']['rec_id']; ?>]" readonly="readonly" id="goods_number_<?php echo $this->_var['goods']['rec_id']; ?>" value="<?php echo $this->_var['goods']['goods_number']; ?>"/>
                           <a ><img src="<?php echo $this->_var['image_url']; ?>wap/images/+.png"/></a>
                       </div>
                       <div class="clear"></div>
                   </div>
                   <?php else: ?>
                   <div class="go_con_bottom_one">
                       <div class="go_con_bott_one_left">
                           <?php if ($this->_var['goods']['is_gift'] > 0 && $this->_var['goods']['goods_price'] == '0.00' && $this->_var['goods']['eye_id'] > 0 && $this->_var['goods']['zselect'] == ''): ?>
                           <p><span class="red"><!--度数请填在<br/>订单附言中--></span></p>
                           <?php else: ?>
                           <?php if ($this->_var['goods']['goods_attr']): ?>
                           <p>眼镜度数：<?php echo $this->_var['goods']['goods_attr']; ?></p>
                           <?php else: ?>
                           <p><?php if ($this->_var['goods']['zselect']): ?>度数：<?php echo $this->_var['goods']['zselect']; ?><?php endif; ?></p>
                           <p><?php if ($this->_var['goods']['yselect']): ?>度数：<?php echo $this->_var['goods']['yselect']; ?><?php endif; ?></p>
                           <?php endif; ?>
                           <?php endif; ?>
                       </div>
                       <div class="go_con_bott_one_right">
                           <a ><img src="<?php echo $this->_var['image_url']; ?>wap/images/-.png"/></a>
                           <input type="text" style="color:#999" onchange="changez(<?php echo $this->_var['goods']['rec_id']; ?>)" readonly="readonly" name="package[<?php echo $this->_var['goods']['rec_id']; ?>]" id="goods_number_<?php echo $this->_var['goods']['rec_id']; ?>" value="<?php echo $this->_var['goods']['goods_number']; ?>" />
                           <a ><img src="<?php echo $this->_var['image_url']; ?>wap/images/+.png"/></a>
                       </div>
                       <div class="clear"></div>
                   </div>
                   <?php endif; ?>
               <?php endif; ?>
                </div>
          </div>
          <div class="go_content_one_right">
              <!--<a href="javascript:if(confirm('<?php echo $this->_var['lang']['drop_goods_confirm']; ?>')) location.href='flow.php?step=drop_goods&amp;id=<?php echo $this->_var['goods']['rec_id']; ?>';">&times;</a>-->
              <?php if ($this->_var['goods']['goods_id'] > 0 && $this->_var['goods']['extension_code'] == 'package_buy'): ?>
                  <?php if ($this->_var['goods']['goods_sn'] == 1): ?>
                  <a href="javascript:drop_package(<?php echo $this->_var['goods']['rec_id']; ?>)">&times;</a>
                  <?php else: ?>
                  <font style="color:#999999">&times;</font>
                  <?php endif; ?>
              <?php elseif ($this->_var['goods']['goods_id'] > 0 && $this->_var['goods']['extension_code'] == 'tuan_buy'): ?>
                  <?php if ($this->_var['goods']['goods_sn'] > 0): ?>
                  <a href="javascript:drop_tuan(<?php echo $this->_var['goods']['rec_id']; ?>)">&times;</a>
                  <?php else: ?>
                  <a href="javascript:alert('子商品无法删除，请删除主商品')">&times;</a>
                  <?php endif; ?>
              <?php elseif ($this->_var['goods']['parent_id'] != 0): ?>
                  <a href="javascript:alert('子商品无法删除，请删除主商品')">&times;</a>
              <?php else: ?>
                  <?php if ($this->_var['goods']['extension_code'] == 'exchange_buy'): ?>
                  <a href="javascript:drop_defined(<?php echo $this->_var['goods']['rec_id']; ?>, 1);">&times;</a>
                  <?php else: ?>
                  <a href="javascript:if(confirm('<?php echo $this->_var['lang']['drop_goods_confirm']; ?>')) location.href='flow.php?step=drop_goods&amp;id=<?php echo $this->_var['goods']['rec_id']; ?>'; ">&times;</a>
                  <?php endif; ?>
              <?php endif; ?>
          </div>
          <div class="clear"></div>
       </div>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

    <!--   <div class="go_content_two">
           <div class="go_content_two_left">选择优惠活动，使用礼券</div>
           <div class="go_content_two_right"><a href="#"><img src="<?php echo $this->_var['image_url']; ?>wap/images/jt.png"></a></div>
           <div class="clear"></div>
       </div>-->
       <div class="go_content_three">
          <div>商品总重：<span id="cart_weight"><?php echo $this->_var['total']['goods_weight']; ?>kg</span></div>
          <div>商品数量总计：<span id="cart_num"><?php echo $this->_var['total']['goods_number']; ?>件</span></div>
          <div>赠送积分总计：<span id="cart_points"><?php echo $this->_var['total']['goods_amount_float']; ?>分</span></div>
          <div>商品总价（不含运费）：<span id="cart_sum"><?php echo $this->_var['shopping_moneyn']; ?></span></div>
          <div class="cart1_end">
            <span id="freepx"><?php if ($this->_var['goods_pricex'] != - 1): ?><?php if ($this->_var['goods_pricex']): ?><span class="note1">您还差<font class="red"><?php echo $this->_var['goods_pricex']; ?>元</font>就可以得到免费配送</span>
            <?php else: ?><span class="note1">购物已超过<?php echo $this->_var['base_line']; ?>元，您可以享受免费快递。</span><?php endif; ?><?php endif; ?></span>
          </div>
       </div>
       <div style="text-align:center;" class="goto_jiesuan"><a href="flow.php?step=checkout">提交订单，去结算 >></a></div>
    </div>
</div>
<?php endif; ?>
<?php echo $this->fetch('library/footer.lbi'); ?>
<script src="http://file.easeeyes.com/wap/js/touch.js"></script>
<script type="text/javascript">
    //banner轮播
    function TouchSlide(opts){
        var opts=opts||{};
        this.count=0;
        this.auto=opts.auto||false;
        this.elem=opts.elem;
        this.len=opts.len||this.elem.getElementsByTagName("li").length;
        this.width=opts.width;
        this.init.apply(this,arguments);
    }
    TouchSlide.prototype={
        constructor:TouchSlide,
        move:function(points){
            if(this.count>=this.len-1){
                this.count=0;
            }else{
                this.count++;
            }
            this.elem.style.left=-this.count*this.width+"px";
            var i,
                    point=points.getElementsByTagName("li"),
                    len=point.length;
            for(i=0;i<len;i++){
                point[i].className="";
            }
            point[this.count].className="selected";
        },
        isAuto:function(points){
            var that=this;
            if(that.auto){
                that.mover=setInterval(function(){
                    that.move(points);
                },that.auto);
            }
        },
        createPoints:function(){
            var i,
                    points=document.createElement("ul");
            points.className="points";
            for(i=0;i<this.len;i++){
                points.appendChild(document.createElement("li"));
            }
            this.elem.parentNode.appendChild(points);
            return points;
        },
        binder:function(points){
            var initp={},
                    movep={},
                    endp={},
                    xpos,
                    that=this;
            this.elem.addEventListener("touchstart",function(e){
                if(that.mover) clearInterval(that.mover);
                xpos=parseInt(this.style.left)||0;
                initp=touchEvent.getPos(e);
            },false);
            this.elem.addEventListener("touchmove",function(e){
                movep=touchEvent.getPos(e);
                this.style.left=xpos+movep.mx-initp.x+"px";
            },false);
            this.elem.addEventListener("touchend",function(e){
                endp=touchEvent.getPos(e);
                var dir=touchEvent.getDirect(initp.x,endp.ex,initp.y,endp.ey);
                if(dir=="left"){
                    if(Math.abs(endp.ex-initp.x)>30){
                        if(that.count>=that.len-1){
                            that.count=that.len-1
                        }else{
                            that.count++;
                        }
                    }
                }else if(dir=="right"){
                    if(Math.abs(endp.ex-initp.x)>30){
                        if(that.count<=0){
                            that.count=0;
                        }else{
                            that.count--;
                        }
                    }
                }
                var i,
                        point=points.getElementsByTagName("li"),
                        len=point.length;
                for(i=0;i<len;i++){
                    point[i].className="";
                }
                this.style.left=-that.count*that.width+"px";
                point[that.count].className="selected";
                that.isAuto(points);
            },false);
        },
        init:function(){
            var points=this.createPoints();
            points.getElementsByTagName("li")[0].className="selected";
            this.binder(points);
            this.isAuto(points);
        }
    };
    // 滚动
    window.addEventListener("load",function(){
        try{
            var s1=new TouchSlide({
                elem:document.getElementById("cart_slider_01"),
                width:parseInt(document.getElementById("cart_slider_box_01").clientWidth),
                len:document.getElementById("cart_slider_01").getElementsByTagName("li").length/3,
                auto:false
            });
        }catch(ex){

        }
    },false);

// 折叠
var Fold=function(){
    function toggleFold(bars){
        bars.forEach(function(bar){
            bar.addEventListener("click",function(){
                try{
                    if(bar.dataset.fold=="true"){
                        bar.dataset.fold="false";
                    }else{
                        bar.dataset.fold="true";
                    }
                }catch(ex){

                }
            },false);
        });
    }
    return {
        init:function(opts){
            var opts=opts||{},
                bars=opts.bars||{};
            var bars=Array.prototype.slice.call(bars,0);
            bars.forEach(function(bar){
                bar.dataset.fold="true";
            });
            toggleFold(bars);
        }
    }
}();
Fold.init({bars:document.getElementsByClassName("th_g_h")});

</script>
</body>
</html>