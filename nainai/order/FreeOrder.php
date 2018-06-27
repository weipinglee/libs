<?php
/**
 * @author panduo
 * @date 2016-4-25
 * @brief 自由摘牌订单表 暂只支持余额支付
 *
 */
namespace nainai\order;
use \Library\M;
use \Library\Query;
use \Library\tool;
use \Library\url;
class FreeOrder extends Order{
	
	public function __construct(){
		parent::__construct(parent::ORDER_FREE);
	}

	//生成摘牌订单
	public function geneOrder($orderData){
		unset($orderData['payment']);
		if($orderData['mode'] == self::ORDER_FREE){
			$orderData['contract_status'] = self::CONTRACT_BUYER_RETAINAGE;
		}else{
			return tool::getSuccInfo(0,'生成订单错误');
		}
		
		$offer_exist = $this->offerExist($orderData['offer_id']);
		if($offer_exist === false) return tool::getSuccInfo(0,'报盘不存在或未通过审核');

		$offer_info = $this->offerInfo($orderData['offer_id']);
        $this->vipPrice($offer_info);
        $orderData['price_unit'] = $offer_info['price'];
		if($offer_info['user_id'] == $orderData['user_id']){
			return tool::getSuccInfo(0,'不能购买自己的商品');
		}
		if(isset($offer_info['price']) && $offer_info['price']>0){
			$product_valid = $this->productNumValid($orderData['num'],$offer_info);
			if($product_valid !== true)
				return tool::getSuccInfo(0,$product_valid);
			$orderData['amount'] = $offer_info['price'] * $orderData['num'];
			$orderData['offer_user_id'] = $offer_info['user_id'];
			$upd_res = $this->orderUpdate($orderData);
			$pro_res = $this->productsFreeze($offer_info,$orderData['num']);
			
			if($pro_res != true) return tool::getSuccInfo(0,$pro_res);
			$res = isset($res) ? tool::getSuccInfo(0,$res) : $upd_res;
			
			$buyer = $orderData['user_id'];
			$seller = $offer_info['user_id'];
			$bankinfo = $this->userBankInfo($seller);

			$mess_buyer = new \nainai\message($buyer);
			$content = '合同'.$orderData['order_no'].'已形成,请您尽快完成支付';
			$mess_buyer->send('common',$content);

			$mess_seller = new \nainai\message($seller);
			$content = $bankinfo ? '您有一笔合同形成,合同号：'.$orderData['order_no'].',正在等待买家支付货款' : '您有一笔合同形成,合同号：'.$orderData['order_no'].'。正在等待买家支付。请您及时进行开户申请。<a href="'.url::createUrl('/fund/bank@user').'">去开户</a>';
			$mess_seller->send('common',$content);

			//拿到订单id,创建事件
            $order_id = isset($upd_res['order_id']) ? $upd_res['order_id'] : 0;
            if($order_id){
                $this->createEvent($order_id);
            }
		}else{
			$res = tool::getSuccInfo(0,'无效报盘');
		}
		return $res;
	}


