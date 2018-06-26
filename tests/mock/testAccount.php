<?php
/**
 * 代理账户管理类
 * author: weipinglee
 * Date: 2016/4/20
 * Time: 17:25
 */

namespace tests\mock;

class testAccount {




    public function createMessageProduct()
    {
        // TODO: Implement createMessageProduct() method.
    }

    public function createCommunicateProduct()
    {
        // TODO: Implement createCommunicateProduct() method.
    }

    /**
      * 生成流水数据
      * @param int $user_id 用户id
      * @param float $num 更改金额
      * @param string $type 类型
      * @param string $note 备注
      *
      */
     private function createFlowData($user_id,$num,$type,$note=''){



    }

    public function __construct(){

    }

    /**
     * 获取可用余额
     * @param int $user_id
     */
    public function getActive($user_id){

        return 0;
    }

    /**
     * 获取冻结资金金额
     * @param int $user_id 用户id
     */
    public function getFreeze($user_id){

        return 0;
    }

     /**
      * 获取资金流水表
      * @param int $user_id
      * @param int $where 查询条件 ‘begin'开始时间，’end'结束时间，'no'序列号
      */
     public function getFundFlow($user_id=0,$cond=array()){


   }
    /**
  * 入金操作
  * @param int $user_id 用户id
  * @param $num float 入金金额
     * @param string $note 备注
  */
    public function in($user_id,$num,$note=''){
        return true;


   }

     /**
      * 出金操作
      * @param int $user_id 用户id
      * @param $num float 入金金额
      * @param string $note 备注
      */
     public function out($user_id,$num,$note=''){
          return true;



   }


    /**
     * 资金冻结
     * @param int $user_id 用户id
     * @param float $num 冻结金额
     */
    public function freeze($user_id, $num, $note = '',$buyer_id=0,$seller_id=0,$orderNo='',$amount=0){
         return true ;

    }


    /**
     * 冻结资金释放
     * @param int $user_id
     * @param float $num 释放金额
     * @param string $note 备注
     * @param string $freezeno 冻结编号
     */
    public function freezeRelease($user_id, $num, $note,$buyer_id=0,$seller_id=0,$orderNo='',$amount=0){

            return true;

    }

    /**
     * 冻结资金支付
     * 将冻结资金解冻，支付给另外一个用户
     * @param int $from 冻结账户用户id
     * @param int $to  转到的账户用户id,0代表市场
     * @param float $num 转账的金额
     * @param string $note 备注
     *
     */
    public function freezePay($from,$to=0,$num,$note=''){

                return true;

    }

    /**
     * 可用余额直接付款给市场
     * @param int $user_id 支付用户id
     * @param float $num 转账的金额
     */
    public function payMarket($user_id,$num,$note=''){
          return true;

    }

    /**
     * 转账
     * @param  Int $user_id 转账用户
     * @param  Int $to_user 被转账用户
     * @param  array  $data    转账数据
     * @return Boolean          
     */
    public function transfer($user_id, $to_user, $data=array()){
              return true;

    }

    public function signedStatus($user_id)
    {
        return true;
    }

    public function transSigninfo($user_id)
    {
        // TODO: Implement transSigninfo() method.
    }

    public function marketToUser($user_id, $num,$note='')
    {
        // TODO: Implement marketToUser() method.
    }


}