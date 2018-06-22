<?php
/**
 * Created by PhpStorm.
 * User: weipinglee
 * Date: 2018/6/22
 * Time: 15:11
 */

namespace tests;


require 'start.php';

use \nainai\offer\jingjiaOffer;
class offerTest extends base
{
    public function testAA()
    {
        $a = 2;

        $offer = new jingjiaOffer();
        $res = $offer->doOffer(array(),array());
        //print_r($res);
       // $this->assertArrayHasKey(1,$res);

        $this->assertEquals($a, 2);
    }


}
