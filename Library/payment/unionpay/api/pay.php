<?php
namespace Library\payment\unionpay\api;
use \Library\payment\paymentplugin;
use \Library\payment\unionpay\acpService;
/**
 * ��������֧������
 * User: Administrator
 * Date: 2017/4/11 0011
 * Time: ���� 3:28
 */
include_once dirname(dirname(__FILE__)) . "/SDKConfig.php";

class pay extends paymentplugin{

    protected $paymentId = 3;
    public $name = '��������֧��';//�������

    /**
     * @see paymentplugin::getSubmitUrl()
     */
    public function getSubmitUrl() {
        return SDK_FRONT_TRANS_URL;//�ύ��ַ
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
                    $orderNo = $callbackData['orderId'];//������
                    $flowNo  = $callbackData['queryId'];//��������ˮ��
                    $money   = $callbackData['txnAmt']/100;//���׶�
                    return 1;
                }
                else if ($callbackData["respCode"] == "03"
                    || $callbackData["respCode"] == "04"
                    || $callbackData["respCode"] == "05" ){
                    //�����跢����״̬��ѯ����ȷ������״̬
                    //TODO
                    $message = "����ʱ�����Ժ��ѯ";
                } else {
                    //����Ӧ��������ʧ�ܴ���
                    //TODO
                    $message = "ʧ�ܣ�" . $callbackData["respMsg"];
                }

            } else {
                $message = 'ǩ������ȷ';
            }
        } else {
            $message = 'ǩ��Ϊ��';
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
            'version' => '5.0.0', //�汾��
            'encoding' => 'utf-8', //���뷽ʽ
            'txnType' => '01', //��������     //�����ǻ��
            'txnSubType' => '01', //�������� 01����
            'bizType' => '000201', //ҵ������
            'frontUrl' => $argument['frontUrl'], //SDK_FRONT_NOTIFY_URL,  		//ǰ̨֪ͨ��ַ
            'backUrl' => $argument['backUrl'], //SDK_BACK_NOTIFY_URL,		//��̨֪ͨ��ַ
            'signMethod' => '01', //ǩ������
            'channelType' => '07', //�������ͣ�07-PC��08-�ֻ�
            'accessType' => '0', //��������
            'merId' => $payment['M_merId'], //�̻����룬����Լ��Ĳ����̻���
            'currencyCode' => '156', //���ױ���
            'txnTime' => date('YmdHis'), //��������ʱ��
			'orderId' => $argument['M_OrderNO'],//�̻�������
			'txnAmt'  => $argument['M_Amount'] * 100,//���׽���λ��
			 'reqReserved' => $argument['M_OrderId'] . ":" . $payment['M_Remark'],//��������ʱ��'͸����Ϣ'; //���󷽱�����͸���ֶΣ���ѯ��ͨ
            //'orderDesc' => '��������',  //��������������֧����wap֧����ʱ��������
        );
  
        // ǩ��
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