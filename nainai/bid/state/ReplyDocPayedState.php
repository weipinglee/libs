<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file stateBase.php
 * @brief 招标初始化类
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid\state;


class replyDocPayedState extends stateBase
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

      public function replyCertsVerify($status){

      }

      public function replyCertDel($cert_id){

      }


      public function replyCertAdd($reply_id,$cert){

      }

      public function replyPaydocFee($pay_type){

      }

     public function replyDocUpload($upload){
        $this->bidObj->beginTrans();
        if($this->bidObj->addReplyDoc($this->replyID,$upload)){
         $this->bidObj->setReplyStatus($this->replyID,self::REPLY_DOC_UPLOADED);
        }
        return $this->bidObj->commit();

     }
     public function replySubmitPackage($data){

     }

}