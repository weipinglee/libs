<?php
/**
 * 单元测试入口文件.
 * User: weipinglee
 * Date: 2018/6/22
 * Time: 16:11
 */


//
//spl_autoload_register(function($className){
//    $dir = dirname(dirname(__FILE__));
//    $file = $dir.'\\'.$className.'.php';
//    if(file_exists($file)){
//        include($file);
//    }
//
//
//});


//配置yaf
define('TEST_PATH',__DIR__);
date_default_timezone_set('Asia/Shanghai');
$app = new Yaf\Application(TEST_PATH."/conf/application.ini");
$config = Yaf\Application::app()->getConfig();
\Yaf\Registry::set('config', $config);

//$app->getDispatcher()->dispatch(new Yaf\Request_Simple());

