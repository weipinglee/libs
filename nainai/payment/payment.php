<?php
namespace nainai\payment;

/**
 * @file payment.php
 * @brief 支付业务相关接口类
 * @author
 * @date 2011-01-20
 * @version 0.6
 * @note
 */

/**
 * @class Payment
 * @brief 支付方式 操作类
 */

abstract class Payment {

	public $payObj = null;

	public $paymentId = 0;

	protected $callbackUrl = '';

	protected $serverCallback = '';

	public function __construct($pay=null)
	{
		$this->payObj = $pay;
		$this->paymentId = $pay->getPaymentId();
	}


	//调用前的操作
	abstract public function payBefore(Array $argument=array());

	//调用后第三方同步或异步的业务操作
	abstract public function payAfter(Array $argument=array());


	public static function createOrderNum() {
		return  date('YmdHis') . rand(100000, 999999);
	}
	public static function getDateTime($format = '', $time = '') {
		$time = $time ? $time : time();
		$format = $format ? $format : 'Y-m-d H:i:s';
		return date($format, $time);
	}
}