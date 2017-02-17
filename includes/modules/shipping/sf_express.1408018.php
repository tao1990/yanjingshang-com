<?php
/* ============================================================================
 * 普通快递 配送方式插件【原来的顺丰速运插件修改而来】【2012/3/2】
 * ============================================================================
 * 快递运费规则：【1.2kg以内算首重，超过1.2kg才算超重】
 * 首重标准:1.2kg
 * 快递运费用计算方式: 起点到终点 * 重量(kg)【新增加免费的梯度功能】
 *                      免运费逻辑：达到一定的金额免掉首重费用。只收续重费用。
 */

if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}
$shipping_lang = ROOT_PATH.'languages/' .$GLOBALS['_CFG']['lang']. '/shipping/sf_express.php';

if(file_exists($shipping_lang))
{
    global $_LANG;
    include_once($shipping_lang);
}

//配送方式插件的基本信息【可修改配置】
if(isset($set_modules) && $set_modules == TRUE)
{
    $i = (isset($modules)) ? count($modules) : 0;

    //配送方式插件的代码必须和文件名保持一致
    $modules[$i]['code']    = basename(__FILE__, '.php');
    $modules[$i]['version'] = '1.0.0';            //版本
    $modules[$i]['desc']    = 'sf_express_desc';  //描述
    $modules[$i]['cod']     = false;              //是否支持货到付款
    $modules[$i]['author']  = 'YI';               //插件作者
    $modules[$i]['website'] = '';                 //插件作者官方网站

    //配送接口需要的参数(默认配送配置)
    $modules[$i]['configure'] = array(
                                    array('name' => 'item_fee',    'value'=>20), /* 单件商品的配送费用 */
                                    array('name' => 'base_fee',    'value'=>15), /* 1000克以内的价格   */
                                    array('name' => 'step_fee',    'value'=>2),  /* 续重每1000克增加的价格 */
                                );
    return;
}


//配送方式类【oop】
class sf_express
{
    /*------------------------------------------------------ */
    //-- PUBLIC ATTRIBUTEs
    /*------------------------------------------------------ */

    /**
     * 配置信息参数
     */
    var $configure;
	var $step = 1;  //首重标准1kg。

    /*------------------------------------------------------ */
    //-- PUBLIC METHODs
    /*------------------------------------------------------ */

    /**
     * 构造函数
     * @param: $configure[array] 配送方式的参数的数组 
     * @return null
     */
    function sf_express($cfg=array())
    {
        foreach($cfg AS $key=>$val)
        {
            $this->configure[$val['name']] = $val['value'];
        }
    }

    /**
     * 计算订单的配送费用的函数【新增加免费的梯度功能】
     * @param   float   $goods_weight   商品重量
     * @param   float   $goods_amount   商品金额
     * @param   float   $goods_number   商品数量
	 * area_id  int 配送地区编号
     * @return  decimal
     */
    function calculate($goods_weight, $goods_amount, $goods_number, $area_id=0)
    {
		//yi:购物金额达到免费额度，只免首重。超出首重部分任然要收费=====================================//
		//$_SESSION['base_line'] = 9999; //2014.01.25~27 改顺风快递，不满额免邮
    	if($_SESSION['user_id']>0 && isset($_SESSION['base_line']) && $_SESSION['base_line']>0)
		{
			$this->configure['free_money'] = intval($_SESSION['base_line']); //yi:会员福利，不同会员免运费金额不同。
		}

		//全场包邮（免首重）功能(2014.03.24~2014.03.30满69元包邮)
		//2014.05.14~2014.05.20满149包邮
		//if(in_array($area_id, array(6,7,20)) && $_SERVER['REQUEST_TIME'] >= 1390924800 && $_SERVER['REQUEST_TIME'] <= 1391702399 && $goods_amount > 0)
		//if(in_array($area_id, array(6,7,20)) && $_SERVER['REQUEST_TIME'] >= 1399996800 && $_SERVER['REQUEST_TIME'] <= 1400601599 && $goods_amount >= 149)
		if(in_array($area_id, array(6,7,20)) && $_SERVER['REQUEST_TIME'] >= 1402502400 && $_SERVER['REQUEST_TIME'] <= 1402761599 && $goods_amount > 0)
		{
			$this->configure['free_money'] = 0.1;
		}

        if($this->configure['free_money'] > 0 && $goods_amount >= $this->configure['free_money'] && $area_id != 22)
        {
			if($goods_weight <= $this->step)
			{
				//免掉首重
				return 0;
			}
			else
			{
				//超重，按照阶梯算法增加运费
				$yifee      = 0;          //总运费
				$fee_weight = $this->step;//减免重量(初始值为首重)

				//==================================商品运费分梯次免掉===============================||
				if($area_id>0)
				{
					//找出全部的运费阶梯
					$sql_l  = "select * from ".$GLOBALS['ecs']->table('shipping_ladder')." where shipping_area_id='$area_id' order by fee desc";
					$ladder = $GLOBALS['db']->GetAll($sql_l);					
					
					//商品金额达到哪个层次，给予对应金额减免
					foreach($ladder as $k => $v)
					{
						if(!empty($goods_amount) && $goods_amount>= $ladder[$k]['fee'])
						{
							if($ladder[$k]['weight']> $this->step)
							{
								$fee_weight = $ladder[$k]['weight'];
							}
							break;
						}
					}
				}

				//计算超重的费用。fee_weight：免掉的重量。（包括免掉重量之后任然超重费用）
				if($goods_weight > $fee_weight)
				{
					$yifee = (ceil(($goods_weight-$fee_weight)))*$this->configure['step_fee'];
				}
				else
				{
					//商品重量小于减免重量 运费为0
					$yifee = 0;
				}
				return $yifee;
				//==================================商品运费分梯次免掉【end】=========================||
			}
        }
        else
        {
			//==================================【购物金额没有达到免运费条件(未满150),按原来方式计算】==================================//

            @$fee = $this->configure['base_fee'];//首重费用
            $this->configure['fee_compute_mode'] = !empty($this->configure['fee_compute_mode']) ? $this->configure['fee_compute_mode'] : 'by_weight';

            if($this->configure['fee_compute_mode'] == 'by_number')
            {
                $fee = $goods_number * $this->configure['item_fee'];
            }
            else
            {
                if($goods_weight > $this->step)
                {
                    $fee += (ceil(($goods_weight - $this->step))) * $this->configure['step_fee'];
                }
            }
            //$_SESSION['cart_weight'] = $goods_weight;
            return $fee;
        }
    }

