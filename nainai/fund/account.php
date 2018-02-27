<?php
/**
 * ÓÃ»§ÕË»§¹ÜÀíÀà
 * author: weipinglee
 * Date: 2016/4/20
 * Time: 16:18
 */
namespace nainai\fund;
use nainai\order\Order;
abstract class account{

    /**
     * 创建报文格式对象
     * @return mixed
     */
    abstract protected function createMessageProduct();

    /**
     * 创建通信对象，比如http
     * @return mixed
     */
    abstract protected function createCommunicateProduct();
    /**
     * 获取用户的可用资金
     * @param int $user_id
     */
    abstract protected function getActive($user_id);

    /**
     * 获取用户冻结资金
     * @param int $user_id ÓÃ»§id
     */
    abstract protected function getFreeze($user_id);
    /**
     * 入金
     * @param int $user_id 用户id
     * @param $num float 金额
     */
    abstract protected function in($user_id,$num);



    /**
     * 冻结用户一定数量的资金
     * @param int $user_id 用户id
     * @param float $num 冻结数额
     */
    abstract protected function freeze($user_id,$num,$note='');

    /**
     * 释放用户一定数量的冻结资金
     * @param int $user_id
     * @param float $num 数量
     */
    abstract protected function freezeRelease($user_id,$num,$note,$freezeno='');

    /**
     * 使用用户的冻结资金支付给另一个用户
     * @param int $from 付款用户id
     * @param int $to  收款用户id
     * @param float $num 金额
     *
     */
    abstract protected function freezePay($from,$to,$num,$note='',$amount='');

    /**
     * 支付给市场
     * @param int $user_id 用户id
     * @param float $num 金额
     */
    abstract protected function payMarket($user_id,$num);

    /**
     * 查询签约状态
     * @param int $user_id 用户id
     * @return mixed
     */
    abstract protected function signedStatus($user_id);

    /**
     * 传输签约信息
     * @param int $user_id
     * @return mixed
     */
    abstract protected function transSigninfo($user_id);


}