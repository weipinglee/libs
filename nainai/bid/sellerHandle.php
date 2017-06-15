<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/5 0005
 * Time: ÏÂÎç 4:03
 */

namespace nainai\bid;
use \nainai\bid\query\bidQuery;

class sellerHandle extends handle
{
    public function check(){

    }

    public function checkReply()
    {
        $bidObj = new \Library\M($this->bidReplyTable);
        $user_id = $bidObj->where(array('id'=>$this->replyID))->getField('reply_user_id');
        if($user_id && $user_id==$this->operUserId)
            return true;
        return false;
    }

    public function getBidDetail($id)
    {
        $bidQuery = new bidQuery();
        $where = array(
            'b.status=:status',
            array('status'=>self::BID_RELEASE_VERIFYSUCC)

        );
        return $bidQuery->getBidDetail($id,$where);

    }

    public function getUserReplyCerts($user_id,$bid_id)
    {
        $bidQuery = new bidQuery();
        return $bidQuery->getUserReplyCerts($user_id,$bid_id);

    }

    public function getReplyList($page=1){
        $bidQuery = new bidQuery();
        $where = array(
            'br.reply_user_id =:user_id',
            array('user_id'=>$this->operUserId)

        );
        return $bidQuery->getReplyList($page,$where);
    }

    public function getReplyDetail($id){
        $bidQuery = new bidQuery();
        return $bidQuery->getReplyDetail($id);
    }

    public function getZbInfo($bid_id,$user_id){
        $bidQuery = new bidQuery();
        $where = array(
            'bp.bid_id=:bid_id and bp.win_user_id=:user_id',
            array('bid_id'=>$bid_id,'user_id'=>$user_id)
        );
        return $bidQuery->getZbUser($where);
    }
}