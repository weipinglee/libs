<?php
/**
 * 竞价报盘管理
 * author: weipinglee
 * Date: 2017/12/12
 */

namespace nainai\offer;
use \Library\tool;
class jingjiaOffer extends product{

    protected $limitTimes = 1;//同一个报盘设置竞价交易的限制次数，1表示限制1次，0不限制

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
    public function doOffer($offer_id,$offerData)
    {
        $obj = new \Library\M('product_offer');
        $obj->beginTrans();
        $offer_id = intval($offer_id);
        $query = 'select * from product_offer where id='.$offer_id.' and status='.self::OFFER_OK.' FOR UPDATE';
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

            if($newOfferData['max_num']>$max_num){
                return tool::getSuccInfo(0,'参与活动的商品量不能大于原报盘剩余量');
            }
            $newOfferData['max_num'] = $offerData['max_num'];
            $oldOfferData['max_num'] =  $max_num - $newOfferData['max_num'] > 0 ?   $max_num - $newOfferData['max_num'] : -1;

            //插入新的报盘和更改旧报盘
            $obj->data($newOfferData)->add();
            $obj->data($oldOfferData)->where(array('id'=>$offer_id))->update();

        }
        else{
            return tool::getSuccInfo(0,'该报盘不存在');
        }
        //提交事务
        if($obj->commit()){
            return tool::getSuccInfo();
        }
        else{
            return tool::getSuccInfo(0,'操作失败');
        }



    }


}