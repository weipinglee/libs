<?php
/**
 * 资金操作类
 * author:weipinglee
 * Date: 2016/4/22
 * Time: 10:11
 */

namespace nainai;
class fund{

    const FUND_AGENT  =  1; //代理账户
    const FUND_ZX     = 2;  //中信账户
    const FUND_ZX_EN  = 'zx';
    const FUND_JS     = 3;
    const FUND_JS_EN  = 'js';

    public static function getFundName($type){
        switch($type){
            case self::FUND_JS:
            case self::FUND_JS_EN:
                return '建设账户';
            case self::FUND_ZX :
            case self::FUND_ZX_EN :
                return '中信账户';
                break;
            case self::FUND_AGENT :
            default : {
                return '市场代理账户';
            }
                break;
        }
    }

    public static function createFund($id){

        switch($id){
            case self::FUND_JS:
            case self::FUND_JS_EN:
                return new \nainai\fund\js();
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