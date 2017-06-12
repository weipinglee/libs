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

    }

    public function release($pay_type){

    }

    public function verify($state,$mess='')
    {


    }

    public function bidRerelease($data){

    }

    public function bidCancle()
    {
        $this->bidObj->beginTrans();
        $this->bidObj->cancleBid($this->bidID);
        $this->bidObj->setStatus($this->bidID,self::BID_CANCLE);
       return  $this->bidObj->commit();
    }

    public function bidClose()
    {
        $this->bidObj->beginTrans();
        $this->bidObj->cancleBid($this->bidID);
        $this->bidObj->setStatus($this->bidID,self::BID_CLOSE);
        return  $this->bidObj->commit();
    }



    public function replyUploadCerts($reply_user_id,$certs){
        $this->bidObj->beginTrans();
        $reply_id = $this->bidObj->createNewBidreply($this->bidID,$reply_user_id);
        if($reply_id){
            $this->bidObj->addReplyCerts($reply_id,$certs);
        }
        $this->bidObj->setStatus($this->bidID,self::REPLY_CREATE);
       return  $this->bidObj->commit($reply_id);
    }

    public function replySubmitCert()
    {
        // TODO: Implement replySubmitCert() method.
    }

    public function replyCertsVerify($status){

    }

    public function replyCertAdd($reply_id,$cert)
    {

    }

    public function replyCertDel($cert_id){

    }



    public function replyDocUpload($upload){

    }

    public function replyPaydocFee($pay_type){

    }

    public function replySubmitPackage($data,$upload){

    }

    public function bidStop()
    {
        $this->bidObj->beginTrans();
         $this->bidObj->setStatus($this->bidID,self::BID_STOP);
        return $this->bidObj->commit();
    }
}