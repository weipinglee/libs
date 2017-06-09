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

    public function replyUploadCerts($reply_user_id,$cert){

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