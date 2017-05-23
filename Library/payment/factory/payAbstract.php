<?php
/**
 * 支付抽象工厂
 * User: Administrator
 * Date: 2017/4/11 0011
 * Time: 下午 3:11
 */
namespace Library\payment\factory;
abstract class payAbstract{

    //获取支付对象接口方法
    abstract  public function getPayObj();

    //获取退款对象接口方法
    abstract public function getRefundObj();

    //其他支付业务类型...


}