	/**
	 * 买家支付尾款
	 * @param  int  $order_id 订单id
	 * @param  int  $user_id  当前操作用户id
	 * @param  string  $payment  线上/线下支付
	 * @param  string  $proof    线下支付凭证图片
	 * @param  int $account  线上支付方式
	 * @return array  操作信息
	 */
	public function buyerRetainage($order_id,$user_id,$payment='online',$proof = '',$account=0){
		if($this->orderComplain($order_id)) return tool::getSuccInfo(0,'申述处理中');
		$info = $this->orderInfo(intval($order_id));
		$offerInfo = $this->offerInfo($info['offer_id']);
		if(is_array($info) && isset($info['contract_status'])){
			$seller = $this->sellerUserid($order_id);
			$buyer = $offerInfo['type'] == \nainai\offer\product::TYPE_SELL ? intval($info['user_id']) : $seller;
			$seller = $offerInfo['type'] == \nainai\offer\product::TYPE_SELL ? $seller : intval($info['user_id']);

			if($info['contract_status'] == self::CONTRACT_BUYER_RETAINAGE || $info['contract_status'] == self::CONTRACT_NOTFORM){
				if($buyer != $user_id)
					return tool::getSuccInfo(0,'订单买家信息有误');

				$amount = floatval($info['amount']);
				$buyerDeposit = floatval($info['pay_deposit']);
				$retainage = $amount - $buyerDeposit;
				$sim_oper = in_array($info['mode'],array());
				if($retainage>0){
					try {
						$this->order->beginTrans();
						$orderData['id'] = $order_id;
						$orderData['retainage_payment'] = $account;
						$payment = $proof!='' ? 'offline' : $payment;
						//自由与委托报盘只接受线下凭证
						$mess = new \nainai\message($seller);

						// var_dump($retainage);exit;
						if($payment == 'online'){
							//冻结买家帐户余额
							$orderData['pay_retainage'] = $retainage;
							$orderData['contract_status'] = self::CONTRACT_FREE_VERIFY ;//等待卖家确认收款
							// $orderData['retainage_clientid'] = $account == self::PAYMENT_BANK ? $clientID : '';
							$upd_res = $this->orderUpdate($orderData);
							if($upd_res['success'] == 1){
								$log_res = $this->payLog($order_id,$user_id,0,'买家线上支付全款');

								$mess->send('buyerRetainage',$info['order_no']);
								$mess_buyer = new \nainai\message($buyer);

								$content = '(合同'.$info['order_no'].'买家已支付全款，请您关注资金动态。交收流程请您在线下进行操作。)';

								$mess_buyer->send('common',$content);
								$res = $log_res;
							}else{
								$res = $upd_res['info'];
							}

							if($res === true ){
								$note = '冻结合同'.$info['order_no'].'款项￥'.number_format($retainage,2);
								$account = $this->base_account->get_account($account);
								if(!is_object($account)) return tool::getSuccInfo(0,$account);
								$acc_res = $account->freeze($buyer,$retainage,$note,$buyer,$seller,$info['order_no'],$info['amount']);

							}

							if($res === true && $acc_res===true ) $res = $this->order->commit();
						}elseif($payment == 'offline'){
							$orderData['proof'] = $proof;
							$upd_res = $this->orderUpdate($orderData);
							if($upd_res['success'] == 1){
								$jump_url = "<a href='".url::createUrl('/contract/sellerDetail?id='.$order_id.'@user')."'>跳转到合同详情页</a>";
								$content = '(合同'.$info['order_no'].',买方已上传支付凭证,请您及时进行凭证确认,并关注资金动态。)'.$jump_url;
								$mess->send('common',$content);
								$log_res = $this->payLog($order_id,$user_id,0,'买家上传线下支付凭证');
								$res = $log_res === true ? $this->order->commit() : $log_res;
							}else{
								$res = $upd_res['info'];
							}
						}else{
							$this->order->rollBack();
							$res = '无效支付方式';
						}
					} catch (\PDOException $e) {
						$res = $e->getMessage();
						$this->order->rollBack();
					}
				}else{
					$res = '合同金额有误';
				}
			}else{
				$res = '合同状态有误';
			}
		}else{
			$res = '无效订单';
		}

		return $res === true ? tool::getSuccInfo() : tool::getSuccInfo(0,$res ? $res : '未知错误');
	}

	/**
	 * 确认线上支付成功
	 * @param $order_id
	 * @param $user_id
	 * @return array
	 */
	public function confirmPay($order_id,$user_id){
		if($this->orderComplain($order_id)) return tool::getSuccInfo(0,'申述处理中');
		$info = $this->orderInfo($order_id);
		$offerInfo = $this->offerInfo($info['offer_id']);
        if(empty($info))
			return tool::getSuccInfo(0,'订单不存在');
		$this->order->beginTrans();
		if($info['contract_status'] != self::CONTRACT_FREE_VERIFY){
			return tool::getSuccInfo(0,'合同状态有误');
		}

		$seller_tmp = $this->sellerUserid($order_id);
		$seller = $offerInfo['type'] == \nainai\offer\product::TYPE_SELL ? $seller_tmp : intval($info['user_id']);
		$buyer  = $offerInfo['type'] == \nainai\offer\product::TYPE_SELL ? intval($info['user_id']) : $seller_tmp;
		if($seller != $user_id)
			return tool::getSuccInfo(0,'订单卖家信息有误');

		$account = $this->base_account->createFund($info['retainage_payment']);
		$res = $account->freezePay($buyer,$seller,$info['amount'],'合同'.$info['order_no'].'的款项￥'.$info['amount'],$info['order_no'],1,strtotime($info['create_time']));
        if($res===true){
			$data = array('contract_status'=>self::CONTRACT_COMPLETE,'id'=>$info['id']);
            $data['end_time'] =  date('Y-m-d H:i:s',time());

            $res1 = $this->orderUpdate($data);
			if(isset($res1['success']) && $res1['success']==1 && $this->order->commit()){
				return tool::getSuccInfo();
			}
			$this->order->rollBack();
            $res = '操作失败';
		}

		return tool::getSuccInfo(0,$res);
	}

}




