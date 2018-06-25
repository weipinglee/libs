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
class jingOfferTest extends base
{
    public function testDoOffer()
    {

        $user_id = 36;
        //正确的时间段
        $now = \Library\time::getDateTime();
        $dateBeginObj = new \DateTime($now);
        $dateInter2S = new \DateInterval('PT2S');
        $dateInter3S = new \DateInterval('PT3S');

        //减去3S得到错误开始时间
        $dateBeginObj->sub($dateInter3S);
        $start_error = $dateBeginObj->format('Y-m-d H:i:s');
        $dateBeginObj->add($dateInter3S);

        //加2s得到正确开始时间
        $dateBeginObj->add($dateInter2S);
        $start_time = $dateBeginObj->format('Y-m-d H:i:s');

        //减去2S得到错误结束时间
        $dateBeginObj->sub($dateInter2S);
        $end_error = $dateBeginObj->format('Y-m-d H:i:s');
        $dateBeginObj->add($dateInter2S);


        //加2S得到正确结束时间
        $dateBeginObj->add($dateInter2S);
        $end_time= $dateBeginObj->format('Y-m-d H:i:s');


        //减去2S得到第二阶段的错误开始时间
        $dateBeginObj->sub($dateInter2S);
        $start_error2 = $dateBeginObj->format('Y-m-d H:i:s');
        $dateBeginObj->add($dateInter2S);

        //加2S得到正确第二阶段开始时间
        $dateBeginObj->add($dateInter2S);
        $start_time2 = $dateBeginObj->format('Y-m-d H:i:s');

        //减去2S得到第二阶段错误结束时间
        $dateBeginObj->sub($dateInter2S);
        $dateBeginObj->format('Y-m-d H:i:s');
        $dateBeginObj->add($dateInter2S);

        //加2s得到正确第二阶段结束时间
        $dateBeginObj->add($dateInter2S);
        $end_time2 = $dateBeginObj->format('Y-m-d H:i:s');


        //输入数据
        $offerData = array(
                'mode' => 2,//保证金报盘
                'apply_time'  => $now,
                'divide'      => 0,
                'minimum'     =>  0,
                'minstep'     =>  0,
                'price'       => 0,

                'accept_area' => '山西阳泉',
                'accept_day' => '3',

                'weight_type' => '吃水',

                'other' => '其他补充说明',
                'shop_id' => 1,
                'set'   => array(
                    0=> array(
                        'invitees' =>  '36,2,45',
                        'start_time' => '',
                        'end_time'   => '',
                        'price_l'    => 5,
                        'price_step' => 2
                    ),
                    1=> array(
                        'invitees' =>  '',
                        'start_time' => '',
                        'end_time'   => '',
                        'price_l'    => 10,
                        'price_step' => 1
                    )
                )
        );

        //测试数据库要有分类数据
        $productData = array();
        $productData[0] = array(
            'name'         => '竞价商品1',
            'cate_id'      => 68,
            'quantity'     => 98,
            'attribute'    => serialize(array(
                122 => '123',
                177 => '567'
            )),
            'note'         => '这是商品1的备注',
            'produce_area' => '140311',
            'create_time'  => $now,
            'unit'         => '吨',
            'user_id' => $user_id,//要有用户基础数据库
            'shop_id' => 1,
            'market_id' => 24

        );

        $productData[1] = array(
            array('img'=>'upload/2018/6/19/1.jpg@user'),
            array('img'=>'upload/2018/6/19/2.jpg@user')
        );

        $productData[2] = 'weipinglee';

        //开始测试
        $offer = new jingjiaOffer($user_id);

        //第一阶段开始时间错误
        $offerData['set'][0]['start_time'] = $start_error;
        $res = $offer->doOffer($productData,$offerData);

        $this->assertArrayHasKey('success',$res,'未检测到开始时间小于当前时间的错误');
        $this->assertEquals(0,$res['success'],'开始时间小于当前时间 返回状态错误');
        $this->assertEquals('开始时间不能小于当前时间',$res['info'],'竞价开始时间不能小于当前时间的提示语错误');

        //第一阶段结束时间错误
        $offerData['set'][0]['start_time'] = $start_time;
        $offerData['set'][0]['end_time'] = $end_error;
        $res1 = $offer->doOffer($productData,$offerData);
        $this->assertArrayHasKey('success',$res1,'未检测到结束时间小于开始时间的错误');
        $this->assertEquals(0,$res1['success'],'结束时间大于开始时间的错误 返回状态错误');
        $this->assertEquals('第1阶段的结束时间必须大于开始时间',$res1['info'],'竞价开始时间不能小于当前时间的提示语错误');

        //第二阶段开始时间错误
        $offerData['set'][0]['start_time'] = $start_time;
        $offerData['set'][0]['end_time'] = $end_time;
        $offerData['set'][1]['start_time'] = $start_error2;
        $offerData['set'][1]['end_time'] = $end_time2;
        $res2 = $offer->doOffer($productData,$offerData);//print_r($res2);
        $this->assertArrayHasKey('success',$res2,'未检测到第二阶段开始时间小于第一阶段结束时间的错误');
        $this->assertEquals(0,$res2['success'],' 返回状态错误');
        $this->assertEquals('第2阶段的开始时间不能小于上一阶段的结束时间',$res2['info'],'竞价开始时间不能小于上一阶段结束时间的提示语错误');


        //正确的时间

        $offerData['set'][0]['start_time'] = $start_time;
        $offerData['set'][0]['end_time'] = $end_time;
        $offerData['set'][1]['start_time'] = $start_time2;
        $offerData['set'][1]['end_time'] = $end_time2;
        $res2 = $offer->doOffer($productData,$offerData);

        $this->assertEquals(1,$res2['success'],'竞价报盘未成功');

        $jingjiaSet = $offerData['set'];
        unset($offerData['set']);

        //数据库中期望的数据
        $expOfferData = $offerData;
        $expProductData = $productData[0];
        //print_r($res);
      //  print_r($expOfferData);
        $offerDataInDB = $this->seeInDatabase('product_offer',$expOfferData);
        $expProductData['id'] = $offerDataInDB['product_id'];
        $this->seeInDatabase('products',$expProductData);

        foreach($jingjiaSet as $item){
            $item['jingjia_id'] = $offerDataInDB['id'];
            $this->seeInDatabase('product_jingjia_set',$item);
        }


    }



    public static  function tearDownAfterClass(){
        self::clearTable('product_offer');
        self::clearTable('products');
        self::clearTable('product_jingjia_set');
        self::clearTable('product_photos');
    }

}
