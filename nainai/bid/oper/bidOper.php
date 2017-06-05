<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file stateBase.php
 * @brief 招标初始化类
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\state;
use \Library\M;

class bidOper extends \nainai\bid\bidBase
{

    protected $bidModel = null;

    protected $rules = array(

    );
    public function __construct()
    {
        $this->bidModel = new M($this->bidTable);

    }

    public function createNewBid($arg=array())
    {

    }

    /**
     * 发布招标,冻结用户保证金
     * @param $bid_id int 招标id
     */
    public function releaseBid($bid_id)
    {

    }

    /**
     * 招标发布审核
     * @param $bid_id int 招标id
     * @param $status int 审核状态，1：通过，0：拒绝
     */
    public function verifyBid($bid_id,$status){

    }



    /**
     * 获取保证金数额
     */
    protected function getBidDeposit()
    {
        return 0;
    }

    /**
     * 生成招标号码
     * @return string
     */
    protected function createBidNo()
    {
        return  'zb'.date('YmdHis') . rand(100000, 999999);
    }

}