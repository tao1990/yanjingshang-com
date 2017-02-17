 <?php if ($this->_var['pifa_confirm'] == 1): ?>
                        <ul>
    					    <li>起批量</li>
				            <li>价格</li>
    					</ul>
    <?php $_from = $this->_var['wholesale_arr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'li_0_18631400_1484796252');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['key'] => $this->_var['li_0_18631400_1484796252']):
        $this->_foreach['foo']['iteration']++;
?>      
    								<ul>
    									<li>≥<?php echo $this->_var['li_0_18631400_1484796252']['quantity']; ?><?php echo $this->_var['goods']['unit']; ?></li>
    									<li>¥ <?php echo $this->_var['li_0_18631400_1484796252']['price']; ?>/<?php echo $this->_var['goods']['unit']; ?></li>
    								</ul>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
<?php else: ?>
                            
                            <?php if ($this->_var['user_rank'] > 0): ?>
                            
                                <p class="goodsInfo-del">vip价:<?php echo $this->_var['rank_prices']['7']['price']; ?> - <?php echo $this->_var['vip_prices']; ?>/<?php echo $this->_var['goods']['unit']; ?></p>
                                <p class="goodsInfo-price">&yen;<?php echo $this->_var['rank_price']; ?>/<?php echo $this->_var['goods']['unit']; ?>  </p>
                    
                            <?php else: ?>
                                <p class="goodsInfo-del">vip价:<?php echo $this->_var['rank_prices']['7']['price']; ?> - <?php echo $this->_var['vip_prices']; ?>/<?php echo $this->_var['goods']['unit']; ?></p>
                                <p class="goodsInfo-price">&yen;<?php echo $this->_var['shop_price']; ?>/<?php echo $this->_var['goods']['unit']; ?>  </p>
                            <?php endif; ?>
<?php endif; ?>