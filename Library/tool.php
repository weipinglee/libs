<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/18 0018
 * Time: 上午 9:28
 */
namespace Library;
class tool{

    //全局配置
    private static $globalConfigs = array();
    /**
     * 获取application.ini中的配置项，并转化为数组
     * @param string $name 配置名称
     * @return mix 如果没有改配置信息则返回null
     */
    public static function getConfig($name=null){
        $configObj = \Yaf\Registry::get("config");
        if($configObj===false){
            $configObj = \Yaf\Application::app()->getConfig();
        }
        if($name!=null){
            if(!is_array($name)){
                $configObj = isset($configObj->$name) ? $configObj->$name : null;
            }
            else{
                foreach($name as $v){
                    if($configObj==null)break;
                    $configObj = isset($configObj->$v) ? $configObj->$v : null;
                }
            }

        }
        if(is_object($configObj))
            return $configObj->toArray();
        else if(is_null($configObj))
            return array();
        else return $configObj;
    }

    public static function getBasePath(){
        return APPLICATION_PATH.'/public/';
    }

    /**
     * 将图片路径加上@当前系统名
     * @param string $imgSrc 图片相对路径
     * @return string
     */
    public static function setImgApp($imgSrc){
        $name = self::getConfig(array('application','name'));
        if(!is_string($name)){
            $name = '';
        }
        return ($imgSrc!='' && strpos($imgSrc,'@')===false) ? $imgSrc.'@'.$name : $imgSrc;

    }

    //获取全局配置信息
    public static function getGlobalConfig($name=null){
        if(empty(self::$globalConfigs)){
            self::$globalConfigs = require self::getConfig(array('application','baseDir')).'/conf/configs.php';
        }

        if($name==null)
            return self::$globalConfigs;
        elseif(is_string($name))
            return isset(self::$globalConfigs[$name]) ?self::$globalConfigs[$name] : null ;
        else if(is_array($name)){
            $temp = self::$globalConfigs;
            foreach($name as $v){
                if(isset($temp[$v])){
                    $temp = $temp[$v];
                }
                else return null;
            }
            return $temp;
        }
    }

    public static function getSuccInfo($res=1,$info='',$url='',$id=''){
        return array('success'=>$res,'info'=>$info,'returnUrl'=>$url,'id'=>$id);
    }

    public static function create_uuid($user_id = 0){
        return date('YmdHis',time()).$user_id.substr(-1,3,time()).mt_rand(0,99);
    }

    //uuid
    // public static function create_uuid(){
    //     if (function_exists('com_create_guid')){
    //         return com_create_guid();
    //     }else{
    //         mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
    //         $charid = strtoupper(md5(uniqid(rand(), true)));
    //         $hyphen = chr(45);// "-"
    //         $uuid = substr($charid, 0, 8).$hyphen
    //                 .substr($charid, 8, 4).$hyphen
    //                 .substr($charid,12, 4).$hyphen
    //                 .substr($charid,16, 4).$hyphen
    //                 .substr($charid,20,12);
    //         return $uuid;
    //     }
    // }

    public static function pre_dump($data){
        echo '<pre>';

        print_r($data);
        echo '</pre>';
    }

    public static function getIP() { 
        if (getenv('HTTP_CLIENT_IP')) { 
            $ip = getenv('HTTP_CLIENT_IP'); 
        } 
        elseif (getenv('HTTP_X_FORWARDED_FOR')) { 
            $ip = getenv('HTTP_X_FORWARDED_FOR'); 
        } 
        elseif (getenv('HTTP_X_FORWARDED')) { 
            $ip = getenv('HTTP_X_FORWARDED'); 
        } 
        elseif (getenv('HTTP_FORWARDED_FOR')) { 
            $ip = getenv('HTTP_FORWARDED_FOR'); 

        } 
        elseif (getenv('HTTP_FORWARDED')) { 
            $ip = getenv('HTTP_FORWARDED'); 
        } 
        else { 
            $ip = $_SERVER['REMOTE_ADDR']; 
        } 

        if($ip == '::1')
            $ip = '127.0.0.1';
        return $ip; 
    } 

    public static function explode($str){
        return isset($str) && $str ? (strpos($str,',') ? explode($str,',') : array($str) ): array();
    }

    /**
     *数字金额转换成中文大写金额的函数
     *String Int $num 要转换的小写数字或小写字符串
     *return 大写字母
     *小数位为两位
     **/
    public static function toChineseNumber($num){
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角元拾佰仟万拾佰仟亿";
        //精确到分后面就不要了，所以只留两个小数位
        $num = round($num, 2);
        //将数字转化为整数
        $num = $num * 100;
        if (strlen($num) > 10) {
            return "金额太大，请检查";
        }
        $i = 0;
        $c = "";
        while (1) {
            if ($i == 0) {
                //获取最后一位数字
                $n = substr($num, strlen($num)-1, 1);
            } else {
                $n = $num % 10;
            }
            //每次将最后一位数字转化为中文
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                $c = $p1 . $p2 . $c;
            } else {
                $c = $p1 . $c;
            }
            $i = $i + 1;
            //去掉数字最后一位了
            $num = $num / 10;
            $num = (int)$num;
            //结束循环
            if ($num == 0) {
                break;
            }
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
            //utf8一个汉字相当3个字符
            $m = substr($c, $j, 6);
            //处理数字中很多0的情况,每次循环去掉一个汉字“零”
            if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                $left = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c = $left . $right;
                $j = $j-3;
                $slen = $slen-3;
            }
            $j = $j + 3;
        }
        //这个是为了去掉类似23.0中最后一个“零”字
        if (substr($c, strlen($c)-3, 3) == '零') {
            $c = substr($c, 0, strlen($c)-3);
        }
        //将处理的汉字加上“整”
        if (empty($c)) {
            return "零元整";
        }else{
            return $c . "整";
        }
    }


}