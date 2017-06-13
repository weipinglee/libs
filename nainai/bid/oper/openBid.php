<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file openBid.php
 * @brief 公开招标类
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid\oper;


use nainai\bid\oper\bidOper;

class openBid extends bidOper
{
    public function isInvite($user_id,$invite){
        return true;
    }
}