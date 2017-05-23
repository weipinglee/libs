<?php
/**
 * 充值业务类
 * User: Administrator
 * Date: 2017/4/11 0011
 * Time: 下午 5:27
 */
namespace nainai\payment;
use Library\M;
use Library\url;
class recharge extends payment{

    public function __construct($pay)
    {
        parent::__construct($pay);
        $this->callbackUrl = url::createUrl('/fund/rechargeCallback?id='.$this->paymentId.'@user');
        $this->serverCallback = url::createUrl('/login/rechargeServerCallback?id='.$this->paymentId.'@user');
    }

    /**
     * 充值前操作（生成订单，提交到支付页面）
     * 把业务相关的参数传递给支付类转化后提交给支付系统
     * @param array $argument
     * @return bool
     */
    public function payBefore(Array $argument=array())
    {
        if (!isset($argument['user_id']) || !isset($argument['account']) || $argument['account'] <= 0) {
            return false;

        }

        $rechargeObj = new M('recharge_order');

        $pay_type = $this->payObj->getPaymentId();
        $reData = array(
            'id' => null,
            'user_id' => $argument['user_id'],
            'order_no' => 'recharge'.self::createOrderNum(),
            //资金
            'amount' => $argument['account'],
            'create_time' => self::getDateTime(),
            'proot' => ' ',
            'status' => '0',
            //支付方式
            'pay_type' => $pay_type,
        );
        if($r_id = $rechargeObj->data($reData)->add()){
            $return = array(
                'M_OrderId'=>$r_id,
                'M_OrderNO'=>$reData['order_no'],
                'M_Amount'=>$argument['account'],
                'frontUrl' => $this->callbackUrl,
                'backUrl' => $this->serverCallback
            );

            return $this->payObj->dopay($this->payObj->getSendData($return));
        }
        else{
            return false;
        }

    }

    /**
     * 支付后回调操作（同步和异步）：验签、更改订单状态、修改账户值、添加日志
     * 把第三方系统返回的参数检出转换成业务参数
     * @param array $argument 银联回调返回的参数
     * @return bool
     */
    public function payAfter(Array $argument=array())
    {
        //初始化参数
        $money   = '';
        $message = '支付失败';
        $orderNo = '';
        $flowNo = '';

        //验证签名
        $return = $this->payObj->callbackVerify($argument,$money,$message,$orderNo,$flowNo);

        if($return){
            $rechargeObj = new M('recharge_order');
            $rechargeObj->where(array('order_no'=>$orderNo));
            $rechargeRow = $rechargeObj->getObj();
            if (empty($rechargeRow)) {
                return false;
            }

            if ($rechargeRow['status'] == 1) {
                return true;
            }

            $dataArray = array(
                'status' => 1,
                'proot'  => $flowNo
            );
            $rechargeObj->beginTrans();
            $rechargeObj->where(array('order_no'=>$orderNo))->data($dataArray)->update();

            $userid = $rechargeRow['user_id'];
            $fund =  new \nainai\fund\agentAccount();
            $fundRes = $fund->in($userid, $money);

            if($fundRes===true)
            {
                $userLog=new \Library\userLog();
                $userLog->addLog(['action'=>'充值操作','content'=>'充值了'.$money.'元']);

                if($rechargeObj->commit()){
                    return true;
                }

            }
            $rechargeObj->rollBack();


        }

        return false;
    }


}