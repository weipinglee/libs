<?php
/**
 * ����֧����������
 * User: Administrator
 * Date: 2017/4/11 0011
 * Time: ���� 3:18
 */
namespace Library\payment\factory;
use Library\payment\unionpayb2b\api\pay;
class unionb2bFactory extends payAbstract{

    public  function getPayObj(){
        return new pay();
    }

    public  function getRefundObj()
    {
        // TODO: Implement getRefundObj() method.
    }
}