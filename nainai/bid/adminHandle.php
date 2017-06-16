<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/5 0005
 * Time: ÏÂÎç 4:02
 */

namespace nainai\bid;
use \nainai\bid\query\bidQuery;

class adminHandle extends handle
{
    public function check(){

    }

    public function getBidList($page=1){
        $query = new bidQuery();
        return $query->getBidList($page);
    }

    public function getBidDetail($id){
        $bidQuery = new bidQuery();

        return $bidQuery->getBidDetail($id);
    }
}