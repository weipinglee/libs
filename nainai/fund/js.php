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
    private $encoding = 'gb2312';
    private $config = array();
    private $errorText = '';//错误信息
    private $bankName  = 'js';
    private $bankTableObj = '';
    private $requestUrl = '/abutment/sendRequest';
     public function __construct()
     {
         $this->config = tool::getGlobalConfig(array('signBank','jianshe'));
         $this->mainacc = $this->config['mainacc'];
         $this->encoding = 'gb2312';
         $this->attachAccount = new attachAccount();
         $this->headParams = array(
             'version' => '100',
             'type' => '0200',
             'chanl_no' => '30',
             'chanl_sub_no' => '3001',
             'chanl_date'   => time::getDateTime('Ymd'),
             'chanl_time'   => time::getDateTime('His'),
             'chanl_flow_no'=> 'nnys'.rand(1000,9999),//生成随机的流水号
             'chanl_trad_no'=> '',

         );
         $this->bankTableObj = new M('user_attach');
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
        $xml = 'requestMessage='.$xml;
        $url = $this->config['ip'].':'.$this->config['port'].$this->requestUrl;
        $res = $this->communicateObj->sendRequest($xml,$url);
        if(isset($res['success'])&& $res['success']==0){//本地发生错误
            $this->errorText = $res['info'];
            return false;
        }
        //解析返回的xml
        $xmlReturn = $res;
		echo $res;
        $parseRes = $this->messageObj->parse($xmlReturn);//先将xml的字符串解析成数组
        //分析xml返回结果信息，是成功还是失败。发生在前置机端的错误也按照建行的响应报文头的规范返回
        return $this->analysisRes($parseRes);

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
        try {
            $code = '3FC006';
            $accInfo = $this->attachAccount->attachInfo($user_id, $this->bankName);
            if (empty($accInfo)) {
                throw new \Exception('该建行账户不存在');
            }
            $bodyParams = array(
                'FUNC_CODE' => 1,
                'MCH_NO' => $this->mainacc,
                'SIT_NO' => $accInfo['no'],//获取用户席位号
            );
            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            //根据$res拿到流水数据
            if (isset($res['AVL_BAL'])) {
                return $res['AVL_BAL'];
            }
            else{
                throw new \Exception('用户余额获取错误');
            }
        }catch (\Exception $e){
            return $e->getMessage();
        }

    }

    public function getFreeze($user_id)
    {
        try {
            $code = '3FC006';
            $accInfo = $this->attachAccount->attachInfo($user_id, $this->bankName);
            if (empty($accInfo)) {
                throw new \Exception('该建行账户不存在');
            }
            $bodyParams = array(
                'FUNC_CODE' => 1,
                'MCH_NO' => $this->mainacc,
                'SIT_NO' => $accInfo['no'],//获取用户席位号
            );
            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            //根据$res拿到流水数据
            if (isset($res['STX_SVC_FRZ_BAL']) && isset($res['STX_GUAR_FRZ_BAL'])) {
                return $res['STX_SVC_FRZ_BAL'] + $res['STX_GUAR_FRZ_BAL'];
            }
            else{
                throw new \Exception('用户冻结金额获取错误');
            }
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 查询
     * @param int $user_id 用户id
     * @param array $cond 查询条件
     */
    public function getFundFlow($user_id=0,$cond=array())
    {
        try {
            $code = '3FC007';
            if (!isset($cond['start']))
                $cond['start'] = '20000101';
            if (!isset($cond['end'])) {
                $cond['end'] = time::getDateTime('YMD');
            }
            $accInfo = $this->attachAccount->attachInfo($user_id, $this->bankName);
            if (empty($accInfo)) {
                throw new \Exception('该建行账户不存在');
            }
            $bodyParams = array(
                'FUNC_CODE' => 1,
                'MCH_NO' => $this->mainacc,
                'SIT_NO' => $accInfo['no'],//获取用户席位号
                'STRT_DT' => $cond['start'],
                'END_DT' => $cond['end'],
                'INQ_AMT_TYP' => 0
            );
            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            //根据$res拿到流水数据
            return $res;
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }



    /**
     * @param int $user_id 要冻结的用户id
     * @param float $num 金额
     * @param string $note
     * @param int $buyer_id 买方id
     * @param int $seller_id 卖方id
     * @param string $orderNo ,can't be empty
     * @param float $amount 合同总金额
     * @return bool|string
     */
    public function freeze($user_id, $num, $note = '',$buyer_id=0,$seller_id=0,$orderNo='',$amount=0)
    {
        $code = '3FC009';//交易代码

        try {
            //子账户的信息可能需要从数据库获取
            $buyerInfo = $this->attachAccount->attachInfo($buyer_id, $this->bankName);
            if (empty($buyerInfo)) {
                throw new \Exception('买方建行账户不存在');
            }
            $sellerInfo = $this->attachAccount->attachInfo($seller_id, $this->bankName);
            if (empty($sellerInfo)) {
                throw new \Exception('卖方建行账户不存在');
            }

            $bodyParams = array(
                'FUNC_CODE' => 0,
                'MCH_NO' => $this->mainacc,
                'BUYER_SIT_NO' => $buyerInfo['no'],
                'SELLER_SIT_NO' => $sellerInfo['no'],
                'CTRT_NO' => $orderNo,
                'CTRT_AMT' => $amount,
                'CURR_COD' => '01',
                'RMRK' => $note,
                'FIN_FLG' => 0,
                'FIN_AMT' => 0

            );
            if ($user_id == $buyer_id) {//冻结买方资金
                $bodyParams['BUYER_GUAR_PAY_AMT'] = $num;
            } elseif ($user_id == $seller_id) {
                $bodyParams['SELLER_GUAR_PAY_AMT'] = $num;
            }
            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            if (is_array($res)) {
                //如果成功，则发起强制付款通知
                $this->payNotice($buyer_id,$seller_id,$orderNo,$amount,time::getDateTime());
                return true;
            }
        }catch (\Exception $e){
            return $e->getMessage();
        }

        return false;

    }



    /**
     * 买方支付给卖方货款，不存在卖方支付给买方的情况
     * @param int $from 买方id
     * @param int $to 卖方id
     * @param float $num 金额
     * @param string $note
     * @param string $orderNo  订单号,必须输入
     * @param int $payTime 付款批次
     * @param string $orderTime 订单时间
     */
    public function freezePay($from, $to, $num, $note = '',$orderNo='',$payTime=1,$orderTime='')
    {
        $code = '3FC025';//交易代码

        //子账户的信息可能需要从数据库获取
        $buyer_id = $from;
        $seller_id = $to;
        try {
            $buyerInfo = $this->attachAccount->attachInfo($buyer_id, $this->bankName);
            if (empty($buyerInfo)) {
                throw new \Exception('买方建行账户不存在');
            }
            $sellerInfo = $this->attachAccount->attachInfo($seller_id, $this->bankName);
            if (empty($sellerInfo)) {
                throw new \Exception('卖方建行账户不存在');
            }


            $bodyParams = array(
                'MCH_NO' => $this->mainacc,
                'MCH_NAME' => '',
                'BUYER_SIT_NO' => $buyerInfo['no'],
                'SELLER_SIT_NO' => $sellerInfo['no'],
                'CTRT_NO' => $orderNo,
                'PAY_PRD_NO' => '0' . $payTime,
                'CURR_COD' => '01',
                'TX_AMT' => $num,
                'CTRT_TIME' => time::getDateTime('YmdHMS', $orderTime),
                'CURR_IDEN' => 0,
                'BUYER_PAY_UNFRZ_AMT' => $num

            );

            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            if (is_array($res)) {
                return true;
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }

        return false;
    }


    /**
     * 交易终止冻结支付
     * @param $from
     * @param $num
     * @param string $note
     * @param int $buyer_id
     * @param int $seller_id
     * @param string $orderNo
     * @param int $payTime
     * @return bool|string
     */
    public function breakFreezePay($from, $num, $note = '',$buyer_id=0,$seller_id=0,$orderNo='',$payTime=1){
        $code = '3FC011';//交易代码
        try {
            //子账户的信息需要从数据库获取
            $buyerInfo = $this->attachAccount->attachInfo($buyer_id, $this->bankName);
            if (empty($buyerInfo)) {
                throw new \Exception('买方建行账户不存在');
            }
            $sellerInfo = $this->attachAccount->attachInfo($seller_id, $this->bankName);
            if (empty($sellerInfo)) {
                throw new \Exception('卖方建行账户不存在');
            }


            $bodyParams = array(
                'MCH_NO' => $this->mainacc,
                'BUYER_SIT_NO' => $buyerInfo['no'],
                'SELLER_SIT_NO' => $sellerInfo['no'],
                'CTRT_NO' => $orderNo,
                'PAY_PRD_NO' => '00' ,
                'CURR_COD' => '01',
                'RMRK'    => $note,

            );
            if($from==$buyer_id){
                $bodyParams['BUYER_PEN_AMT'] = $num;
            }
            else{
                $bodyParams['SELLER_PEN_AMT'] = $num;
            }

            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            if (is_array($res)) {
                return true;
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }


        return false;
    }

    public function freezeRelease($user_id, $num, $note,$buyer_id=0,$seller_id=0,$orderNo='',$amount=0)
    {
        $code = '3FC009';//交易代码

        try {
            //子账户的信息可能需要从数据库获取
            $buyerInfo = $this->attachAccount->attachInfo($buyer_id, $this->bankName);
            if (empty($buyerInfo)) {
                throw new \Exception('买方建行账户不存在') ;
            }
            $sellerInfo = $this->attachAccount->attachInfo($seller_id, $this->bankName);
            if (empty($sellerInfo)) {
                throw new \Exception('卖方建行账户不存在');
            }

            $bodyParams = array(
                'FUNC_CODE' => 1,
                'MCH_NO' => $this->mainacc,
                'BUYER_SIT_NO' => $buyerInfo['no'],
                'SELLER_SIT_NO' => $sellerInfo['no'],
                'CTRT_NO' => $orderNo,
                'CTRT_AMT' => $amount,
                'CURR_COD' => '01',
                'RMRK' => $note,
                'FIN_FLG' => 0,
                'FIN_AMT' => 0

            );
            if ($user_id == $buyer_id) {//释放买方资金
                $bodyParams['BUYER_PAY_UNFRZ_AMT'] = $num;
            } elseif ($user_id == $seller_id) {
                $bodyParams['SELLER_PAY_UNFRZ_AMT'] = $num;
            }
            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            if (is_array($res)) {
                return true;
            }
        }catch (\Exception $e){
            return $e->getMessage();
        }

        return false;
    }

    /**
     * 强制付款通知,发起后才可以发起强制付款
     * @param int $buyer_id
     * @param int $seller_id
     * @param string $orderNo
     * @param float $amount
     * @param string $orderTime
     * @return bool|string
     */
    public function payNotice($buyer_id,$seller_id,$orderNo,$amount,$orderTime){
        $code = '3FC024';//交易代码

        try {
            //子账户的信息可能需要从数据库获取
            $buyerInfo = $this->attachAccount->attachInfo($buyer_id, $this->bankName);
            if (empty($buyerInfo)) {
                throw new \Exception('买方建行账户不存在') ;
            }
            $sellerInfo = $this->attachAccount->attachInfo($seller_id, $this->bankName);
            if (empty($sellerInfo)) {
                throw new \Exception('卖方建行账户不存在');
            }

            $bodyParams = array(
                'FUNC_CODE' => 1,
                'MCH_NO' => $this->mainacc,
                'MCH_NAME'=> '',
                'BUYER_SIT_NO' => $buyerInfo['no'],
                'SELLER_SIT_NO' => $sellerInfo['no'],
                'CTRT_NO' => $orderNo,
                'CTRT_AMT' => $amount,
                'CTRT_TIME' => time::getDateTime('YYYYmmddHHMMSS', $orderTime),
                'PAY_PRD_NO' => '',
            );

            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            if (is_array($res)) {
                return true;
            }
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function in($user_id, $num , $note='')
    {
        try{
            $code = '3FC002';//交易代码
            //子账户的信息可能需要从数据库获取
            $accInfo = $this->attachAccount->attachInfo($user_id,$this->bankName);
            if(empty($accInfo)){
                throw new \Exception('该建行账户不存在');
            }
            $bodyParams = array(
                'MCH_NO' => $this->mainacc,
                'CURR_COD' => '01',
                'TX_AMT' => $num,
                'IN_AMT_SIT_NO' => '',
                'RMRK' => $note
            );


            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams,$code);
            if($this->errorText!=''){
                throw new \Exception($this->errorText);
            }
            if(is_array($res)){
                return true;
            }

            return false;
        }catch(\Exception $e){
            return $e->getMessage();
        }

    }

    public function payMarket($user_id, $num,$note='')
    {
        try {
            $code = '3FC029';//交易代码
            //子账户的信息可能需要从数据库获取
            $accInfo = $this->attachAccount->attachInfo($user_id, $this->bankName);
            if (empty($accInfo)) {
                throw new \Exception('该建行账户不存在');
            }
            $bodyParams = array(
                'FUNC_CODE' => 1,
                'MCH_NO' => $this->mainacc,
                'SIT_NO' => $accInfo['no'],//商户结算专户
                'TX_AMT' => $num,
                'RMRK' => $note
            );


            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            if (is_array($res)) {
                return true;
            }

            return false;
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public function marketToUser($user_id, $num,$note='')
    {
        try {
            $code = '3FC029';//交易代码
            //子账户的信息可能需要从数据库获取
            $accInfo = $this->attachAccount->attachInfo($user_id, $this->bankName);
            if (empty($accInfo)) {
                throw new \Exception('该建行账户不存在');
            }
            $bodyParams = array(
                'FUNC_CODE' => 2,
                'MCH_NO' => $this->mainacc,
                'SIT_NO' => $accInfo['no'],//商户结算专户
                'TX_AMT' => -$num,
                'RMRK' => $note
            );


            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            if (is_array($res)) {
                return true;
            }

            return false;
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }


    public function out($user_id,$num,$note='')
    {
        try {
            $code = '3FC022';//交易代码
            //子账户的信息可能需要从数据库获取
            $accInfo = $this->attachAccount->attachInfo($user_id, $this->bankName);
            if (empty($accInfo)) {
                throw new \Exception('该建行账户不存在');
            }
            $bodyParams = array(
                'MCH_NO' => $this->mainacc,
                'FLOW_NO' => '',
                'DRAWEE_ACCT_NO' => '45345345345',//商户结算专户
                'PAYEE_ACCT_NO' => 'sdf1234234',//出金到的账户
                'CURR_COD' => '01',
                'TX_AMT' => $num,
                'OUT_AMT_SIT_NO' => '',//？
                'AUDIT_STS' => '1',
                'RMRK' => $note
            );


            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            if (is_array($res)) {
                return true;
            }

            return false;
        }catch (\Exception $e){
            return $e->getMessage();
        }

    }

    public function signedStatus($user_id)
    {
        try {
            $code = '3FC014';//交易代码
            //子账户的信息可能需要从数据库获取
            $accInfo = $this->attachAccount->attachInfo($user_id, $this->bankName);
            if (empty($accInfo)) {
                throw new \Exception('该建行账户不存在');
            }
            $bodyParams = array(
                'MCH_NO' => $this->mainacc,
                'FUNC_CODE' => 2,
                'CERT_TYPE' => $accInfo['id_type'],
                'CERT_NO' => $accInfo['id_card']

            );

            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            if (is_array($res)) {
                return true;
            }
            return false;
        }catch(\Exception $e){
            $e->getMessage();
        }

    }

    public function transSigninfo($user_id)
    {
        try {
            $code = '3FC001';//交易代码
            //子账户的信息可能需要从数据库获取
            $accInfo = $this->attachAccount->attachInfo($user_id, $this->bankName);
            if (empty($accInfo)) {
                throw new \Exception('该建行账户不存在');
            }
            $bodyParams = array(
                'MCH_NO' => $this->mainacc,
                'MBR_CERT_TYPE' => $accInfo['id_type'],
                'MBR_CERT_NO' => $accInfo['id_card'],
                'SPOT_SIT_NO' => $accInfo['no'],
                'MBR_NAME' => $accInfo['legal'],
                // 'MBR_SPE_ACCT_NO' => '',
                'MBR_CONTACT' => $accInfo['contact_name'],
                'MBR_PHONE_NUM' => $accInfo['contact_phone'],
                'MBR_ADDR' => $accInfo['address'],
                'MBR_INOUT_AMT_SVC_DRAWEE' => 1,
                'MBR_INOUT_AMT_SVC_RCV_STY' => 1,
                'SIGNED_DATE' => time::getDateTime('Ymd'),
                'MBR_STS' => 0,
                'RMRK' => ''

            );
            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            if (is_array($res)) {
                return true;
            }

            return false;
        }catch(\Exception $e){
            $e->getMessage();
        }

    }

    public function sendOut($bankFlowno,$status=1){
        try {
            $code = '3FC004';//交易代码
            $status = $status==1 ? 1 : 2;
            $bodyParams = array(
                'MCH_NO' => $this->mainacc,
                'FLOW_NO' => $bankFlowno,
                'AUDIT_STS' => $status,
                'RMRK' => ''

            );
            //得到响应报文并转化为数组
            $res = $this->SendTranMessage($bodyParams, $code);
            if ($this->errorText != '') {
                throw new \Exception($this->errorText);
            }
            if (is_array($res)) {
                return true;
            }

            return false;
        }catch(\Exception $e){
            $e->getMessage();
        }
    }

    private function rollback(){
        $M = new M('order_tobe');
        $M->rollBack();
    }



}