<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file stateBase.php
 * @brief ·¢²¼×´Ì¬Àà
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid\state;


/**
 * Class releaseState
 * @package nainai\state
 */
class releaseState extends stateBase
{
    public function init($args)
    {
        // TODO: Implement init() method.
    }

    public function release($pay_type){

    }

    public function verify($state,$mess='')
    {
        $newState = $state==1 ? self::BID_RELEASE_VERIFYSUCC : self::BID_RELEASE_VERIFYFAIL;
         $this->bidObj->verifyBid($this->bidID,$newState,$mess);
        return $this->bidObj->getSuccInfo();

    }

    public function bidRerelease($data){

    }

    public function bidCancle(){

    }

    public function bidClose(){

    }



    public function replyUploadCerts($reply_user_id,$certs){

    }

    public function replyCertsVerify($status){

    }

    public function replyCertDel($cert_id){
    }

    public function replyCertAdd($reply_id,$cert)
    {

    }


    public function replyDocUpload($upload){

    }

    public function replyPaydocFee($pay_type){

    }

    public function replySubmitPackage($data){

    }



}