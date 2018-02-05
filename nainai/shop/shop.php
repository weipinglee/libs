<?php
/**
 * 资金操作类
 * author:weipinglee
 * Date: 2016/4/22
 * Time: 10:11
 */

namespace nainai\shop;
use \Library\M;
class shop{

      public static function info($user_id){
	       $shopObj = new M('shop_info');
		   return $shopObj->where(array('user_id'=>$user_id))->getObj();
	  
	  }
   
}