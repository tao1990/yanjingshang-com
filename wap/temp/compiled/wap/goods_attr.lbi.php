	<div style="background:#fff;margin-top:.7rem;" class="proGg">
			<h2 class="detailpage-title">产品规格</h2>
            <div class="items detailpage-main">
				<div class="item">
					<span class="key">品牌</span>
					<span class="value"><?php echo $this->_var['goods']['goods_brand']; ?></span>
				</div>
                <?php if ($this->_var['goods']['goods_type'] == 10 || $this->_var['goods']['goods_type'] == 12): ?>
                    <?php $_from = $this->_var['attrs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'attr');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['attr']):
        $this->_foreach['foo']['iteration']++;
?>
                    <?php if ($this->_var['attr']['attr_id'] == 211 || $this->_var['attr']['attr_id'] == 219): ?>
                    <div class="item">
                        <span class="key">周期</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 212 || $this->_var['attr']['attr_id'] == 220): ?>
                    <div class="item">
                        <span class="key">颜色</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 213 || $this->_var['attr']['attr_id'] == 221): ?>
                    <div class="item">
                        <span class="key">含水量</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 214 || $this->_var['attr']['attr_id'] == 222): ?>
                    <div class="item">
                        <span class="key">直径</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 215 || $this->_var['attr']['attr_id'] == 223): ?>
                    <div class="item">
                        <span class="key">基弧</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 224 || $this->_var['attr']['attr_id'] == 229): ?>
                    <div class="item">
                        <span class="key">中心厚度</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 240): ?>
                    <div class="item">
                        <span class="key">透氧系数</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 225 || $this->_var['attr']['attr_id'] == 230): ?>
                    <div class="item">
                        <span class="key">材质</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 241 || $this->_var['attr']['attr_id'] == 242): ?>
                    <div class="item">
                        <span class="key">单位</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 226 || $this->_var['attr']['attr_id'] == 231): ?>
                    <div class="item">
                        <span class="key">产地</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 227 || $this->_var['attr']['attr_id'] == 232): ?>
                    <div class="item">
                        <span class="key">有效期</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 228 || $this->_var['attr']['attr_id'] == 233): ?>
                    <div class="item">
                        <span class="key">注册号</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endif; ?>

                <?php if ($this->_var['goods']['goods_type'] == 13 || $this->_var['goods']['goods_type'] == 0): ?>
                    <div class="item">
                        <span class="key">品牌</span>
                        <span class="value"><?php echo $this->_var['goods']['goods_brand']; ?></span>
                    </div>
                    <?php $_from = $this->_var['attrs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'attr');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['attr']):
        $this->_foreach['foo']['iteration']++;
?>
                    <?php if ($this->_var['attr']['attr_id'] == 243): ?>
                    <div class="item">
                        <span class="key">单位</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 234): ?>
                    <div class="item">
                        <span class="key">更换周期</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 235): ?>
                    <div class="item">
                        <span class="key">主要功能</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 236): ?>
                    <div class="item">
                        <span class="key">主要成分</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 237): ?>
                    <div class="item">
                        <span class="key">产地</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 238): ?>
                    <div class="item">
                        <span class="key">有效期</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->_var['attr']['attr_id'] == 239): ?>
                    <div class="item">
                        <span class="key">注册号</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endif; ?>

                <?php if ($this->_var['goods']['goods_type'] == 14): ?>
                    <div class="item">
                        <span class="key">品牌</span>
                        <span class="value"><?php echo $this->_var['goods']['goods_brand']; ?></span>
                    </div>
                    <div class="item">
                        <span class="key">单位</span>
                        <span class="value"><?php echo empty($this->_var['goods']['unit']) ? '盒' : $this->_var['goods']['unit']; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($this->_var['goods']['goods_type'] == 15): ?>
                    <div class="item">
                        <span class="key">品牌</span>
                        <span class="value"><?php echo $this->_var['goods']['goods_brand']; ?></span>
                    </div>
                    <?php $_from = $this->_var['attrs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'attr');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['attr']):
        $this->_foreach['foo']['iteration']++;
?>
                    <div class="item">
                        <span class="key">
                            <?php if ($this->_var['attr']['attr_id'] == 244): ?>款式
                            <?php elseif ($this->_var['attr']['attr_id'] == 245): ?>框型
                            <?php elseif ($this->_var['attr']['attr_id'] == 246): ?>尺寸
                            <?php elseif ($this->_var['attr']['attr_id'] == 247): ?>材质
                            <?php elseif ($this->_var['attr']['attr_id'] == 248): ?>颜色
                            <?php elseif ($this->_var['attr']['attr_id'] == 249): ?>风格
                            <?php elseif ($this->_var['attr']['attr_id'] == 250): ?>镜框尺寸
                            <?php elseif ($this->_var['attr']['attr_id'] == 251): ?>鼻梁尺寸
                            <?php elseif ($this->_var['attr']['attr_id'] == 252): ?>镜腿尺寸
                            <?php elseif ($this->_var['attr']['attr_id'] == 253): ?>镜框高度
                            <?php elseif ($this->_var['attr']['attr_id'] == 254): ?>总宽度
                            <?php else: ?><?php endif; ?>:
                        </span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                    <div class="item">
                        <span class="key">单位</span>
                        <span class="value"><?php echo empty($this->_var['goods']['unit']) ? '副' : $this->_var['goods']['unit']; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($this->_var['goods']['goods_type'] == 16): ?>
                    <div class="item">
                        <span class="key">品牌</span>
                        <span class="value"><?php echo $this->_var['goods']['goods_brand']; ?></span>
                    </div>
                    <?php $_from = $this->_var['attrs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'attr');$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from AS $this->_var['attr']):
        $this->_foreach['foo']['iteration']++;
?>
                    <?php if ($this->_var['attr']['attr_id'] < 265 || $this->_var['attr']['attr_id'] > 257): ?>
                    <div class="item">
                        <span class="key">周期</span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                        <span class="key">
                            <?php if ($this->_var['attr']['attr_id'] == 258): ?>款式
                            <?php elseif ($this->_var['attr']['attr_id'] == 259): ?>框型
                            <?php elseif ($this->_var['attr']['attr_id'] == 260): ?>尺码
                            <?php elseif ($this->_var['attr']['attr_id'] == 261): ?>材质
                            <?php elseif ($this->_var['attr']['attr_id'] == 262): ?>颜色
                            <?php elseif ($this->_var['attr']['attr_id'] == 263): ?>产地
                            <?php elseif ($this->_var['attr']['attr_id'] == 264): ?>尺寸
                            <?php else: ?><?php endif; ?>:
                        </span>
                        <span class="value"><?php echo $this->_var['attr']['attr_value']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endif; ?>
			</div>
		</div>