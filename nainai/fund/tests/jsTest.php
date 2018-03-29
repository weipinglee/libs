<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/29
 * Time: 15:05
 */

namespace nainai\fund\tests;
require 'D:/wamp64/vendor/autoload.php';
use PHPUnit\Framework\TestCase;
class jsTest extends TestCase
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
          self::$fundObj = new \nainai\fund\js();
      }

      public function setup(){
          $this->user1Active = self::$fundObj->getActive(self::$user1);
          $this->user2Active = self::$fundObj->getActive(self::$user2);
          $this->user1Freeze = self::$fundObj->getFreeze(self::$user1);
          $this->user2Freeze = self::$fundObj->getFreeze(self::$user2);
      }

      public function testFundin(){
          $amount = 100;
          $active1 =   $this->user1Active;
          $res =  self::$fundObj->in(self::$user1,$amount);
          $this->assertTrue($res,'入金100元失败');
          $activeNow =  self::$fundObj->getActive(self::$user1);
          $this->assertEquals($activeNow,$active1+$amount,'入金后可用余额不等于之前余额加入金金额');

      }

    /**
     * @depends testFundin
     */
      public function testFundOut(){
           $amount = 3.5;
          $activeNow = $this->user1Active;
           $res = self::$fundObj->out(self::$user1,$amount);
          $this->assertTrue($res,'出金3.5失败');
          $active2 = self::$fundObj->getActive(self::$user1);
          $this->assertEquals($active2,$activeNow - $amount,'出金后可用余额计算不正确');

      }

    /**
     * @depends testFundOut
     */
      public function testFreezeBuyerMoney(){
          $amount = 10;
          $orderNo = 'nnw00001';
          $res1 = self::$fundObj->freeze(self::$user1,$amount,'',self::$user1,self::$user2,$orderNo);
          $this->assertTrue($res1,'冻结买方资金失败');
          $activeNow = self::$fundObj->getActive(self::$user1);
          $this->assertEquals($activeNow,$this->user1Active - $amount,'冻结后买方可用余额不正确');
          $freezeNow = self::$fundObj->getFreeze(self::$user1);
          $this->assertEquals($freezeNow,$this->user1Freeze + $amount,'冻结后买方冻结金额不正确');
      }

      public function testFreezeSellerMoney(){
          $amountFreeze = 10;
          $orderNo = 'nnw00001';
          $amountIn= 0;
          $res1 = self::$fundObj->freeze(self::$user2,$amountFreeze,'',self::$user1,self::$user2,$orderNo);
          if($this->user2Active<$amountFreeze){
              //TODO::判断返回是否是错误信息

              $amountIn = 50;
              self::$fundObj->in(self::$user2,$amountIn);
          }
          else{
              $this->assertTrue($res1,'冻结卖方资金失败');
          }

          $res2 = self::$fundObj->freeze(self::$user2,$amountFreeze,'',self::$user1,self::$user2,$orderNo);
          $this->assertTrue($res2,'冻结卖方资金失败');
          $activeNow = self::$fundObj->getActive(self::$user2);
          $this->assertEquals($activeNow,$this->user2Active + $amountIn - $amountFreeze,'冻结后卖方可用余额不正确');


      }

      public function testReleaseBuyerMoney(){

      }





      public static  function tearDownAfterClass(){

      }
}