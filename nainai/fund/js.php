<?php
/**
 * 平安银行银企直连
 * author: liweiping
 * Date: 2017/12/8
 */

namespace nainai\fund;
use \Library\M;
use \Library\Time;
use \Library\tool;
use \nainai\fund\jianshe\common;
class js extends account{


    private $headParams = array();
     private $mainacc = '';
    private $messageObj = null;//处理报文的对象
    private $communicateObj = null;//通信对象
    private $attachAccount = null;
    private $encoding = '';
    private $config = array();
    private $errorText = '';//错误信息
    private $bankName  = 'js';
     public function __construct()
     {
         $this->configs = tool::getGlobalConfig(array('signBank','jianshe'));
         $this->mainacc = $this->configs['mainacc'];
         $this->encoding = 'gb2312';
         $this->attachAccount = new attachAccount();
         $this->headParams = array(
             'version' => '100',
             'type' => '0200',
             'chanl_no' => '30',
             'chanl_sub_no' => '01',
             'chanl_date'   => time::getDateTime('YMD'),
             'chanl_time'   => time::getDateTime('HMS'),
             'chanl_flow_no'=> '123456',//生成随机的流水号
             'chanl_trad_no'=> '',

         );
         $this->createMessageProduct();
         $this->createCommunicateProduct();
     }


    protected function createMessageProduct()
    {
        $this->messageObj = new \nainai\fund\messForm\xml($this->encoding,3);
    }

    protected function createCommunicateProduct()
    {
        $this->communicateObj = new \nainai\fund\communicate\http($this->encoding);
    }



    /**
     * 生成报文并提交，接收返回报文并解析成数组
     * @param $bodyParams
     * @param $tradeCode
     * @return array|string 返回报文数组 或错误信息字符串
     */
    private function SendTranMessage($bodyParams,$tradeCode)
    {
        $this->headParams['chanl_trad_no'] = $tradeCode;
        $xmlArr = array(
            'message'=> array(
                'head'=>$this->headParams,
                'body'=>$bodyParams
            )
        );
        $xml = $this->messageObj->create($xmlArr);//生成xml字符串

        //签名
        $sign = common::sign($xml);
        //加密
        $xml = common::desEncryp($xml);

        //通过http上传
        $param = array('xml'=>$xml,'sign'=>$sign);
        $url = $this->config['ip'].':'.$this->config['port'];
        $res = $this->communicateObj->sendRequest($param,$url);
        if(isset($res['success'])&& $res['success']==1){//有错误返回错误信息
            $this->errorText = $res['info'];
            return false;
        }
        $xmlReturn = '';//待写获取方法
        $signReturn = '';
        $xmlReturn = common::desEncryp($xmlReturn);
        if(common::verify($xmlReturn,$signReturn)){//验签成功
            $parseRes = $this->messageObj->parse($xmlReturn);//先将xml的字符串解析成数据
            return $this->analysisRes($parseRes);//分析xml返回结果信息，是成功还是失败
        }
        else{
            $this->errorText = '验签失败';
            return false;
        }


    }

    /**
     * 解析响应报文
     * @param string $message 待解析的响应报文
     * @return mixed 返回数组表示操作成功，各个接口根据业务需要获取内容，返回字符串表示操作失败
     */
    private function analysisRes($result)
    {
        //请求失败返回错误信息
        if(isset($result['message']['head']['resp_code']) && $result['message']['head']['resp_code']!='000000000000'){
            $this->errorText = '['.$result['message']['head']['resp_code'].']'.$result['message']['head']['resp_msg'];
            return false;
        }
        else{//成功的情况
            $res = $this->parseXmlArr($result['message']);
            $res['success'] = 1;//成功标志
            return $res;
        }

    }

    /**
     * 对有属性的xml字段进行解析，pin=true的字段解密，目前直接返回
     * @param $arr
     * @return array
     */
    private function parseXmlArr($arr){
        $res = array();
        foreach($arr as $key=>$val){
            if(is_array($val)&&isset($val['value'])){
                $res[$key] = $val['value'];
                if(isset($val['pin'])&& $val['pin']=='true'){
                    $res[$key] = $val['value'];//加密的进行解密
                }
            }
            elseif(!is_array($val)){
                $res[$key] = $val;
            }
            else{
                $res[$key] = $this->parseXmlArr($val);
             }

        }
        return $res;
    }



    public function getActive($user_id)
    {

    }

    public function getFreeze($user_id)
    {
        // TODO: Implement getFreeze() method.
    }

    /**
     * 查询
     * @param int $user_id 用户id
     * @param array $cond 查询条件
     */
    public function getFundFlow($user_id=0,$cond=array())
    {
        $code = '3FC007';
        if(!isset($cond['start']))
            $cond['start'] = '20000101';
        if(!isset($cond['end'])){
            return time::getDateTime('YMD');
        }
        $accInfo = $this->attachAccount->attachInfo($user_id,$this->bankName);
        if(empty($accInfo)){
            return '该建行账户不存在';
        }
        $bodyParams = array(
            'FUNC_CODE'=> 1,
            'MCH_NO'   => $this->mainacc,
            'SIT_NO'   => '',//获取用户席位号
            'STRT_DT'  => $cond['start'],
            'END_DT'   => $cond['end'],
            'INQ_AMT_TYP' => 0
        );
        //得到响应报文并转化为数组
        $res = $this->SendTranMessage($bodyParams,$code);
        if($this->errorText!=''){
            return $this->errorText;
        }
        //根据$res拿到流水数据
        return array();
    }

    public function freeze($user_id, $num, $clientID = '')
    {
        // TODO: Implement freeze() method.
    }

    public function freezePay($from, $to, $num, $note = '', $amount = '')
    {
        // TODO: Implement freezePay() method.
    }

    public function freezeRelease($user_id, $num, $note, $freezeno = '')
    {
        // TODO: Implement freezeRelease() method.
    }

    public function in($user_id, $num)
    {
        // TODO: Implement in() method.
    }

    public function payMarket($user_id, $num)
    {
        // TODO: Implement payMarket() method.
    }


    public function out($user_id,$num,$note='')
    {
        $code = '3FC022';//交易代码
        //子账户的信息可能需要从数据库获取
        $accInfo = $this->attachAccount->attachInfo($user_id,$this->bankName);
        if(empty($accInfo)){
            return '该建行账户不存在';
        }
        $bodyParams = array(
            'MCH_NO' => $this->mainacc,
            'FLOW_NO' => '',
            'DRAWEE_ACCT_NO' => '45345345345',//商户结算专户
            'PAYEE_ACCT_NO'  => 'sdf1234234',//出金到的账户
            'CURR_COD' => '01',
            'TX_AMT' => $num,
            'OUT_AMT_SIT_NO' => '',//？
            'AUDIT_STS' => '1',
            'RMRK' => $note
        );


        //得到响应报文并转化为数组
        $res = $this->SendTranMessage($bodyParams,$code);
       if($this->errorText!=''){
           return $this->errorText;
       }
        if(is_array($res)){
            return true;
        }

        return false;

    }

}