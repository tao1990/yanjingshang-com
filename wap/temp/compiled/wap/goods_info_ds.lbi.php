 度数　
    <select name="goods_select">
	   <option value="">请选择</option>
	   <?php 
$k = array (
  'name' => 'ds_list',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?>
    </select>　
       <div class="d-select-div clearfix">
                        <?php if ($this->_var['goods_is_sg'] && $this->_var['goods_sgds']): ?>
                                <div class="fl ml15" style="padding-right:15px;">散光</div>
                                <input type="hidden" id="is_sg" value="is_sg" />
                                <select name="zsg" class="pro_top_link_selse fl">
                                    <option value="">请选择</option>
                                    <?php $_from = $this->_var['goods_sgds']['ds_values']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'value');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['value']):
?>
                                    <option value="<?php echo $this->_var['value']; ?>"><?php echo $this->_var['value']; ?></option>
                                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                                </select>
                                <div class="fl ml15" style="padding:0 15px;">　轴位</div>
                                <select name="zzhou" class="pro_top_link_selse fl">
                                    <option value="">请选择</option>
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="20">20</option>
                                    <option value="25">25</option>
                                    <option value="30">30</option>
                                    <option value="35">35</option>
                                    <option value="40">40</option>
                                    <option value="45">45</option>
                                    <option value="50">50</option>
                                    <option value="55">55</option>
                                    <option value="60">60</option>
                                    <option value="65">65</option>
                                    <option value="70">70</option>
                                    <option value="75">75</option>
                                    <option value="80">80</option>
                                    <option value="85">85</option>
                                    <option value="90">90</option>
                                    <option value="95">95</option>
                                    <option value="100">100</option>
                                    <option value="105">105</option>
                                    <option value="110">110</option>
                                    <option value="115">115</option>
                                    <option value="120">120</option>
                                    <option value="125">125</option>
                                    <option value="130">130</option>
                                    <option value="135">135</option>
                                    <option value="140">140</option>
                                    <option value="145">145</option>
                                    <option value="150">150</option>
                                    <option value="155">155</option>
                                    <option value="160">160</option>
                                    <option value="165">165</option>
                                    <option value="170">170</option>
                                    <option value="175">175</option>
                                    <option value="180">180</option>
                                </select>  
                        <?php else: ?>
                                <input type="hidden" id="is_sg" value=""/>
                        <?php endif; ?>  
       </div>
       <div class="d-select-div">  
    采购量　
        <div class="numCount-pg">
	       <button class="numCount-pg-cut">&minus;</button>
		<div class="numCount-pg-num">
									<input type="text" name="goods_count" value="1" id="number" /><?php echo $this->_var['goods']['unit']; ?>
								</div>
								<button class="numCount-pg-add">&#43;</button>
							</div>
    </div>  