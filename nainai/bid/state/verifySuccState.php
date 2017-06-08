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

    public function bidCancle(){

    }

    public function bidClose(){

    }



    public function replyUploadCerts($reply_user_id,$certs){
        $this->bidObj->beginTrans();
        $reply_id = $this->bidObj->createNewBidreply($this->bidID,$reply_user_id);
        if($reply_id){
            $this->bidObj->addReplyCerts($reply_id,$certs);
        }
        $this->bidObj->setStatus($this->bidID,self::REPLY_DOC_UPLOADED);
       return  $this->bidObj->commit();
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

    public function replySubmitPackage($data){

    }
}