<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/5 0005
 * Time: ÏÂÎç 4:02
 */

namespace nainai\bid;

use \nainai\bid\query\bidQuery;
class buyerHandle extends handle
{
    protected $queryObj = null;

    public function __construct($user_id)
    {
        parent::__construct($user_id);
        $this->queryObj = new bidQuery();
    }

    public function check(){
        $user_id = isset($this->bidData['user_id']) ? $this->bidData['user_id'] : 0;;
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
            'b.user_id =:user_id and br.bid_id=:bid_id and br.status >'.self::REPLY_CREATE,
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

    public function getBidNotice($id){
        $where = array(
            'n.bid_id=:bid_id and b.user_id=:user_id',
            array('bid_id'=>$id,'user_id'=>$this->operUserId)
        );
        return $this->queryObj->getBidNotice($where);
    }

    /**
     *
     * @param $reply_id int
     * @return array
     */
    public function getReplyCerts($reply_id)
    {
        $where = array('c.reply_id=:reply_id',array('reply_id'=>$reply_id));
        return $this->queryObj->getCertsList($where);
    }

}