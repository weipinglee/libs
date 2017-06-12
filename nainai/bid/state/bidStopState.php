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


class bidStopState extends stateBase
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

     public function replySubmitCert()
     {
      // TODO: Implement replySubmitCert() method.
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

}