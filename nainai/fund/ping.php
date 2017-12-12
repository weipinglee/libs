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
class ping extends account{


     private $mainacc = '';
     public function __construct()
     {
         $configs = tool::getGlobalConfig(array('signBank','pingan'));
         $this->mainacc = $configs['mainacc'];
     }

    /**
     * 生成随机的系统流水号
     * @return string
     */
    private function getSystemflowno(){
        return 'pingan'.'123456787';
    }
    /**
     * 生成请求银行的完整报文
     */
    private function getTranMessage($bodyParam,$transFunc)
    {
        //获取报文体字符串
        $bodyString = '';
        foreach($bodyParam as $val){
            $bodyString .= $val.'&';
        }
        $bodyString =  iconv('UTF-8','GBK',$bodyString);

        //获取业务报文头
        $flowNo = $this->getSystemflowno();
        $param = array(
            'TranFunc' => $transFunc,
            'bodyLen'=> sprintf("%08d", strlen($bodyString)),
            'flowNo'=> $flowNo,
            'qydm' => $this->mainacc
        );
        $tranMessageHead = $this->getTranMessageHead($param);

        //获取通讯报文头
        $paramNet = array(
            'bodyLen' => sprintf('010d',strlen($bodyString)+122),
            'flowNo'=> $flowNo,
            'qydm' => $this->mainacc
        );
        $tranMessageNetHead = $this->getTranMessageNetHead($paramNet);

        $tranMessage = $tranMessageNetHead.$tranMessageHead.$bodyString;
        iconv('UTF-8','GBK',$tranMessage);
        return $tranMessage;

    }
    /**
     * 生成请求银行的通讯报文头
     */
    private function getTranMessageNetHead($param)
    {
        $transParams = array(
           '1' => 'A001',
            '2'=> '03',
            '3' => '01',
            '4' => '01',
            '5' => $param['qydm'],
            '6' => $param['bodyLen'],
            '7' => '000000',
            '8' => '     ',
            '9' => '01',
            '10' => time::getDateTime('YmdHis'),//第10和11个字段
            '12' => $param['flowNo'],
            '13' => '000000',
            '14' => '                                                                                                    ',//100个空格
            '15' => ' ',
            '16' => '   ',
            '17' => ' ',
            '18' => ' ',
            '19' => '            ',
            '20' => '00000000000'//第20和21字段

        );
        $returnString = '';
        foreach($transParams as $val){
            $returnString .= $val;
        }
        return $returnString;
    }

    /**
     * 生成请求银行的业务报文头
     */
    private function getTranMessageHead($param)
    {
        $transParams = array(
            'TranFunc' => $param['TranFunc'],
            'ServType'=>'01',
            'MacCode' => '                ',
            'TranDateTime'=> time::getDateTime('YmdHis'),//合并了日期和时间两个字段两个字段
            'RspCode' => '999999',
            'RspMsg' => '                                          ',//42个空格
            'ConFlag' => '0',
            'Length' => $param['bodyLen'],
            'CounterId' => 'A0001',//是否需要从应用中获取
            'ThirdLogNo' => $param['flowNo'],
            'Qydm' => $param['qydm']

        );
        $returnString = '';
        foreach($transParams as $val){
            $returnString .= $val;
        }
        return $returnString;

    }


    /**
     * 生成报文，并接受银行返回
     */
    private function SendTranMessage($bodyParams,$transFunc)
    {
        $tranMessage = $this->getTranMessage($bodyParams,$transFunc);
        //SOCKET
        $configs = tool::getGlobalConfig(array('signBank','pingan'));
        $ip = isset($configs['ip']) ? $configs['ip'] : '';
        $port = isset($configs['port']) ? $configs['port'] : '';
        try{
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket === false) {
                throw new \Exception("socket_create() failed: reason: " . socket_strerror(socket_last_error()));

            }
            $result = socket_connect($socket, $ip, $port);
            if ($result === false) {
                throw new \Exception("socket_connect() failed:reason: " . socket_strerror(socket_last_error($socket)));
            }

            $res = socket_write($socket, $tranMessage, strlen($tranMessage));
            if($res===false){
                throw new \Exception("socket_write() failed:reason: " . socket_strerror(socket_last_error($socket)));
            }
            $res = socket_read($socket,'\0',PHP_BINARY_READ);
            if($res===false){//如果读失败了，服务端已经做了相关的操作，客户端没有做相关处理，怎么办
                throw new \Exception("socket_read() failed:reason: " . socket_strerror(socket_last_error($socket)));
                //此处是否需要记录交易流水号，以供后续查询，然后手动处理
            }
            socket_close($socket);
        }catch (\Exception $e){
            $errorMsg = $e->getMessage();
            //记录错误日志

        }

        //解析返回的字符串，并返回







    }

    private function parseTranMeasage($message){
        if(1){//响应报文，返回

        }
        else{//请求报文，返回请求函数和报文内容

        }
    }

    /**
     * 作为tcp服务器接收到信息后，做相应的处理
     * @param $message
     */
    public function serverCallback($message)
    {
       $res = $this->parseTranMeasage($message);
        //根据请求函数调用相应的方法，返回报文给tcp服务器

    }



    public function getActive($user_id)
    {
        //子账户的信息可能需要从数据库获取
        $bodyParams = array(
           // 'transFunc'=>1010,
            'SupAcctId'=>233,
            'ThirdCustId'=>'',
            'CustAcctId'=>'',
            'SelectFlag'=>2,
            'PageNum'=>1

        );
//        $string = '';

        $res = $this->SendTranMessage($bodyParams,1010);

        //拿到tcp返回的结果，处理


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


    public function out()
    {
        //TODO:
    }



}