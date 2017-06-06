<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file stateBase.php
 * @brief 状态基类
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid\state;


use nainai\bid\oper\openBid;
use nainai\bid\oper\privateBid;
use nainai\state\bidOper;
use Library\M;
use \Library\tool;
abstract class stateBase extends \nainai\bid\bidBase
{
    public $bidObj = null;
    public $bidID = 0;//操作的招标id
    public function __construct()
    {
        $this->bidObj = new bidOper();
    }


    public function setBidID($id){
        $this->bidID = $id;
    }


    abstract public function init($args);

    abstract public function release($pay_type);

    abstract public function verify($state);


}