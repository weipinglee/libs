<?php
/**
 * ��ֵҵ����
 * User: Administrator
 * Date: 2017/4/11 0011
 * Time: ���� 5:27
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
     * ��ֵǰ���������ɶ������ύ��֧��ҳ�棩
     * ��ҵ����صĲ������ݸ�֧����ת�����ύ��֧��ϵͳ
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
            //�ʽ�
            'amount' => $argument['account'],
            'create_time' => self::getDateTime(),
            'proot' => ' ',
            'status' => '0',
            //֧����ʽ
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
     * ֧����ص�������ͬ�����첽������ǩ�����Ķ���״̬���޸��˻�ֵ�������־
     * �ѵ�����ϵͳ���صĲ������ת����ҵ�����
     * @param array $argument �����ص����صĲ���
     * @return bool
     */
    public function payAfter(Array $argument=array())
    {
        //��ʼ������
        $money   = '';
        $message = '֧��ʧ��';
        $orderNo = '';
        $flowNo = '';

        //��֤ǩ��
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
                $userLog->addLog(['action'=>'��ֵ����','content'=>'��ֵ��'.$money.'Ԫ']);

                if($rechargeObj->commit()){
                    return true;
                }

            }
            $rechargeObj->rollBack();


        }

        return false;
    }


}