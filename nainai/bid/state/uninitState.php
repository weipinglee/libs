<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file uninitState.php
 * @brief 招标未初始化类
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid\state;


class uninitState extends stateBase
{
    public function init($args)
    {
        $this->bidObj->beginTrans();
        $new_id = $this->bidObj->createNewBid($args);
        $this->bidObj->createNewPackage($new_id,$args['package']);
        $res = $this->bidObj->commit();
        $res['id'] = $new_id;
        return $res;
    }

    public function uploadBid()
    {
        return $this->bidObj->uploadBidDoc();
    }

    public function release($pay_type)
     {
         return $this->errInfo;
     }

     public function verify($status,$mess='')
     {
         return $this->errInfo;
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

    public function replyUploadCerts($reply_user_id,$cert){
        return $this->errInfo;
    }

    public function replyCertsVerify($status){
        return $this->errInfo;
    }

    public function replySubmitCert()
    {
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
        return $this->errInfo;
    }

    public function bidComment($content,$user_id)
    {
        return $this->errInfo;
    }

    public function rebackReplyBail($bid_id){
        return $this->errInfo;
    }


}