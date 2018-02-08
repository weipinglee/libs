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
class yikoujiaOffer extends product{

    protected $limitTimes = 1;//同一个报盘设置竞价交易的限制次数，1表示限制1次，0不限制

    protected $jingjiaMode = 1;//竞价模式代码

    protected $yikoujiaMode = 2;//一口价模式代码




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
                return tool::getSuccInfo(0,'该报盘有正在进行的一口价交易，请勿重复提交');
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
            $newOfferData['divide'] = 0;
            $newOfferData['minimum'] = 0;
            $newOfferData['minstep'] = 0;
            $newOfferData['status'] = 0;

            //计算新报盘和旧报盘的最大购买数量
            $max_num = $newOfferData['max_num']-$newOfferData['sell_num'];
            

            if($offerData['max_num']>$max_num){
                return tool::getSuccInfo(0,'参与活动的商品量不能大于原报盘剩余量');
            }
			
			 if($offerData['max_num'] == $max_num){//如果剩余量等于竞价量，原报盘状态改为成交
				$oldOfferData['status'] = 6
			}
            if(time::getTime()>time::getTime($newOfferData['start_time'])){
                return tool::getSuccInfo(0,'开始时间不能小于当前时间');
            }
            if(time::getTime($newOfferData['end_time'])<=time::getTime($newOfferData['start_time'])){
                return tool::getSuccInfo(0,'结束时间必须大于开始时间');
            }

            $newOfferData['sell_num'] = 0;
            $oldOfferData['max_num'] =  $newOfferData['max_num'] - $offerData['max_num'] ;
            $newOfferData['max_num'] = $offerData['max_num'];

            //插入新的报盘和更改旧报盘
            $newOfferId = $obj->data($newOfferData)->add();
            $obj->data($oldOfferData)->where(array('id'=>$offer_id))->update();


        }
        else{
            return tool::getSuccInfo(0,'该报盘不存在');
        }
        //提交事务
        if($obj->commit()){
            $this->createEvent($newOfferId);//创建事件的语句要写在commit之后，因为在mysql中，create语句会暗含事务的提交。
            return tool::getSuccInfo();
        }
        else{
            return tool::getSuccInfo(0,'操作失败');
        }



    }


    /**
     * 创建到期自动执行的事件
     * @param $offer_id
     * @param string $end_time
     * @return bool
     */
    protected function createEvent($offer_id,$end_time='')
    {
        $event_name = 'autoStopYikou_'.$offer_id;
        $jingjiaOffer = new M('product_offer');
        if($end_time==''){
            $end_time = $jingjiaOffer->where(array('id'=>$offer_id))->getField('end_time');
        }

        $sql = 'CREATE  EVENT IF NOT EXISTS `'.$event_name.'`  ON SCHEDULE AT "'.$end_time.'" ON COMPLETION NOT PRESERVE ENABLE DO
        CALL yikoujiaHandle('.$offer_id.');';
        $res = $jingjiaOffer->query($sql);
        if($res){
            return true;
        }
        return false;
    }



    public function beforeTrade($offer_id,$user_id=0){
        $jingjiaOffer = new M('product_offer');
        $data = $jingjiaOffer->where(array('id'=>$offer_id))->lock('update')->fields('start_time,end_time,status')->getObj();
        if(empty($data))
            return tool::getSuccInfo(0,'该报盘不存在');

        //判断是否处于交易时间内
        $now = time::getTime();
        if($now<time::getTime($data['start_time']) ){
            return tool::getSuccInfo(0,'该抢购报盘还未开始');
        }

        if($now>time::getTime($data['end_time'])){
            return tool::getSuccInfo(0,'该抢购报盘已结束');
        }

        if($data['status']==self::OFFER_COMPLETE){
            return tool::getSuccInfo(0,'该抢购报盘已售罄');
        }
        return tool::getSuccInfo();
    }

    public function afterTrade($offer_id){

    }


}