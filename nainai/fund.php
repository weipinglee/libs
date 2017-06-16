<?php
/**
 * �ʽ������
 * author:weipinglee
 * Date: 2016/4/22
 * Time: 10:11
 */

namespace nainai;
class fund{

    const FUND_AGENT  =  1; //�����˻�
    const FUND_ZX     = 2;  //�����˻�
    const FUND_ZX_EN  = 'zx';

    public static function getFundName($type){
        switch($type){

            case self::FUND_ZX :
            case self::FUND_ZX_EN :
                return '�����˻�';
                break;
            case self::FUND_AGENT :
            default : {
                return '�г������˻�';
            }
                break;
        }
    }

    public static function createFund($id){

        switch($id){

            case self::FUND_ZX :
            case self::FUND_ZX_EN :
                return new \nainai\fund\zx();
            break;
            case self::FUND_AGENT :
            default : {
                 return new \nainai\fund\agentAccount();
             }
            break;
        }
    }

    public function get_account($type){
        return call_user_func_array(array($this,'createFund'),array($type));
    }
}