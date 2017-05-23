<?php
namespace Library\payment\unionpayb2b\api;
use \Library\payment\paymentplugin;
use \Library\payment\unionpayb2b\acpService;
/**
 * 银联网关支付子类
 * User: Administrator
 * Date: 2017/4/11 0011
 * Time: 下午 3:28
 */
include_once dirname(dirname(__FILE__)) . "/SDKConfig.php";
class pay extends paymentplugin{

    protected $paymentId = 4;
    public $name = '银联B2B支付';//插件名称

    /**
     * @see paymentplugin::getSubmitUrl()
     */
    public function getSubmitUrl() {
        return SDK_FRONT_TRANS_URL;//提交地址
    }

    /**
     * @see paymentplugin::notifyStop()
     */
    public function notifyStop() {
        echo "success";
    }


    /**
     * @see paymentplugin::callback()
     */
    public function callbackVerify($callbackData, &$money, &$message, &$orderNo,&$flowNo) {
        if (isset ( $callbackData['signature'] ))
        {
            if (AcpService::validate ( $callbackData ))
            {

                if ($callbackData["respCode"] == "00"){
                    //交易已受理，等待接收后台通知更新订单状态，如果通知长时间未收到也可发起交易状态查询
                    $orderNo = $callbackData['orderId'];//订单号
                    if(isset($callbackData['queryId'])){
                        $flowNo  = $callbackData['queryId'];//第三方流水号

                    }
                    $money   = $callbackData['txnAmt']/100;
                    return 1;
                } else if ($callbackData["respCode"] == "03"
                    || $callbackData["respCode"] == "04"
                    || $callbackData["respCode"] == "05" ){
                    //后续需发起交易状态查询交易确定交易状态
                    //TODO
                    $message = "处理超时，请稍后查询";
                } else {
                    //其他应答码做以失败处理
                    //TODO
                    $message = "失败：" . $callbackData["respMsg"];
                }
            }
            else
            {
                $message = '签名不正确';
            }
        }
        else
        {
            $message = '签名为空';
        }

        return 0;
    }


    /**
     * @see paymentplugin::getSendData()
     */
    public function getSendData($argument) {
       // header ( 'Content-type:text/html;charset=utf-8' );
        $payment = $this->getPaymentInfo();
        $argument = array_merge($argument,$payment);
        $params = array(

            //以下信息非特殊情况不需要改动
            'version' => '5.0.0',                 //版本号
            'encoding' => 'utf-8',				  //编码方式
            'txnType' => '01',				      //交易类型
            'txnSubType' => '01',				  //交易子类
            'bizType' => '000202',				  //业务类型
            'frontUrl' =>  $argument['frontUrl'],//SDK_FRONT_NOTIFY_URL,  //前台通知地址
            'backUrl' => $argument['backUrl'],//SDK_BACK_NOTIFY_URL,	  //后台通知地址
            'signMethod' => '01',	              //签名方法
            'channelType' => '07',	              //渠道类型，07-PC，08-手机
            'accessType' => '0',		          //接入类型
            'currencyCode' => '156',	          //交易币种，境内商户固定156

            //TODO 以下信息需要填写
            'merId' => $argument["M_merId"],
            'orderId' => $argument["M_OrderNO"],
            'txnTime' => date('YmdHis'),
            'txnAmt' => $argument["M_Amount"]*100,
            // 		'reqReserved' =>'透传信息',        //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据
            'reqReserved' =>  $argument['M_OrderId'] . ":" . $argument['M_Remark']
            //TODO 其他特殊用法：
            //【直接跳转发卡行网银】
            //（因测试环境所有商户号都默认不允许开通网银支付权限，所以要实现此功能需要使用正式申请的商户号去生产环境测试）：
            // 1）联系银联业务运营部门开通商户号的网银前置权限
            // 2）上送issInsCode字段，该字段的值参考《平台接入接口规范-第5部分-附录》（全渠道平台银行名称-简码对照表）
            //'issInsCode' => 'ABC',  //发卡机构代码
        );
        AcpService::sign ( $params );
        return $params;
    }


    /**
     * @see paymentplugin::getPaymentId()
     */
    public function getPaymentId(){
        return $this->paymentId;
    }

    /*
     * @param 获取配置参数
     */
    public function configParam() {
        $result = array(
            'M_merId' => '777290058118388',
            'M_certPwd' => '000000',
        );
        return $result;
    }
}