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
     return $this->errInfo;
    }

     public function release($pay_type)
     {return $this->errInfo;

     }

     public function verify($status,$mess='')
     {return $this->errInfo;

     }

     public function bidRerelease($data){
      return $this->errInfo;
     }

     public function bidCancle(){
      return $this->errInfo;
     }

     public function bidClose(){
      return $this->errInfo;
     }

     public function replyCreate(){
      return $this->errInfo;
     }

      public function replyUploadCerts($reply_user_id,$certs){
       return $this->errInfo;
      }

      public function replyCertsVerify($status){
       return $this->errInfo;
      }

      public function replyCertDel($cert_id){
       return $this->errInfo;
      }


      public function replyCertAdd($reply_id,$cert){
       return $this->errInfo;
      }

      public function replyPaydocFee($pay_type){
       return $this->errInfo;
      }

     public function replyDocUpload($upload){
      return $this->errInfo;

     }
     public function replySubmitPackage($data,$upload){
        $this->bidObj->beginTrans();
        if($this->bidObj->addReplyDoc($this->replyID,$upload)){
         $this->bidObj->replyPackage($this->replyID,$data);
         $this->bidObj->setReplyStatus($this->replyID,self::REPLY_PACKAGE_SUBMIT);
        }
        $res = $this->bidObj->commit();
      if($res['success']==1){
       $res['info'] = '投标成功，等待开标';
      }
      return $res;
     }

 public function bidStop()
 {
  return $this->errInfo;
 }

 public function replySubmitCert()
 {
  return $this->errInfo;
 }

}