<?php
/* ============================================================================
 * 上门自提 上门取货插件【全部的配送费用都为0】【2012/3/2】
 * ============================================================================
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$shipping_lang = ROOT_PATH.'languages/' .$GLOBALS['_CFG']['lang']. '/shipping/cac.php';
if(file_exists($shipping_lang))
{
    global $_LANG;
    include_once($shipping_lang);
}

//模块的基本信息
if(isset($set_modules) && $set_modules == TRUE)
{
    $i = (isset($modules)) ? count($modules) : 0;

    $modules[$i]['code']    = 'cac';     //配送方式插件的代码必须和文件名保持一致
    $modules[$i]['version'] = '1.0.0';
    $modules[$i]['desc']    = 'cac_desc';//配送方式的描述
    $modules[$i]['insure']  = false;     //不支持保价
    $modules[$i]['cod']     = TRUE;      //配送方式是否支持货到付款
    $modules[$i]['author']  = 'YI';      //插件的作者
    $modules[$i]['website'] = '';        //插件作者的官方网站
    $modules[$i]['configure'] = array(); //配送接口需要的参数
    return;
}

class cac
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
    function cac($cfg = array())
    {
    }

    /**
     * 计算订单的配送费用的函数
     *
     * @param   float   $goods_weight   商品重量
     * @param   float   $goods_amount   商品金额
     * @return  decimal
     */
    function calculate($goods_weight, $goods_amount)
    {
        return 0;
    }

	//yi:【150不免邮的情况应付多少运费】
    function cal_old($goods_weight, $goods_amount, $goods_number,$area_id=0){
		return 0;		
	}

    /**
     * 查询发货状态
     * 该配送方式不支持查询发货状态
     *
     * @access  public
     * @param   string  $invoice_sn     发货单号
     * @return  string
     */
    function query($invoice_sn)
    {
        return $invoice_sn;
    }
}
?>