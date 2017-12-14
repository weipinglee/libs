<?php
/**
 * 竞价报盘管理
 * author: weipinglee
 * Date: 2017/12/12
 */

namespace nainai\offer;
use \Library\tool;
use \Library\M;
use \Library\time;
class jingjiaOffer extends product{

    protected $limitTimes = 1;//同一个报盘设置竞价交易的限制次数，1表示限制1次，0不限制

    protected $jingjiaMode = 1;//竞价模式代码

    protected $yikoujiaMode = 2;//一口价模式代码

    /**
     * 获取报盘的最大可售数量
     * @param $offer_id int 报盘id
     */
    protected function getActiveNums($offer_id)
    {

    }


    /**
     * 报盘插入数据
     * @param array $offer_id  原报盘id
     * @param array $offerData 更改的报盘数据
     */
    public function doOffer($offer_id,$offerData,$user_id)
    {
        $obj = new \Library\M('product_offer');
        $obj->beginTrans();
        $offer_id = intval($offer_id);
        $query = 'select * from product_offer where id='.$offer_id.' and status='.self::OFFER_OK.' AND user_id='.$user_id.' FOR UPDATE';
        $newOfferData = $obj->query($query);//从旧的报盘中查询出数据作为新的报盘数据
        $oldOfferData = array();
        if(isset($newOfferData[0])){
            $newOfferData = $newOfferData[0];

            //检验该报盘是否已经超过竞价报盘发布的次数
            $where = array('sub_mode'=>$offerData['submode'],'product_id'=>$newOfferData['product_id'],'status'=>self::OFFER_OK);
            $have = $obj->where($where)->getField('id');
            if($this->limitTimes==1 && !empty($have)){
                return tool::getSuccInfo(0,'该报盘有正在进行的竞价交易，请勿重复提交');
            }

            //获取商品剩余量
            $proObj = new \Library\M('products');
            $proResult = $proObj->where(array('id'=>$newOfferData['product_id']))->fields('quantity,freeze,sell')->getObj();
            $proLeft = $proResult['quantity'] - $proResult['freeze'] - $proResult['sell'];

            //对新报盘数据部分字段进行修改
            $newOfferData['old_offer'] = $newOfferData['id'];
            unset($newOfferData['id']);
            $newOfferData['pro_name'] = $offerData['proname'];
            $newOfferData['sub_mode'] = $offerData['submode'];
            $newOfferData['start_time'] = $offerData['start_time'];
            $newOfferData['end_time'] = \Library\time::getDiffSec($newOfferData['expire_time'],$offerData['end_time'])>0 ? $offerData['end_time'] : $newOfferData['expire_time'];
            $newOfferData['price'] = $offerData['price'];
            $newOfferData['price_l'] = $offerData['price_l'];
            $newOfferData['price_r'] = $offerData['price_r'];
            $newOfferData['divide'] = 0;
            $newOfferData['minimum'] = 0;
            $newOfferData['minstep'] = 0;
            //计算新报盘和旧报盘的最大购买数量，如果旧报盘剩余量为0，则max_num字段设为-1,表示不可购买
            if($newOfferData['max_num']>0){
                $max_num = min($newOfferData['max_num'],$proLeft);
            }
            else{
                $max_num = $proLeft;
            }

            if($offerData['max_num']>$max_num){
                return tool::getSuccInfo(0,'参与活动的商品量不能大于原报盘剩余量');
            }
            $newOfferData['max_num'] = $offerData['max_num'];
            $oldOfferData['max_num'] =  $max_num - $newOfferData['max_num'] > 0 ?   $max_num - $newOfferData['max_num'] : -1;

            //插入新的报盘和更改旧报盘
            $newOfferId = $obj->data($newOfferData)->add();
            $obj->data($oldOfferData)->where(array('id'=>$offer_id))->update();


        }
        else{
            return tool::getSuccInfo(0,'该报盘不存在');
        }
        //提交事务
        if($obj->commit()){
            $this->createEvent($newOfferId);
            return tool::getSuccInfo();
        }
        else{
            return tool::getSuccInfo(0,'操作失败');
        }



    }

    /**
     * 竞价交易报价
     * @param $offer_id int 报盘id
     * @param $price float 提报的价格
     * @param $user_id int 报价的用户id
     */
    public function baojia($offer_id,$price,$user_id)
    {
        $offerObj = new M('product_offer');
        //获取符合条件的报盘
        $res = $offerObj->where(array('id'=>$offer_id,'sub_mode'=>$this->jingjiaMode,'status'=>self::OFFER_OK))->getObj();
        if(empty($res)){
            return tool::getSuccInfo(0,'该报盘不存在或已成交');
        }
        if($user_id==$res['user_id'])
            return tool::getSuccInfo(0,'不能给自己的报盘报价');

        //判断是否处于交易时间内
        $now = time::getTime();
        if($now<time::getTime($res['start_time']) || $now>time::getTime($res['end_time'])){
            return tool::getSuccInfo(0,'该竞价未开始或已过期');
        }

        //判断价格是否合适
        $baojiaObj = new M('product_jingjia');
        $baojiaData = $baojiaObj->where(array('offer_id'=>$offer_id))->fields('max(price) as max')->getObj();
        //没有报价且价格低于设置的最低价时不能报价
        if(!isset($baojiaData['max']) && $price<$res['price_l']){
            return tool::getSuccInfo(0,'您的报价低于卖家设置的最低价，不能报价');
        }
        //出价低于其他用户的出价不能报价
        if(isset($baojiaData['max']) && $price<=$baojiaData['max']){
            return tool::getSuccInfo(0,'您的报价低于其他竞价者的出价，请重新出价');
        }

        $insertData = array(
            'user_id'=>$user_id,
            'offer_id'=>$offer_id,
            'price' => $price,
            'time' => time::getDateTime()
        );
        $offerObj->beginTrans();
        $insertRes = $baojiaObj->data($insertData)->add();
        if($insertRes){
            if($price>=$res['price_r']){//报价高于设置的最高价，调用用户定义的mysql程序，更改offer状态
                $sql = 'CALL jingjiaHandle('.$offer_id.','.$user_id.')';
                $offerObj->query($sql);
            }
            if($offerObj->commit()){
                return tool::getSuccInfo();
            }

        }
        $offerObj->rollBack();
        return tool::getSuccInfo(0,'报价失败');



    }

    protected function createEvent($offer_id,$end_time='')
    {
        $event_name = 'autoStopJingjia_'.$offer_id;
        $jingjiaOffer = new M('product_offer');
        if($end_time==''){
            $end_time = $jingjiaOffer->where(array('id'=>$offer_id))->getField('end_time');
        }

        $sql = 'CREATE  EVENT IF NOT EXISTS `'.$event_name.'`  ON SCHEDULE AT "'.$end_time.'" ON COMPLETION NOT PRESERVE ENABLE DO
        CALL jingjiaHandle('.$offer_id.',0);';
        $res = $jingjiaOffer->query($sql);
        if($res){
            return true;
        }
        return false;
    }




}