<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/29
 * Time: 15:05
 */

namespace nainai\fund\tests;
require 'D:/wamp64/vendor/autoload.php';
require 'autoload.php';
use PHPUnit\Framework\TestCase;
class test extends TestCase
{
      protected static $user1 = '';
      protected static $user2 = '';
      protected static $fundObj = null;
      protected $user1Active = 0;
      protected $user2Active = 0;
      protected $user1Freeze = 0;
      protected $user2Freeze = 0;

      public static function setUpBeforeClass(){
          self::$user1 = 1;
          self::$user2 = 2;
          self::$fundObj = new \nainai\fund\js(array('mainacc'=>'213423434'));
      }

      public function setup(){

      }

      public function testGet(){
          $res = true;
          $this->assertTrue($res,'²»ÊÇtrue');
      }


}