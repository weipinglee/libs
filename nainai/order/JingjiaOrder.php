<?php
/**
 * @author liweiping
 * @date 2017-12-14
 * @brief 竞价报盘订单，与其他报盘订单是组合关系
 *
 */
namespace nainai\order;
use \Library\M;
use \Library\Query;
use \Library\tool;
use \Library\url;

class JingjiaOrder {

	/**
	 * 判断报盘是否可以交易，状态为7时是待交易状态，可以交易
	 * @param  int $offer_id 报盘id
	 * @return boolean  true:通过 false:未通过
	 */
	public function offerExist($offer_id){
		$offer = new M('product_offer');
		$res = $offer->where(array('id'=>$offer_id))->lock('update')->fields('status,is_del,expire_time')->getObj();

		return !empty($res) && $res['status'] == 7 && $res['is_del'] == 0 && time() < strtotime($res['expire_time'])? true : false;

	}


}




