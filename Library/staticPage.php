<?php
/**
 * 静态文件管理类
 * User: weipinglee
 * Date: 2018/3/7
 * Time: 下午 5:09
 */

namespace Library;
class staticPage{

    private static $dir = 'static';

    private static $request = null;

    private static $fileName = '';

    private static $postFix = '.html';

    public static function checkHasStatic($request){
         self::$request = $request;
        $expireTime = 3600;//过期时间，单位秒
         self::getFileName($request);
        //if  the static file is exist,and not expire ,include it.
        $fileName = self::$dir.'/'.self::$fileName;
         if(file_exists($fileName) && time()-filemtime($fileName)<$expireTime){
              include($fileName);exit;
         }

         return false;
    }

    private static function getFileName($request){
        $moduleName = $request->getModuleName();
        $controllerName = strtolower($request->getControllerName());
        $actionName = $request->getActionName();
        $params = $request->getParams();
        $fileName = '';
        $dir = '';
        if($controllerName!='index'){
            $dir .= $controllerName;
        }
        $fileName .= $actionName;
        if(!empty($params)){
            foreach($params as $val){
                $fileName .= '_'.$val;
            }
        }
        self::$dir = \Library\tool::getConfig(array('application','baseDir')).'/'.self::$dir.'/'.$dir;
        self::$fileName = $fileName.self::$postFix;

    }


    public static function createStaticFile(){
        if(!is_dir(self::$dir)){
             mkdir(self::$dir);
        }
        return self::$dir.'/'.self::$fileName;


    }
}