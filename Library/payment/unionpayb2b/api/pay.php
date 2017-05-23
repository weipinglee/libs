<?php
namespace Library\payment\unionpayb2b\api;
use \Library\payment\paymentplugin;
use \Library\payment\unionpayb2b\acpService;
/**
 * ��������֧������
 * User: Administrator
 * Date: 2017/4/11 0011
 * Time: ���� 3:28
 */
include_once dirname(dirname(__FILE__)) . "/SDKConfig.php";
class pay extends paymentplugin{

    protected $paymentId = 4;
    public $name = '����B2B֧��';//�������

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
        if (isset ( $callbackData['signature'] ))
        {
            if (AcpService::validate ( $callbackData ))
            {

                if ($callbackData["respCode"] == "00"){
                    //�����������ȴ����պ�̨֪ͨ���¶���״̬�����֪ͨ��ʱ��δ�յ�Ҳ�ɷ�����״̬��ѯ
                    $orderNo = $callbackData['orderId'];//������
                    if(isset($callbackData['queryId'])){
                        $flowNo  = $callbackData['queryId'];//��������ˮ��

                    }
                    $money   = $callbackData['txnAmt']/100;
                    return 1;
                } else if ($callbackData["respCode"] == "03"
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
            }
            else
            {
                $message = 'ǩ������ȷ';
            }
        }
        else
        {
            $message = 'ǩ��Ϊ��';
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

            //������Ϣ�������������Ҫ�Ķ�
            'version' => '5.0.0',                 //�汾��
            'encoding' => 'utf-8',				  //���뷽ʽ
            'txnType' => '01',				      //��������
            'txnSubType' => '01',				  //��������
            'bizType' => '000202',				  //ҵ������
            'frontUrl' =>  $argument['frontUrl'],//SDK_FRONT_NOTIFY_URL,  //ǰ̨֪ͨ��ַ
            'backUrl' => $argument['backUrl'],//SDK_BACK_NOTIFY_URL,	  //��̨֪ͨ��ַ
            'signMethod' => '01',	              //ǩ������
            'channelType' => '07',	              //�������ͣ�07-PC��08-�ֻ�
            'accessType' => '0',		          //��������
            'currencyCode' => '156',	          //���ױ��֣������̻��̶�156

            //TODO ������Ϣ��Ҫ��д
            'merId' => $argument["M_merId"],
            'orderId' => $argument["M_OrderNO"],
            'txnTime' => date('YmdHis'),
            'txnAmt' => $argument["M_Amount"]*100,
            // 		'reqReserved' =>'͸����Ϣ',        //���󷽱�����͸���ֶΣ���ѯ��֪ͨ�������ļ��о���ԭ�����֣�������Ҫ�����ò��޸��Լ�ϣ��͸��������
            'reqReserved' =>  $argument['M_OrderId'] . ":" . $argument['M_Remark']
            //TODO ���������÷���
            //��ֱ����ת������������
            //������Ի��������̻��Ŷ�Ĭ�ϲ�����ͨ����֧��Ȩ�ޣ�����Ҫʵ�ִ˹�����Ҫʹ����ʽ������̻���ȥ�����������ԣ���
            // 1����ϵ����ҵ����Ӫ���ſ�ͨ�̻��ŵ�����ǰ��Ȩ��
            // 2������issInsCode�ֶΣ����ֶε�ֵ�ο���ƽ̨����ӿڹ淶-��5����-��¼����ȫ����ƽ̨��������-������ձ�
            //'issInsCode' => 'ABC',  //������������
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
     * @param ��ȡ���ò���
     */
    public function configParam() {
        $result = array(
            'M_merId' => '777290058118388',
            'M_certPwd' => '000000',
        );
        return $result;
    }
}