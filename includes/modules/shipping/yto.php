<?php
/* ============================================================================
 *【宅急送快递=>货到付款专用快递插件】【原来圆通】【2012/3/16】
 * ============================================================================
 */

if(!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$shipping_lang = ROOT_PATH.'languages/' .$GLOBALS['_CFG']['lang']. '/shipping/yto.php';
if(file_exists($shipping_lang))
{
    global $_LANG;
    include_once($shipping_lang);
}

//插件模块的基本信息
if(isset($set_modules) && $set_modules == TRUE)
{
    $i = (isset($modules)) ? count($modules) : 0;

    $modules[$i]['code']    = 'yto';     //配送方式插件的代码必须和文件名保持一致
    $modules[$i]['version'] = '1.0.0';
    $modules[$i]['desc']    = 'yto_desc';//配送方式的描述
    $modules[$i]['insure']  = false;     //不支持保价
    $modules[$i]['cod']     = TRUE;      //配送方式是否支持货到付款
    $modules[$i]['author']  = 'YI';      //插件的作者
    $modules[$i]['website'] = '';        //插件作者的官方网站

    //配送接口需要的参数
    $modules[$i]['configure'] = array(
                                    array('name' => 'item_fee',     'value'=>10),   /* 单件商品的配送价格 */
                                    array('name' => 'base_fee',     'value'=>10),   /* 1000克以内的价格 */
                                    array('name' => 'step_fee',     'value'=>5),    /* 续重每1000克增加的价格 */
                                );

    return;
}

class yto
{
    /*------------------------------------------------------ */
    //-- PUBLIC ATTRIBUTEs
    /*------------------------------------------------------ */

    /**
     * 配置信息
     */
    var $configure;

    /*------------------------------------------------------ */
    //-- PUBLIC METHODs
    /*------------------------------------------------------ */

    /**
     * 构造函数
     *
     * @param: $configure[array]    配送方式的参数的数组
     *
     * @return null
     */
    function yto($cfg = array())
    {
        foreach ($cfg AS $key=>$val)
        {
            $this->configure[$val['name']] = $val['value'];
        }
    }

    /**
     * 计算订单的配送费用的函数
     *
     * @param   float   $goods_weight   商品重量
     * @param   float   $goods_amount   商品金额
     * @param   float   $goods_number   商品件数
     * @return  decimal
     */
    function calculate($goods_weight, $goods_amount, $goods_number)
    {
        if($this->configure['free_money'] > 0 && $goods_amount >= $this->configure['free_money'])
        {
            return 0;
        }
        else
        {
            @$fee = $this->configure['base_fee'];//首重费用
            $this->configure['fee_compute_mode'] = !empty($this->configure['fee_compute_mode']) ? $this->configure['fee_compute_mode'] : 'by_weight';

            if($this->configure['fee_compute_mode'] == 'by_number')
            {
                $fee = $goods_number * $this->configure['item_fee'];
            }
            else
            {
				//按重量计算运费
                if($goods_weight > 1)
                {
                    $fee += (ceil(($goods_weight - 1))) * $this->configure['step_fee'];
                }
            }
            return $fee;
        }
    }

	//yi:【150不免邮的情况应付多少运费】
    function cal_old($goods_weight, $goods_amount, $goods_number,$area_id=0){
        if ($this->configure['free_money'] > 0 && $goods_amount >= $this->configure['free_money'])
        {
            return 0;
        }
        else
        {
            @$fee = $this->configure['base_fee'];
            $this->configure['fee_compute_mode'] = !empty($this->configure['fee_compute_mode']) ? $this->configure['fee_compute_mode'] : 'by_weight';

            if ($this->configure['fee_compute_mode'] == 'by_number')
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
            return $fee;
        }		
	}

    /**
     * 查询发货状态
     *
     * @access  public
     * @param   string  $invoice_sn     发货单号
     * @return  string
     */
    function query($invoice_sn)
    {
        //圆通快递查询会判断链接来源，目前的查询无法生效。
        $str = '<form style="margin:0px" methods="post" '.
            'action="http://www.yto.net.cn/service/service.asp" name="queryForm_' .$invoice_sn. '" target="_blank">'.
            '<input type="hidden" name="NumberText" value="' .$invoice_sn. '" />'.
            '<a href="javascript:document.forms[\'queryForm_' .$invoice_sn. '\'].submit();">' .$invoice_sn. '</a>'.
            '<input type="hidden" name="imageField.x" value="54" />'.
            '<input type="hidden" name="imageField.y" value="19" />'.
            '</form>';

        return $str;
    }
}
?>