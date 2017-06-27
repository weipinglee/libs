<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file stateBase.php
 * @brief ÉóºË³É¹¦×´Ì¬Àà
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid\state;


class verifySuccState extends stateBase
{
    public function init($args)
    {
        return $this->errInfo;
    }

    public function release($pay_type){
        return $this->errInfo;
    }

    public function verify($state,$mess='')
    {
        return $this->errInfo;

    }

    public function bidRerelease($data){
        return $this->errInfo;
    }

    public function bidCancle()
    {
        $this->bidObj->cancleBid($this->bidID);
        $this->bidObj->beginTrans();
        $this->bidObj->setStatus($this->bidID,self::BID_CANCLE);
       return  $this->bidObj->commit();
    }

    public function bidClose()
    {
        $this->bidObj->cancleBid($this->bidID);
        $this->bidObj->beginTrans();
        $this->bidObj->setStatus($this->bidID,self::BID_CLOSE);
        return  $this->bidObj->commit();
    }



    public function replyUploadCerts($reply_user_id,$certs){
        $this->bidObj->beginTrans();
        $reply_id = $this->bidObj->createNewBidreply($this->bidID,$reply_user_id);
        if($reply_id){
            $this->bidObj->addReplyCerts($reply_id,$certs);
            $this->bidObj->setReplyStatus($reply_id,self::REPLY_CREATE);
        }

       return  $this->bidObj->commit($reply_id);
    }

    public function replySubmitCert()
    {
        return $this->errInfo;
    }

    public function replyCertsVerify($status){
        return $this->errInfo;
    }

    public function replyCertAdd($reply_id,$cert)
    {
        return $this->errInfo;
    }

    public function replyCertDel($cert_id,$reply_id){
        return $this->errInfo;
    }



    public function replyDocUpload($upload){
        return $this->errInfo;
    }

    public function replyPaydocFee($pay_type){
        return $this->errInfo;
    }

    public function replySubmitPackage($data,$upload){
        return $this->errInfo;
    }

    public function bidStop()
    {
        $this->bidObj->beginTrans();
         $this->bidObj->setStatus($this->bidID,self::BID_STOP);
        return $this->bidObj->commit();
    }

    public function addBidNotice($title,$content){
        $this->bidObj->addBidNotice($this->bidID,$title,$content);
        return $this->bidObj->getSuccInfo();
    }

    public function bidComment($content,$user_id)
    {
       $this->bidObj->beginTrans();
        $this->bidObj->addBidComment($this->bidID,$content,$user_id);
        return $this->bidObj->commit();
    }

    public function rebackReplyBail($bid_id){
        return $this->errInfo;
    }
}