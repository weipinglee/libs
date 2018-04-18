<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/30
 * Time: 11:14
 */

namespace nainai\fund\jianshe;
use \Library\time;
class bankRequestHandle
{

    private $messageObj = null;
    private $communicateObj = null;
    private $encoding = 'gb2312';
    private $returnMsg = array();
    /**
     * ����ǰ�÷���xml���ݣ��ͻ��˸��ݽ��״����������������Ӧ��ҵ����
     * @return mixed
     * @throws \Exception
     */
    public function __construct()
    {
        $this->messageObj = new \nainai\fund\messForm\xml($this->encoding,3);
        $this->communicateObj = new \nainai\fund\communicate\http($this->encoding);
        $this->returnMsg = array(
            'message'=>array(
                'head'=>array(
                    'version' => '100',
                    'type' => '0200',
                    'chanl_no' => '30',
                    'chanl_sub_no' => '3001',
                    'ectip_date'   => '',//��������
                    'chanl_flow_no'=> 'nnys'.rand(1000,9999),//�����������ˮ��
                    'ectip_flow_no'=> '',//������ˮ��
                    'chanl_trad_no'=> '',
                    'resp_code' => '000000000000',
                    'resp_msg'  => 'SUCCESS'
                )
            )
        );
    }

    /**
     * ��ȡ�����Ĳ�����
     * @return array
     */
    private function receiveTranMessage(){
        $xml = $this->communicateObj->receiveMessage();
		$xml = base64_decode($xml);
		$model = new \Library\M('test');
		$model->data(array('json'=>$xml))->add();
        $parseRes = $this->messageObj->parse($xml);//�Ƚ�xml���ַ�������������
        return $parseRes;

    }

    /**
     * �����������ģ�������Ӧ��xml
     * @return mixed
     */
     public function handleRequest(){
         $tradeCode = 0;//������
         $bankDate = '';//��������
         $bankFlow = '';
         try{
             $messData = $this->receiveTranMessage();
             if(isset($messData['message']['head']['chanl_trad_no']))
                 $tradeCode = $messData['message']['head']['chanl_trad_no'];
             if(isset($messData['message']['head']['chanl_date']))
                 $bankDate = $messData['message']['head']['chanl_date'];
             if(isset($messData['message']['head']['chanl_date']))
                 $bankFlow = $messData['message']['head']['chanl_flow_no'];
             $transMessage = $this->returnMsg;
             $transMessage['message']['head']['ectip_date'] = $bankDate;
             $transMessage['message']['head']['chanl_trad_no'] = $tradeCode;
             $transMessage['message']['head']['ectip_flow_no'] = $bankFlow;
             switch($tradeCode){
                 case '3FC019'://��ͬ״̬���֪ͨ
                     $res = $this->orderStatuschg($messData,$transMessage);
                     break;
                 case '3FC008':
                     $res = $this->userSignInfo($messData,$transMessage);
                     break;
                 default://û��ʵ�ֵ����󷵻سɹ�����
                     $res = true;
                     break;
             }
             if($res===true){//ҵ������ɹ������سɹ�����
                 $returnMsg = $transMessage;
             }
             else{//res�ǰ�������������Ϣ������
                 $returnMsg = array_merge($this->returnMsg['message']['head'],$res);
             }
             return $this->messageObj->create($returnMsg);

         }catch (\Exception $e){
             //������󣬲�����xml,һ�㲻�����
             $returnMsg = array(
                 'message'=>array(
                     'head'=>array(
                         'version' => '100',
                         'type' => '0200',
                         'chanl_no' => '30',
                         'chanl_sub_no' => '3001',
                         'chanl_date'   => time::getDateTime('Ymd'),
                         'chanl_time'   => time::getDateTime('His'),
                         'chanl_flow_no'=> 'nnys'.rand(1000,9999),//�����������ˮ��
                         'chanl_trad_no'=> $tradeCode,
                         'resp_code' => 'ERR001',
                         'resp_msg'  => $e->getMessage()
                     )
                 )
             );
             return $this->messageObj->create($returnMsg);

         }

     }

    /**
     * ��ͬ״̬�޸�
     * @param array $messData
     * @return bool|array �ɹ�����true ʧ�ܷ�������
     */
     private function orderStatuschg($messData=array(),&$resData=array()){
        $orderNo = $messData['message']['body']['CTRT_NO'];
        $status = $messData['message']['body']['PAY_STS'];
         $res= true;
        if($status==2){ //��ֹ����
            $res = true;//���ú�ͬ�ӿ�
            //TODO:
        }
        elseif(in_array($status,array(1,4))){//����ɹ�
             //TODO:���ú�ͬ�ӿ�
        }

         if($res!==true){
             return $this->errCode($res['code'],$res['msg']);
         }
        return true;
     }

     private function userSignInfo($rqData=array(),&$resData=array()){
         $messData = $rqData;
         $sit_no = isset($messData['message']['body']['SIT_NO']) ? $messData['message']['body']['SIT_NO'] : '';
         $cert_no = isset($messData['message']['body']['CERT_NO']) ? $messData['message']['body']['CERT_NO'] : '';
         if($sit_no){//��ϯλ��ѯ
             $where = array('no'=>$sit_no);
         }elseif($cert_no){
             $where = array('id_card'=>$cert_no);
         }else{
             return $this->errCode('ERR002','ϯλ�ź�֤����Ϊ��');
         }
         $userObj = new \Library\M('user_attach');
         $data = $userObj->where($where)->getObj();
         if(!empty($data)){
             $resData['message']['body'] = array(
                 'MBR_CERT_TYPE'=>$data['id_type'],
                 'MBR_CERT_NO'  => $data['id_card'],
                 'SPOT_SIT_NO'  => $data['no'],
                 'MBR_NAME'     => $data['name'],
                 'MBR_ANNUAL_FEE_AMT' => '0.0',
                 'MBR_INOUT_AMT_SVC_AMT'=> '0.0',
                 'MBR_INOUT_AMT_SVC_DRAWEE' =>1,
                 'MBR_INOUT_AMT_SVC_RCV_STY'=>1,
                 'SIGNED_DATE'  => time::getDateTime('Ymd'),
                 'MBR_STS'      => 0
             );
             return true;
         }else{
             return $this->errCode('ERR003','��Ա��Ϣ������');
         }

     }

     private function errCode($code,$msg){
         return array('resp_code'=>$code,'resp_msg'=>$msg);
     }

     private function succCode(){
         return array('resp_code'=>'000000000000','resp_msg'=>'SUCCESS');
     }
}