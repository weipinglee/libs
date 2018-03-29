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
          $this->assertTrue($res,'���100Ԫʧ��');
          $activeNow =  self::$fundObj->getActive(self::$user1);
          $this->assertEquals($activeNow,$active1+$amount,'�������������֮ǰ���������');

      }

    /**
     * @depends testFundin
     */
      public function testFundOut(){
           $amount = 3.5;
          $activeNow = $this->user1Active;
           $res = self::$fundObj->out(self::$user1,$amount);
          $this->assertTrue($res,'����3.5ʧ��');
          $active2 = self::$fundObj->getActive(self::$user1);
          $this->assertEquals($active2,$activeNow - $amount,'�������������㲻��ȷ');

      }

    /**
     * @depends testFundOut
     */
      public function testFreezeBuyerMoney(){
          $amount = 10;
          $orderNo = 'nnw00001';
          $res1 = self::$fundObj->freeze(self::$user1,$amount,'',self::$user1,self::$user2,$orderNo);
          $this->assertTrue($res1,'�������ʽ�ʧ��');
          $activeNow = self::$fundObj->getActive(self::$user1);
          $this->assertEquals($activeNow,$this->user1Active - $amount,'������򷽿�������ȷ');
          $freezeNow = self::$fundObj->getFreeze(self::$user1);
          $this->assertEquals($freezeNow,$this->user1Freeze + $amount,'������򷽶������ȷ');
      }

      public function testFreezeSellerMoney(){
          $amountFreeze = 10;
          $orderNo = 'nnw00001';
          $amountIn= 0;
          $res1 = self::$fundObj->freeze(self::$user2,$amountFreeze,'',self::$user1,self::$user2,$orderNo);
          if($this->user2Active<$amountFreeze){
              //TODO::�жϷ����Ƿ��Ǵ�����Ϣ

              $amountIn = 50;
              self::$fundObj->in(self::$user2,$amountIn);
          }
          else{
              $this->assertTrue($res1,'���������ʽ�ʧ��');
          }

          $res2 = self::$fundObj->freeze(self::$user2,$amountFreeze,'',self::$user1,self::$user2,$orderNo);
          $this->assertTrue($res2,'���������ʽ�ʧ��');
          $activeNow = self::$fundObj->getActive(self::$user2);
          $this->assertEquals($activeNow,$this->user2Active + $amountIn - $amountFreeze,'�����������������ȷ');


      }

      public function testReleaseBuyerMoney(){

      }





      public static  function tearDownAfterClass(){

      }
}