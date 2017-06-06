<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file uninitState.php
 * @brief 招标未初始化类
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid\state;


class uninitState extends stateBase
{
    public function init($args)
    {
        $this->bidObj->beginTrans();
        $new_id = $this->bidObj->createNewBid($args);
        $this->bidObj->createNewPackage($new_id,$args['package']);
        return $this->bidObj->commit();
    }

     public function release()
     {

     }

     public function verify($status)
     {

     }

}