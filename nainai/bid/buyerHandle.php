<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/5 0005
 * Time: ���� 4:02
 */

namespace nainai\bid;

use \nainai\bid\query\bidQuery;
class buyerHandle extends handle
{
    public function check(){
        $bidObj = new \Library\M($this->bidTable);
        $user_id = $bidObj->where(array('id'=>$this->bidID))->getField('user_id');
        if($user_id && $user_id==$this->operUserId)
            return true;
        return false;
    }

    public function getBidList($page=1,$user_id=0){
        if(!$user_id)
            $user_id = $this->operUserId;
        $where = array(
            'b.user_id = :user_id',
            array('user_id'=>$user_id)
        );
        $query = new bidQuery();
        return $query->getBidList($page,$where);
    }

    public function getBidDetail($id)
    {
        $bidQuery = new bidQuery();
        $where = array(
            'b.user_id=:user_id',
            array('user_id'=>$this->operUserId)

        );
        return $bidQuery->getBidDetail($id,$where);

    }


    public function getReplyList($bid_id,$page=1){
        $bidQuery = new bidQuery();
        $where = array(
            'b.user_id =:user_id and br.bid_id=:bid_id',
            array('user_id'=>$this->operUserId,'bid_id'=>$bid_id)

        );
        return $bidQuery->getReplyList($page,$where);
    }

    public function getReplyPackList($bid_id){
        $bidQuery = new bidQuery();
        $where = array(
            'br.bid_id =:bid_id  ',
            array('bid_id'=>$bid_id)

        );
        return $bidQuery->getReplyPackList($where);
    }

    public function getPackCompareList($pack_ids){
        $bidQuery = new bidQuery();
        $where = array(
            'rp.id in ('.$pack_ids.') '

        );
        return $bidQuery->getReplyPackList($where);
    }

    public function getZbUser($bid_id){
        $bidQuery = new bidQuery();
        $where = array(
            'bp.bid_id=:bid_id',
            array('bid_id'=>$bid_id)
        );
        return $bidQuery->getZbUser($where);
    }

}