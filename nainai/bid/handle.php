<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file handle.php
 * @brief ������
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid;

use \Library\M;
abstract class handle extends \nainai\bid\state\stateBase
{
    public $stateObj = null;
    public $operObj  = null;
    public $operUserId = 0;
    public $bidID = 0;
    public $replyID = 0;

    public function __construct($user_id=0)
    {
        $this->operUserId = $user_id;
    }

    //����״̬����
    public function setStateObj($type='bid',$id=0)
    {
        $bid_id= 0;
        $reply_id = 0;
        if($type=='bid'){
            $this->getStateObj($id,0);
            $bid_id = $id;
            $reply_id = 0;
        }
        elseif($type=='reply'){
            $reply_id = $id;
            $replyObj = new M($this->bidReplyTable);
            $replyData = $replyObj->where(array('id'=>$id))->fields('bid_id,status')->getObj();

            if(!empty($replyData)){
                $this->getStateObj($replyData['bid_id'],$id,$replyData);
                $bid_id = $replyData['bid_id'];
            }

        }
        $this->bidID = $bid_id;
        $this->replyID = $reply_id;
        if($this->stateObj){
            $this->stateObj->_init($bid_id,$reply_id,$this->operObj);
        }

    }

    /**
     * ��ȡ������
     * @param $mode
     */
    private function getOperClass($mode){
        //��ȡ������
        switch($mode){
            case 'gk' :
                $this->operObj = new \nainai\bid\oper\openBid();
                break;
            case 'yq' :
                $this->operObj = new \nainai\bid\oper\privateBid();
                break;
        }
    }

    /**
     * ����״̬����
     * @param $bid_id int �б�id
     * @param $reply_id int Ͷ��id
     * @param array $replyData Ͷ�����ݣ���������ֵ���򲻱��ظ���ȡͶ������
     */
    private function getStateObj($bid_id,$reply_id,$replyData=array()){
        if(!$bid_id)
            $this->stateObj = new \nainai\bid\state\uninitState();
        else{
            $bidObj = new M($this->bidTable);
            $bidData = $bidObj->where(array('id'=>$bid_id))->fields('status,mode')->getObj();
            if(!empty($bidData) ){
                $this->getOperClass($bidData['mode']);
                //��ȡ״̬��
                switch($bidData['status']){
                    case self::BID_INIT : {
                        $this->stateObj = new \nainai\bid\state\initState();
                    }
                        break;
                    case self::BID_RELEASE_WAITVERIFY :
                        $this->stateObj = new \nainai\bid\state\releaseState();
                        break;
                    case self::BID_RELEASE_VERIFYFAIL:
                        $this->stateObj = new \nainai\bid\state\verifyFailState();
                        break;
                    case self::BID_RELEASE_VERIFYSUCC:
                        $this->stateObj = new \nainai\bid\state\verifySuccState();
                        break;
                    case self::BID_STOP :
                        $this->stateObj = new \nainai\bid\state\bidStopState();
                    case self::BID_CANCLE :
                        $this->stateObj = new \nainai\bid\state\bidCancleState();
                        break;
                    case self::BID_CLOSE :
                        $this->stateObj = new \nainai\bid\state\bidCloseState();
                        break;
                }

                //Ͷ��id��Ϊ0���б�״̬Ϊ�ɹ�������Ͷ��״̬����
                if($reply_id!=0 && $bidData['status']==self::BID_RELEASE_VERIFYSUCC){
                    if(empty($replyData)){
                        $replyObj = new M($this->bidReplyTable);
                        $replyData = $replyObj->where(array('id'=>$reply_id))->fields('status,bid_id')->getObj();

                    }
                    if(!empty($replyData)){
                        switch($replyData['status']){
                            case self::REPLY_CREATE :
                                $this->stateObj = new \nainai\bid\state\replyCreateState();
                                break;
                            case self::REPLY_CERTED :
                                $this->stateObj = new \nainai\bid\state\replyCertedState();
                                break;
                            case self::REPLY_CERT_VERIFYFAIL :
                                $this->stateObj = new \nainai\bid\state\replyCertVerifyfailState();
                                break;
                            case self::REPLY_CERT_VERIFYSUCC :
                                $this->stateObj = new \nainai\bid\state\replyCertVerifysuccState();
                                break;
                            case self::REPLY_DOC_PAYED :
                                $this->stateObj = new \nainai\bid\state\replyDocPayedState();
                                break;
                            case self::REPLY_DOC_UPLOADED :
                                $this->stateObj = new \nainai\bid\state\replyDocUploadedState();
                                break;
                            case self::REPLY_PACKAGE_SUBMIT :
                                $this->stateObj = new \nainai\bid\state\replyPackageSubmitState();
                                break;

                        }
                    }

                }


            }
        }

    }

   public function init($args)
   {
      return $this->stateObj->init($args);
   }

    public function release($pay_type)
    {
       if( $this->check())
            return $this->stateObj->release($pay_type);
    }

    public function verify($state,$mess='')
    {
        return $this->stateObj->verify($state,$mess='');

    }

    public function bidRerelease($data)
    {
        $this->stateObj->bidRerelease($data);
    }

    public function bidCancle()
    {
        //if( $this->check())
             return $this->stateObj->bidCancle();

    }

    public function bidClose()
    {
        if( $this->check())
            $this->stateObj->bidClose();
    }



    public function replyUploadCerts($reply_user_id,$certs)
    {
        return $this->stateObj->replyUploadCerts($reply_user_id,$certs);
    }

    public function replySubmitCert()
    {
        return $this->stateObj->replySubmitCert();
    }

    public function replyCertsVerify($status)
    {
        return $this->stateObj->replyCertsVerify($status);
    }

    public function replyCertAdd($reply_id,$cert)
    {

    }

    public function replyCertDel($cert_id,$reply_id)
    {
        if($this->checkReply()){
            return $this->stateObj->replyCertDel($cert_id,$reply_id);
        }
        return false;
    }



    public function replyDocUpload($upload){

    }

    public function replyPaydocFee($pay_type){
        return $this->stateObj->replyPaydocFee($pay_type);
    }

    public function replySubmitPackage($data,$upload){
        if($this->checkReply()){
            return $this->stateObj->replySubmitPackage($data,$upload);
        }

    }

    public function uploadBid()
    {
       return  $this->stateObj->uploadBid();
    }

    public function bidStop()
    {
        if($this->check()){
            return $this->stateObj->bidStop();
        }
    }

    public function pingbiao($reply_pack_id,$point,$status){
        return $this->stateObj->pingbiao($reply_pack_id,$point,$status);
    }

    public function pbClose($status)
    {
        if($this->check()){
            return $this->stateObj->pbClose($status);
        }

    }

    public function addBidNotice($title,$content){
        if($this->check()){
            return $this->stateObj->addBidNotice($title,$content);
        }
    }

    public function bidComment($content, $user_id)
    {
        return $this->stateObj->bidComment($content, $user_id);
    }

    public function rebackReplyBail($bid_id){
        if($this->check()){
            return $this->stateObj->rebackReplyBail($bid_id);
        }
    }

}