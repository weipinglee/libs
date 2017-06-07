<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/5 0005
 * Time: ÏÂÎç 4:02
 */

namespace nainai\bid;


class buyerHandle extends handle
{
    public function check(){
        $bidObj = new \Library\M($this->bidTable);
        $user_id = $bidObj->where(array('id'=>$this->bidID))->getField('user_id');
        if($user_id && $user_id==$this->operUserId)
            return true;
        return false;
    }
}