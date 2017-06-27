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


class initState extends stateBase
{
    public function init($args)
    {
        return $this->errInfo;
    }

     public function release($pay_type)
     {
         $this->bidObj->beginTrans();
         $this->bidObj->payBidDeposit($this->bidID,$pay_type);
         $this->bidObj->setStatus($this->bidID,self::BID_RELEASE_WAITVERIFY);
         return $this->bidObj->commit();


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

    public function replyUploadCerts($reply_user_id,$certs){
        return $this->errInfo;
    }

    public function replySubmitCert()
    {
        return $this->errInfo;
    }

    public function replyCertsVerify($status){
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

    public function addBidNotice($title,$content){
        $this->bidObj->addBidNotice($this->bidID,$title,$content);
        return $this->bidObj->getSuccInfo();
    }

    public function bidComment($content,$user_id)
    {
        return $this->errInfo;
    }

    public function rebackReplyBail($bid_id){
        return $this->errInfo;
    }


}