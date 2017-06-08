<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file openBid.php
 * @brief бћЧыеаБъРр
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid\oper;


use nainai\state\bidOper;

class privateBid extends bidOper
{

    public function isInvite($user_id,$invite)
    {
        if($invite){
            $invite_arr = explode(',',$invite);
            if(in_array($user_id,$invite_arr)){
                return true;
            }
        }
        return false;

    }
}