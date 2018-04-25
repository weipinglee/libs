<?php
/**
 * @author panduo
 * @date 2016-06-21 13:31:28
 * @brief 采购订单
 *
 */
namespace nainai\order;
use \Library\M;
use \Library\Query;
use \Library\tool;
use \Library\url;

class PurchaseOrder extends Order{
	
	public function __construct(){
		parent::__construct(parent::ORDER_DEPOSIT);
		$this->purchase = new M('purchase_report');
	}

	/**
	 * 生成采购订单
	 * @param  int $purchase_id 采购表purchase_report id
	 * @param  int $type 0:定金 1:全款
	 * @return array  结果数组
	 */
	public function purchaseOrder($purchase_id,$type = 0,$payment){
		$purchase = new M('purchase_report');
		$query = new Query('purchase_report as pr');
		$query->join = 'left join product_offer as po on pr.offer_id = po.id left join products as p on po.product_id = p.id left join order_sell as o on po.id = o.offer_id';
		$query->fields = 'pr.*,p.id as product_id,p.quantity,po.user_id as buyer_id,o.id as order_id';
		$query->where = 'pr.id = :purchase_id';
		$query->bind = array('purchase_id'=>$purchase_id);
		$purchase_info = $query->getObj();

		if(empty($purchase_info['order_id'])){
			
			$certObj = new \nainai\cert\certificate();
			$certStatus = $certObj->getCertStatus($purchase_info['seller_id'],'deal');
			if($certStatus['status']==4){
				$mess = new \nainai\message($purchase_info['seller_id']);
				$mess->send('repcredentials');
				return tool::getSuccInfo(0,'该报价的发布商家资质不够，暂时不能选择');
			}
			
			$product_info = $this->products->where(array('id'=>$purchase_info['product_id']))->getObj();
			
			try {
				$this->offer->beginTrans();
				$this->offer->data(array('price'=>$purchase_info['price']))->where(array('id'=>$purchase_info['offer_id']))->update();
				
				$orderData['offer_id'] = $purchase_info['offer_id'];
				$orderData['num'] = $purchase_info['quantity'];
				$orderData['order_no'] = tool::create_uuid();
				$orderData['user_id'] = $purchase_info['seller_id'];
				$orderData['create_time'] = date('Y-m-d H:i:s',time());
				$orderData['mode'] = self::ORDER_PURCHASE;
				$orderData['invoice'] = 1;
				$orderData['offer_user_id'] = $purchase_info['buyer_id'];
				// $orderData['buyer_id'] = $purchase_info['buyer_id'];
				$orderData['payment'] = $payment;
				$gen_res = $this->geneOrder($orderData);
				if($gen_res['success'] == 1){
					//将其他的报价状态置为被拒绝 选中报价置为已采纳
					$purchase->where(array('id'=>$purchase_id))->data(array('status'=>\nainai\offer\PurchaseReport::STATUS_ADOPT))->update();

					$purchase->where(array('offer_id'=>$purchase_info['offer_id'],'id'=>array('neq',$purchase_id)))->data(array('status'=>\nainai\offer\PurchaseReport::STATUS_REPLUSE))->update();

					$res = $this->payLog($gen_res['order_id'],$purchase_info['buyer_id'],0,'买方下单');

					//支付定金
					$deposit = new \nainai\order\DepositOrder();
					$dep_res = $deposit->buyerDeposit($gen_res['order_id'],$type,$purchase_info['buyer_id']);

					if($dep_res['success'] != 1){
						$error = $dep_res['info'];
					}else{
						$this->offer->commit();
					}

				}else{
					$error = $gen_res['info'];
				}
			} catch (\PDOException $e) {
				$this->offer->rollBack();
				$error = $e->getMessage() ? $e->getMessage() : '未知错误';
			}
		}else{
			$error = '此报价已生成订单';
		}

		
		
		return isset($error) ? tool::getSuccInfo(0,$error) : tool::getSuccInfo();

	}

    //生成摘牌订单
    public function geneOrder($orderData){
        $check_payment = 0;
        if(in_array($orderData['mode'],array(self::ORDER_FREE,self::ORDER_ENTRUST))){
            $orderData['contract_status'] = self::CONTRACT_BUYER_RETAINAGE;
        }else{
            $check_payment = 1;
            $orderData['contract_status'] = self::CONTRACT_NOTFORM;
        }
        if($orderData['mode'] != self::ORDER_PURCHASE){
            $offer_exist = $this->offerExist($orderData['offer_id']);
            if($offer_exist === false) return tool::getSuccInfo(0,'报盘不存在或未通过审核');
        }

        $offer_info = $this->offerInfo($orderData['offer_id']);

        $orderData['price_unit'] = $offer_info['price'];
        if($offer_info['user_id'] == $orderData['user_id']){
            return tool::getSuccInfo(0,'买方卖方为同一人');
        }

        if(isset($offer_info['price']) && $offer_info['price']>0){
            $product_valid = $this->productNumValid($orderData['num'],$offer_info);
            if($product_valid !== true)
                return tool::getSuccInfo(0,$product_valid);
            $orderData['amount'] = $offer_info['price'] * $orderData['num'];
            $orderData['offer_user_id'] = $offer_info['user_id'];
            //判断用户买家余额是否足够
            if($check_payment){
                //获取摘牌所需定金数额
                $pay_deposit = $this->payDepositCom($orderData['offer_id'],$orderData['amount']);
                $user_id = isset($orderData['buyer_id']) ? $orderData['buyer_id'] : $orderData['user_id'];//采购买家与正常相反
                switch ($orderData['payment']) {
                    case self::PAYMENT_AGENT:
                        //代理账户
                        $balance = $this->account->getActive($user_id);
                        break;
                    case self::PAYMENT_BANK:
                        //银行签约账户
                        $balance = $this->zx->attachBalance($user_id);

                        $balance = $balance['KYAMT'];
                        break;
                    case self::PAYMENT_TICKET:
                        //票据账户
                        break;
                    default:
                        return tool::getSuccInfo(0,'参数错误');
                        break;
                }
                if(floatval($balance) < $pay_deposit){
                    return tool::getSuccInfo(0,'账户余额不足');
                }
            }

            unset($orderData['payment']);

            $upd_res = $this->orderUpdate($orderData);

            $pro_res = $this->productsFreeze($offer_info,$orderData['num']);
            if($pro_res != true) return tool::getSuccInfo(0,$pro_res);

            $res = isset($res) ? tool::getSuccInfo(0,$res) : $upd_res;
        }else{
            $res = tool::getSuccInfo(0,'无效报盘');
        }

        return $res;
    }



}




