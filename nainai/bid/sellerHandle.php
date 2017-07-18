<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/5 0005
 * Time: 下午 4:03
 */

namespace nainai\bid;
use Library\Query;
use \nainai\bid\query\bidQuery;

class sellerHandle extends handle
{
    public function check(){
        return false;
    }

    public function checkReply()
    {
        $user_id = isset($this->replyData['reply_user_id']) ? $this->replyData['reply_user_id'] : 0;
        if($user_id && $user_id==$this->operUserId)
            return true;
        return false;
    }

    public function getBidDetail($id)
    {
        $bidQuery = new bidQuery();
        $where = array(
            'b.status in('.self::BID_RELEASE_VERIFYSUCC.','.self::BID_STOP.','.self::BID_CLOSE.','.self::BID_CANCLE.','.
            self::BID_OVER.','.self::BID_ABORT.')'
        );
        return $bidQuery->getBidDetail($id,$where);

    }

    public function getUserReplyCerts($user_id,$bid_id)
    {
        $bidQuery = new bidQuery();
        return $bidQuery->getUserReplyCerts($user_id,$bid_id);

    }

    /**
     * 获取用户投过标的列表，不同的招标
     * @param int $page
     * @return array
     */
    public function getReplyList($page=1){
        $bidQuery = new bidQuery();
        $where = array(
            'br.reply_user_id =:user_id',
            array('user_id'=>$this->operUserId)

        );
        return $bidQuery->getReplyList($page,$where);
    }

    /**
     * 获取一个招标所有的投标信息
     */
    public function getOneBidReplyList($bid_id)
    {
        $bidQuery = new bidQuery();
        $where = array(
            'br.bid_id =:bid_id',
            array('bid_id'=>$bid_id)

        );
        return $bidQuery->getReplyList(1,$where);
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
    public function getZbInfo($reply_id,$user_id){
        $bidObj = new Query($this->bidReplyPackTable.' as brp');
        $bidObj->join = ' left join '.$this->bidPackageTable.' as bp on brp.pack_id = bp.id ';
        $bidObj->where = 'brp.reply_id=:reply_id';
        $bidObj->bind = array('reply_id'=>$reply_id);
       $bidObj->fields = 'brp.*,bp.win_user_id';
        return $bidObj->find();

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

    public function getBidNotice($bid_id){
        $bidQuery = new bidQuery();
        $where = array('n.bid_id = '.$bid_id);
        return $bidQuery->getBidNotice($where);
    }


}