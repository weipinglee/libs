<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/5 0005
 * Time: 下午 4:03
 */

namespace nainai\bid;
use \nainai\bid\query\bidQuery;

class sellerHandle extends handle
{
    public function check(){
        return false;
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

    /**
     * 获取投标详情
     * @param $id
     * @return array
     */
    public function getReplyDetail($id){
        $bidQuery = new bidQuery();
        return $bidQuery->getReplyDetail($id);
    }

    /**
     * 获取某个用户对某个投标的中标详情
     * @param $bid_id
     * @param $user_id
     * @return array
     */
    public function getZbInfo($bid_id,$user_id){
        $bidQuery = new bidQuery();
        $where = array(
            'bp.bid_id=:bid_id and bp.win_user_id=:user_id',
            array('bid_id'=>$bid_id,'user_id'=>$user_id)
        );
        return $bidQuery->getZbUser($where);
    }

    /**
     * 获取审核成功的招标列表
     * @param $page
     */
    public function getBidList($page,$where=array()){

        $bidQuery = new bidQuery();
        if(empty($where)){
            $where = array(
                'b.status='.self::BID_RELEASE_VERIFYSUCC
            );
        }
        else{
            $where[0] = $where[0].' and b.status='.self::BID_RELEASE_VERIFYSUCC;

        }

        return $bidQuery->getBidList($page,$where);
    }


}