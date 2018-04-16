<?php
/**
 * 银行接口的http通信类
 * author: weipinglee
 * Date: 2018/1/16
 * Time: 下午 4:35
 */

namespace nainai\fund\communicate;


class http extends communicate
{
    private $encoding = '';
    public function __construct($encode='')
    {
        parent::__construct();
        $this->encoding = $encode;

    }

    public function sendRequest($param,$url)
     {
         $ch = curl_init($url);
         //$header = array('Content-type:text/html;charset=gbk');
         curl_setopt($ch,CURLOPT_URL,$url);
         curl_setopt($ch,CURLOPT_POST,1);
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
         //curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
         curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
         curl_setopt($ch,CURLOPT_POSTFIELDS,$param);
         $output = curl_exec($ch);
         if(curl_errno($ch)){
             return \Library\tool::getSuccInfo(0,curl_error($ch));
         }

          //$output = iconv($this->encoding,'UTF-8',$output);
          curl_close($ch);
          return $output;
     }

    public function receiveMessage()
    {
        return file_get_contents('php://input');

    }
}