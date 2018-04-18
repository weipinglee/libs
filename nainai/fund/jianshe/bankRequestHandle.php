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
     * 银行前置返回xml数据，客户端根据交易代码和其他参数做相应的业务处理
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
                    'ectip_date'   => '',//银行日期
                    'chanl_flow_no'=> 'nnys'.rand(1000,9999),//生成随机的流水号
                    'ectip_flow_no'=> '',//银行流水号
                    'chanl_trad_no'=> '',
                    'resp_code' => '000000000000',
                    'resp_msg'  => 'SUCCESS'
                )
            )
        );
    }

    /**
     * 获取请求报文并解析
     * @return array
     */
    private function receiveTranMessage(){
        $xml = $this->communicateObj->receiveMessage();
		$xml = base64_decode($xml);
		$model = new \Library\M('test');
		$model->data(array('json'=>$xml))->add();
        $parseRes = $this->messageObj->parse($xml);//先将xml的字符串解析成数据
        return $parseRes;

    }

    /**
     * 处理建行请求报文，返回响应的xml
     * @return mixed
     */
     public function handleRequest(){
         $tradeCode = 0;//交易码
         $bankDate = '';//银行日期
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
                 case '3FC019'://合同状态变更通知
                     $res = $this->orderStatuschg($messData,$transMessage);
                     break;
                 case '3FC008':
                     $res = $this->userSignInfo($messData,$transMessage);
                     break;
                 default://没有实现的请求返回成功报文
                     $res = true;
                     break;
             }
             if($res===true){//业务操作成功，返回成功报文
                 $returnMsg = $transMessage;
             }
             else{//res是包含错误代码和消息的数组
                 $returnMsg = array_merge($this->returnMsg['message']['head'],$res);
             }
             return $this->messageObj->create($returnMsg);

         }catch (\Exception $e){
             //捕获错误，并返回xml,一般不会调用
             $returnMsg = array(
                 'message'=>array(
                     'head'=>array(
                         'version' => '100',
                         'type' => '0200',
                         'chanl_no' => '30',
                         'chanl_sub_no' => '3001',
                         'chanl_date'   => time::getDateTime('Ymd'),
                         'chanl_time'   => time::getDateTime('His'),
                         'chanl_flow_no'=> 'nnys'.rand(1000,9999),//生成随机的流水号
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
     * 合同状态修改
     * @param array $messData
     * @return bool|array 成功返回true 失败返回数组
     */
     private function orderStatuschg($messData=array(),&$resData=array()){
        $orderNo = $messData['message']['body']['CTRT_NO'];
        $status = $messData['message']['body']['PAY_STS'];
         $res= true;
        if($status==2){ //终止交易
            $res = true;//调用合同接口
            //TODO:
        }
        elseif(in_array($status,array(1,4))){//付款成功
             //TODO:调用合同接口
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
         if($sit_no){//按席位查询
             $where = array('no'=>$sit_no);
         }elseif($cert_no){
             $where = array('id_card'=>$cert_no);
         }else{
             return $this->errCode('ERR002','席位号和证件号为空');
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
             return $this->errCode('ERR003','会员信息不存在');
         }

     }

     private function errCode($code,$msg){
         return array('resp_code'=>$code,'resp_msg'=>$msg);
     }

     private function succCode(){
         return array('resp_code'=>'000000000000','resp_msg'=>'SUCCESS');
     }
}