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
         $tradeCode = 0;
         try{
             $messData = $this->receiveTranMessage();
             if(isset($messData['message']['head']['chanl_trad_no']))
                 $tradeCode = $messData['message']['head']['chanl_trad_no'];
             switch($tradeCode){
                 case '3FC019'://合同状态变更通知
                     $res = $this->orderStatuschg($messData);
                     break;
                 default://没有实现的请求返回成功报文
                     $res = true;
                     break;
             }
             if($res===true){//业务操作成功，返回成功报文
                 $returnMsg = $this->returnMsg;
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
     * 合同状态修改
     * @param array $messData
     * @return bool|array 成功返回true 失败返回数组
     */
     private function orderStatuschg($messData=array()){
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

     private function errCode($code,$msg){
         return array('resp_code'=>$code,'resp_msg'=>$msg);
     }

     private function succCode(){
         return array('resp_code'=>'000000000000','resp_msg'=>'SUCCESS');
     }
}