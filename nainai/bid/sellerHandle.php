<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/5 0005
 * Time: ���� 4:03
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
        $user_id = isset($this->replyData['reply_user_id']) ? $this->replyData['reply_user_id'] : 0;
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

    /**
     * ��ȡ�û�Ͷ������б���ͬ���б�
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
     * ��ȡһ���б����е�Ͷ����Ϣ
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
     * ��ȡͶ������
     * @param $id
     * @return array
     */
    public function getReplyDetail($id){
        $bidQuery = new bidQuery();
        return $bidQuery->getReplyDetail($id);
    }

    /**
     * ��ȡĳ���û���ĳ��Ͷ����б�����
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
     * ��ȡ��˳ɹ����б��б�
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