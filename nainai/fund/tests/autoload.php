<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/30
 * Time: 17:17
 */

namespace nainai\fund\tests;

spl_autoload_register(function($class){
    include "D:/wamp64/www/libs/".$class.'.php';
});