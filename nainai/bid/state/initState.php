<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file stateBase.php
 * @brief 招标初始化类
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid\state;


class initState extends stateBase
{
    public function init($args)
    {

    }

     public function release($pay_type)
     {
         $this->bidObj->beginTrans();
         $this->bidObj->payBidDeposit($this->bidID,$pay_type);
         $this->bidObj->setStatus($this->bidID,self::BID_RELEASE_WAITVERIFY);
         return $this->bidObj->commit();


     }

     public function verify($status)
     {

     }

}