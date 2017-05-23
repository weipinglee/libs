<?php
namespace Library\payment\unionpay\api;
use \Library\payment\paymentplugin;
use \Library\payment\unionpay\acpService;
/**
 * 银联网关支付子类
 * User: Administrator
 * Date: 2017/4/11 0011
 * Time: 下午 3:28
 */
include_once dirname(dirname(__FILE__)) . "/SDKConfig.php";

class pay extends paymentplugin{

    protected $paymentId = 3;
    public $name = '银联在线支付';//插件名称

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
        if (isset($callbackData['signature'])) {
            if (AcpService::validate ( $callbackData )) {

                if ($callbackData["respCode"] == "00"){
                    $orderNo = $callbackData['orderId'];//订单号
                    $flowNo  = $callbackData['queryId'];//第三方流水号
                    $money   = $callbackData['txnAmt']/100;//交易额
                    return 1;
                }
                else if ($callbackData["respCode"] == "03"
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

            } else {
                $message = '签名不正确';
            }
        } else {
            $message = '签名为空';
        }
        return 0;
    }


    /**
     * @see paymentplugin::getSendData()
     */
    public function getSendData($argument) {
        if(!$argument)
            return false;
       $payment = $this->getPaymentInfo();
        $argument = array_merge($argument,$payment);
        $return = array(
            'version' => '5.0.0', //版本号
            'encoding' => 'utf-8', //编码方式
            'txnType' => '01', //交易类型     //可能是活的
            'txnSubType' => '01', //交易子类 01消费
            'bizType' => '000201', //业务类型
            'frontUrl' => $argument['frontUrl'], //SDK_FRONT_NOTIFY_URL,  		//前台通知地址
            'backUrl' => $argument['backUrl'], //SDK_BACK_NOTIFY_URL,		//后台通知地址
            'signMethod' => '01', //签名方法
            'channelType' => '07', //渠道类型，07-PC，08-手机
            'accessType' => '0', //接入类型
            'merId' => $payment['M_merId'], //商户代码，请改自己的测试商户号
            'currencyCode' => '156', //交易币种
            'txnTime' => date('YmdHis'), //订单发送时间
			'orderId' => $argument['M_OrderNO'],//商户订单号
			'txnAmt'  => $argument['M_Amount'] * 100,//交易金额，单位分
			 'reqReserved' => $argument['M_OrderId'] . ":" . $payment['M_Remark'],//订单发送时间'透传信息'; //请求方保留域，透传字段，查询、通
            //'orderDesc' => '订单描述',  //订单描述，网关支付和wap支付暂时不起作用
        );
  
        // 签名
        AcpService::sign($return);
        return $return;
    }


    /**
     * @see paymentplugin::getPaymentId()
     */
    public function getPaymentId(){
        return $this->paymentId;
    }

}