<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file handle.php
 * @brief ´¦ÀíÀà
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid;

use \Library\M;
abstract class handle extends \nainai\bid\state\stateBase
{
    public $stateObj = null;
    public $operUserId = 0;
    public $bidID = 0;
    public function __construct($bid_id=0,$user_id=0)
    {
        $this->setStateObj($bid_id);
        $this->operUserId = $user_id;
        $this->bidID = $bid_id;
        if($this->stateObj){
            $this->stateObj->setBidID($bid_id);
        }


    }

    public function setStateObj($bid_id){
        if(!$bid_id)
            $this->stateObj = new \nainai\bid\state\uninitState();
        else{
            $bidObj = new M($this->bidTable);
            $bid = $bidObj->where(array('id'=>$bid_id))->getObj();
            if(!empty($bid) && isset($bid['status'])){
                switch($bid['status']){
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
                }


            }
        }

    }

   public function init($args)
   {
      $this->stateObj->init($args);
   }

    public function release($pay_type)
    {
       if( $this->check())
            $this->stateObj->release($pay_type);
    }

    public function verify($state)
    {
        $this->stateObj->verify($state);
    }

}