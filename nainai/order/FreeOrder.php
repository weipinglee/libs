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
		if($offer_info['user_id'] == $orderData['user_id']){
			return tool::getSuccInfo(0,'不能购买自己的商品');
		}
		if(isset($offer_info['price']) && $offer_info['price']>0){
			$product_valid = $this->productNumValid($orderData['num'],$offer_info);
			if($product_valid !== true)
				return tool::getSuccInfo(0,$product_valid);
			$orderData['amount'] = $offer_info['price'] * $orderData['num'];
			
			$upd_res = $this->orderUpdate($orderData);
			$pro_res = $this->productsFreeze($offer_info,$orderData['num']);
			
			if($pro_res != true) return tool::getSuccInfo(0,$pro_res);
			$res = isset($res) ? tool::getSuccInfo(0,$res) : $upd_res;
			
			$buyer = $orderData['user_id'];
			$seller = $offer_info['user_id'];
			$bankinfo = $this->userBankInfo($seller);

			$mess_buyer = new \nainai\message($buyer);
			$content = '合同'.$orderData['order_no'].'已形成,请您尽快完成线下支付,并上传支付凭证';
			$mess_buyer->send('common',$content);

			$mess_seller = new \nainai\message($seller);
			$content = $bankinfo ? '您有一笔合同形成,合同号：'.$orderData['order_no'].',正在等待买家支付货款' : '您有一笔合同形成,合同号：'.$orderData['order_no'].'。正在等待买家支付。请您及时进行开户申请。<a href="'.url::createUrl('/fund/bank@user').'">去开户</a>';
			$mess_seller->send('common',$content);
		}else{
			$res = tool::getSuccInfo(0,'无效报盘');
		}
		return $res;
	}

	public function cancleOrder($order_id,$user_id)
	{
		$info = $this->orderInfo($order_id);
		$offerInfo = $this->offerInfo($info['offer_id']);
		if(!empty($info)){
			$orderData['id'] = $order_id;
			$tmp = $this->sellerUserid($order_id);
			if($offerInfo['type'] == \nainai\offer\product::TYPE_SELL){
				$seller = $tmp;
				$buyer = intval($info['user_id']);
			}else{
				$seller = intval($info['user_id']);
				$buyer = $tmp;
			}
			if($user_id!=$seller){
				return tool::getSuccInfo(0,'订单不存在');
			}
			$mess_buyer = new \nainai\message($buyer);
			$mess_seller = new \nainai\message($seller);
			if($info['mode']!=self::ORDER_FREE)
				return tool::getSuccInfo(0,'订单不存在');
			if($info['contract_status'] != self::CONTRACT_BUYER_RETAINAGE)
				return tool::getSuccInfo(0,'合同状态有误');
			$orderData = array();
			$orderData['id'] = $order_id;
			$orderData['contract_status'] = self::CONTRACT_CANCEL;
			$upd_res = $this->orderUpdate($orderData);

			//将商品数量解冻
			$pro_res = $this->productsFreezeRelease($offerInfo,$info['num']);


			$res = $upd_res['success'] == 1  && $pro_res === true ? true : $upd_res['info'].$pro_res;

			if($res === true){
				$content = '合同'.$info['order_no'].'由于买方未按时上传支付凭证已取消。';
				$mess_buyer->send('common',$content);

				$mess_seller->send('common',$content);
				return tool::getSuccInfo();
			}
			return tool::getSuccInfo(0,'操作失败');
		}
		else{
			return tool::getSuccInfo(0,'订单不存在');
		}
	}
}




