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
    private $encoding = '';
    private $config = array();
     public function __construct()
     {
         $this->configs = tool::getGlobalConfig(array('signBank','jianshe'));
         $this->mainacc = $this->configs['mainacc'];
         $this->encoding = 'gb2312';
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
     * @return array 返回报文数组
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

        $xmlReturn = '';//待写获取方法
        $signReturn = '';
        $xmlReturn = common::desEncryp($xmlReturn);
        if(common::verify($xmlReturn,$signReturn)){//验签成功
            return $this->parseTranMeasage($xmlReturn);
        }
        else{
            return tool::getSuccInfo(0,'验签失败');
        }


    }

    /**
     * 解析响应报文
     * @param string $message 待解析的响应报文
     */
    private function parseTranMeasage($message)
    {
        return $this->messageObj->parse($message);
    }


    public function getActive($user_id)
    {

    }

    public function getFreeze($user_id)
    {
        // TODO: Implement getFreeze() method.
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


        $res = $this->SendTranMessage($bodyParams,$code);


    }



}