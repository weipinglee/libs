<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file stateBase.php
 * @brief �б��ʼ����
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid\state;


class replyCertedState extends stateBase
{
    public function init($args)
    {

    }

     public function release($pay_type)
     {

     }

     public function verify($status,$mess='')
     {

     }

     public function bidRerelease($data){

     }

     public function bidCancle(){

     }

     public function bidClose(){

     }

     public function replyCreate(){

     }

     public function replyUploadCerts($reply_user_id,$certs){

     }

    public function replySubmitCert()
    {

    }

     public function replyCertsVerify($status)
     {
         $newStatus = $status==1 ? self::REPLY_CERT_VERIFYSUCC : self::REPLY_CERT_VERIFYFAIL;
         return $this->bidObj->setReplyStatus($this->replyID,$newStatus);
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