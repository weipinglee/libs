<?php
/**
 * 资金操作类
 * author:weipinglee
 * Date: 2016/4/22
 * Time: 10:11
 */

namespace nainai\sso;
use \phpCAS;
class NNcas{

    private static $casObj = null;
    private static $cas_config = array();
   public function __construct()
   {
         self::$cas_config = array(
             'version' => CAS_VERSION_2_0,
             'host' =>'',
             'port' =>'',
             'context'=>'',
             'server_ca_cert_path'=>'',

         );
       phpCAS::client(self::$cas_config['version'],self::$cas_config['host'],self::$cas_config['port'],self::$cas_config['context']);
   }

    /**
     * 验证是否在认证中心登录
     */
    public static function checkServerLogin(){
        return  phpCAS::checkAuthentication();

    }

    public static function getUser(){
        return phpCAS::getUser();
    }
}