	/* -------------------------------------------------------------------------------------------------
	 * 方法 【150不免邮的情况应付多少运费】计算实际应该付款的运费
	 * -------------------------------------------------------------------------------------------------
	 */
    function cal_old($goods_weight, $goods_amount, $goods_number, $area_id=0)
    {
		$fee_old = array();
		
		//=========================计算实际应该付款的运费========================||
		@$fee = $this->configure['base_fee'];
		$this->configure['fee_compute_mode'] = !empty($this->configure['fee_compute_mode']) ? $this->configure['fee_compute_mode'] : 'by_weight';

		if($this->configure['fee_compute_mode'] == 'by_number')
		{
			$fee = $goods_number * $this->configure['item_fee'];
		}
		else
		{
			if($goods_weight > 1)
			{
				$fee += (ceil(($goods_weight - 1))) * $this->configure['step_fee'];
			}
		}
		$fee_old['fee'] = $fee; //实际应该支付的运费

		$fee_wei = 0;           //可免的重量
		$fee_fee = 0;           //还应继续购买就可以全免运费的金额。
		$fee_id  = 0;

		//================================计算原先的基本的数据【以金额为参照标准】================================||
        if($this->configure['free_money'] > 0 && $goods_amount >= $this->configure['free_money'])
        {
			//商品总重【1kg】,已免运费【5元】.再买【100元】总重少于【5kg】即可全免运费
			if($goods_weight > 1)
			{
				if($area_id>0)
				{
					//找出全部的运费阶梯
					$sql_l  = "select * from ".$GLOBALS['ecs']->table('shipping_ladder')." where shipping_area_id='$area_id' order by weight";
					$ladder = $GLOBALS['db']->GetAll($sql_l);					
					
					//记住商品刚刚超过的重量。
					foreach($ladder as $k => $v)
					{
						if(!empty($goods_weight) && $goods_weight < $ladder[$k]['weight'])
						{
							if($ladder[$k]['fee']>$goods_amount)
							{
								$fee_id  = $ladder[$k]['rec_id'];
								$fee_wei = $ladder[$k]['weight'];
								$fee_fee = $ladder[$k]['fee'] - $goods_amount;//在买的金额
							}
							break;
						}
					}
				}else{
					//没有设置的情况 则不体现那句话。
					$fee_wei = 0;
				}				
			}
        }
		$fee_old['fee_id']  = $fee_id;
		$fee_old['fee_wei'] = $fee_wei;
		$fee_old['fee_fee'] = $fee_fee;
		return $fee_old;
    }

    /**
     * 查询快递状态
     * @access  public
     * @return  string  查询窗口的链接地址
     */
    function query($invoice_sn)
    {
        //$form_str = '<a href="http://www.sf-express.com/tabid/68/Default.aspx" target="_blank">' .$invoice_sn. '</a>';
        //return $form_str;
		return '';
    }
}
?>