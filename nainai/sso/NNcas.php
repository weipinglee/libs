<?php
/**
 * 资金操作类
 * author:weipinglee
 * Date: 2016/4/22
 * Time: 10:11
 */

namespace nainai\sso;
use \phpCAS;
use \Library\url;
class NNcas{

    private static $casObj = null;
    private static $cas_config = array();
    private $curUrl = '';
   public function __construct()
   {
         self::$cas_config = array(
             'version' => '2.0',
             'host' =>'shop.nz826.com',
             'port' =>443,
             'context'=>'cas',
             'server_ca_cert_path'=>'',

         );

       $this->client();
       phpCAS::setVerbose(true);


   }

    public function client(){
        phpCAS::client(self::$cas_config['version'],self::$cas_config['host'],self::$cas_config['port'],self::$cas_config['context']);
    }


    public  function checkServerLogin(){
        return phpCAS::checkAuthentication();

    }

    /**
     * 认证中心认证
     */
    public function serverAuthentication($account,$password,$callbackUrl=''){

        $postUrl = 'https://'.self::$cas_config['host'].':'.self::$cas_config['port'].'/'.self::$cas_config['context'].'/login';
        $service = $callbackUrl=='' ? url::createUrl('/') : $callbackUrl;
        $service = urlencode($service);
        $postUrl .= '?service='.$service;
        $header[] = "Location:".$postUrl;
        $header[] = 'HTTP/1.1 302 Moved Temporarily';
        $params = 'username='.$account.'&password='.$password;
        $ch = curl_init($postUrl);
        curl_setopt($ch,CURLOPT_URL,$postUrl);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_AUTOREFERER,true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$params);
        $output = curl_exec($ch);
        if(curl_errno($ch)){
            print curl_error($ch);
        }

        curl_close($ch);
    }

    public function getUser(){
        $this->client();
        return phpCAS::getUser();
    }
}