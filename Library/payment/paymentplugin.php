<?php
namespace Library\payment;
use Library\url;
use Library\unionpayb2b\sdk;
use Library\Query;
use Library\json;
/**
 * @file paymentplugin.php
 * @brief 支付插件基类
 * @author
 * @date 2013/5/7 20:07:59
 * @version 1.0.0
 */

/**
 * @class PaymentPlugin
 * @brief 支付插件抽象类
 */
abstract class paymentPlugin {

	protected $paymentId = 0;//支付接口类型id
	public $method = "post";//表单提交模式
	public $name = null;//支付插件名称
	public $version = 1.0;//版本

	/**
	 * @brief 开始支付
	 */
	public function doPay($sendData) {
		if(!$sendData)
			return false;
		echo <<< OEF
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head></head>
			<body>
				<p>please wait...</p>
				<form action="{$this->getSubmitUrl()}" method="{$this->method}">
OEF;
		foreach ($sendData as $key => $item) {
			echo <<< OEF
					<input type='hidden' name='{$key}' value='{$item}' />
OEF;
		}
		echo <<< OEF
				</form>
			</body>
			<script type='text/javascript'>
				window.document.forms[0].submit();
			</script>
		</html>
OEF;
	}

	/**
	 * @brief 根据支付方式配置编号  获取该插件的详细配置信息
	 * @param $key        string 字段
	 * @return 返回支付插件类参数数据
	 */
	protected  function getPaymentById($key = '') {

		$paymentDB = new Query('payment');
		$paymentDB->where = 'id=:id';
		$payment_id = $this->paymentId;
		$paymentDB->bind = array('id' => $payment_id);
		$paymentRow = $paymentDB->getObj();

		if ($key) {
			return isset($paymentRow[$key]) ? $paymentRow[$key] : '';
		}

		return $paymentRow;
	}

	/**
	 * 获取支付参数（商户id，密码）
	 */
	protected  function getPaymentParam() {
		//最终返回值
		$payment = array();

		//获取公共信息
		$paymentRow = $this->getPaymentById('config_param');
		if ($paymentRow) {
			$paymentRow = json::decode($paymentRow);
			foreach ($paymentRow as $key => $item) {
				$payment[$key] = $item;
			}
		}
		return $payment;
	}


	/**
	 * @brief 获取订单中的支付信息 M:必要信息; R表示店铺; P表示用户;
	 * @return array 支付提交信息
	 */
	protected  function getPaymentInfo() {

		$payment = $this->getPaymentParam();


		//下面三个参数通过业务类传送
		//$payment['M_OrderNO'] = self::createOrderNum();
		//$payment['M_OrderId'] = $r_id;
		//$payment['M_Amount'] = $argument['account'];
		$payment['M_Remark'] = '';


		//交易信息
		$payment['M_Time'] = time();
		$payment['M_Paymentid'] = $this->paymentId;

		//店铺信息
		$payment['R_Address'] = '';
		$payment['R_Name'] = '';
		$payment['R_Mobile'] =  '';
		$payment['R_Telephone'] = '';

		return $payment;
	}




	/**
	 * 获取交易类型1：消费，2：退款
	 * @$paymentId int  支付类型：银联，担保交易等
	 * @$code str   交易类型码
	 */
	public static function getTradeType($paymentId, $code = '01') {
		if ($paymentId == 3) {
//银联支付
			switch ($code) {
				case '01':{
						return 1;
					}
				case '04':{
						return 2;
					}
			}
		}
		if ($paymentId == 7) {
			return 1;
		}

		return 1;
	}
	/**
	 * @brief 返回配置参数
	 */
	public function configParam() {
		return array(
			'M_PartnerId' => '商户ID号',
			'M_PartnerKey' => '商户KEY密钥',
		);
	}

	/**
	 * 异步通知停止
	 */
	abstract public function notifyStop();

	/**
	 * 获取提交地址
	 * @return string Url提交地址
	 */
	abstract public function getSubmitUrl();

	/**
	 * 获取要发送的数据数组结构
	 * @param $argument array 要传递的参数信息
	 * @return array
	 */
	abstract public function getSendData($argument);


	/**
	 * 同步支付回调
	 * @param $ExternalData array  支付接口回传的数据
	 * @param $money        float  交易金额
	 * @param $message      string 信息
	 * @param $orderNo      string 订单号
	 * @param $flowNo       string 第三方系统返回的流水号
	 */
	abstract public function callbackVerify($ExternalData, &$money, &$message, &$orderNo,&$flowNo);

	/**
	 * 获取支付id
	 * @return mixed
	 */
	abstract public function getPaymentId();
}