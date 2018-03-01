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
     * to freeze money
     * @param int $user_id   the userID to be frozen
     * @param float $num     the amount to be frozen
     * @param string $note   note
     * @param int $buyer_id  buyer id
     * @param int $seller_id seller id
     * @param string $orderNo order no
     * @param int $amount    the total amount of order
     * @return mixed
     */
    abstract protected function freeze($user_id, $num, $note = '',$buyer_id=0,$seller_id=0,$orderNo='',$amount=0);

    /**
     * release a certain amount of frozen money
     * @param int $user_id the user account to be release
     * @param float $num the amount to be release
     * @param string $note note
     * @param int $buyer_id
     * @param int $seller_id
     * @param string $orderNo
     * @param float $amount order amount
     * @return mixed
     */
    abstract protected function freezeRelease($user_id, $num, $note,$buyer_id=0,$seller_id=0,$orderNo='',$amount=0);

    /**
     * 使用用户的冻结资金支付给另一个用户
     * @param int $from 付款用户id
     * @param int $to  收款用户id
     * @param float $num 金额
     *
     */
    abstract protected function freezePay($from,$to,$num,$note='');

    /**
     * pay to platform
     * @param int $user_id the payer id
     * @param float $num money ,greater than 0
     */
    abstract protected function payMarket($user_id,$num,$note='');

    /**
     * platform refund to user
     * @param int $user_id the user that the platform refund to
     * @param float $num Refund amount
     * @return mixed
     */
    abstract protected function marketToUser($user_id,$num,$note='');
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