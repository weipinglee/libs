<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/30
 * Time: 11:14
 */

namespace nainai\fund\jianshe;

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
                    'version'=>'100',
                    'type'=> '0210',
                    'chanl_trad_no'=> 0,
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
        $parseRes = $this->messageObj->parse($xml);//�Ƚ�xml���ַ�������������
        return $parseRes;

    }

    /**
     * �����������ģ�������Ӧ��xml
     * @return mixed
     */
     public function handleRequest(){
         $tradeCode = 0;
         try{
             $messData = $this->receiveTranMessage();
             if(isset($messData['message']['head']['chanl_trad_no']))
                 $tradeCode = $messData['message']['head']['chanl_trad_no'];
             switch($tradeCode){
                 case '3FC019'://��ͬ״̬���֪ͨ
                     $res = $this->orderStatuschg($messData);
                     break;
                 default://û��ʵ�ֵ����󷵻سɹ�����
                     $res = true;
                     break;
             }
             if($res===true){//ҵ������ɹ������سɹ�����
                 $returnMsg = $this->returnMsg;
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
                         'version'=>'100',
                         'type'=> '0210',
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
     private function orderStatuschg($messData=array()){
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

     private function errCode($code,$msg){
         return array('resp_code'=>$code,'resp_msg'=>$msg);
     }

     private function succCode(){
         return array('resp_code'=>'000000000000','resp_msg'=>'SUCCESS');
     }
}