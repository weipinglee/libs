<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file stateBase.php
 * @brief 状态基类
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid\state;
use nainai\bid\oper\bidOper;
use Library\M;
use \Library\tool;
abstract class stateBase extends \nainai\bid\bidBase
{
    public $bidObj = null;
    public $bidID = 0;//操作的招标id
    public $replyID = 0;
    protected $errInfo ;
    public function __construct()
    {
        $this->bidObj = new bidOper();
        $this->errInfo = tool::getSuccInfo(0,'该状态不允许此操作');
    }


    /**
     *设置操作的招标id和投标id,招标操作类
     * @param $id int 招标id
     * @param $reply_id int 投标id
     * @param $operObj
     */
    public function _init($id,$reply_id,$operObj){
        $this->bidID = $id;
        $this->replyID = $reply_id;
        if($operObj){
            $this->bidObj = $operObj;
        }
        else
            $this->bidObj = new bidOper();
    }


    public function uploadBid(){}
    abstract public function init($args);//创建招标

    abstract public function release($pay_type);//支付保证金

    abstract public function verify($state,$mess='');//后台审核

    abstract public function bidRerelease($data);//驳回后更新信息重报，不扣保证金

    abstract public function bidCancle();

    abstract public function bidClose();

    abstract public function replyUploadCerts($reply_user_id,$certs);

    abstract public function replySubmitCert();//所有资质文件上传成功后，提交等待招标方审核资质

    abstract public function replyCertsVerify($status);

    abstract public function replyCertDel($cert_id,$reply_id);

    abstract public function replyCertAdd($reply_id,$cert);

    abstract public function replyPaydocFee($pay_type);

    abstract public function replyDocUpload($upload);

    abstract public function replySubmitPackage($data,$upload);

    abstract public function bidStop();

    public function pingbiao($reply_pack_id,$point,$status){}//评标

    public function pbClose($status){}

    public function addBidNotice($title,$content){

    }

    abstract public function bidComment($content,$user_id);

    /**
     * 退还指定招标的未中标用户保证金
     * @param $bid_id int 招标id
     * @return mixed
     */
    abstract public function rebackReplyBail($bid_id);







}