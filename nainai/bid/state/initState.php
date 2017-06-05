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
        $this->bidObj->createNewBid($args);
    }

     public function release()
     {
        $this->bidObj->releaseBid($this->bidID);
     }

     public function verify($status)
     {
         $this->bidObj->verifyBid($this->bidID,$status);
     }

}