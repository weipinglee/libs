<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file openBid.php
 * @brief 邀请招标类
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid\oper;


use nainai\bid\oper\bidOper;

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

    public function sendYqMessage($bid_id){
        $bidObj = new \Library\M($this->bidTable);
        $bidData = $bidObj->where(array('id'=>$bid_id))->fields('yq_user,no')->getObj();
        $yqUser = explode(',',$bidData['yq_user']);
        foreach($yqUser as $key=>$val){
            $messObj = new \nainai\message($val);
            $content = '招标<a href="'.\Library\url::createUrl('/bid/tbdetail').'?id='.$bid_id.'" >'.$bidData['no'].'</a>邀请您投标，请查看';
            $messObj->send('common',array($content,'邀请招标通知'));
        }

        return true;

    }
}