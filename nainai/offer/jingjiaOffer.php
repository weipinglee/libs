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
        $query = 'select * from product_offer where id='.$offer_id.' and status='.self::OFFER_OK.' AND user_id='.$user_id.' and mode=4  FOR UPDATE';
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
           $newOfferData['price_l'] = $offerData['price_l'];
            $newOfferData['price_r'] = $offerData['price_r'];
            $newOfferData['divide'] = 0;
            $newOfferData['minimum'] = 0;
            $newOfferData['sell_num'] = 0;
            $newOfferData['minstep'] = 0;
            $newOfferData['jing_stepprice'] = $offerData['jing_stepprice'];
            //计算新报盘和旧报盘的最大购买数量
            if($newOfferData['max_num']>0){
                $max_num = min($newOfferData['max_num']-$newOfferData['sell_num'],$proLeft);
            }
            else{
                $max_num = $proLeft;
            }

            if($offerData['max_num']>$max_num){
                return tool::getSuccInfo(0,'参与活动的商品量不能大于原报盘剩余量');
            }
            if(time::getTime()>time::getTime($newOfferData['start_time'])){
                return tool::getSuccInfo(0,'开始时间不能小于当前时间');
            }
            if(time::getTime($newOfferData['end_time'])<=time::getTime($newOfferData['start_time'])){
                return tool::getSuccInfo(0,'结束时间必须大于开始时间');
            }
            $newOfferData['max_num'] = $offerData['max_num'];
            $oldOfferData['max_num'] =  $max_num - $newOfferData['max_num'] ;

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
     * 竞价交易报价
     * @param $offer_id int 报盘id
     * @param $price float 提报的价格
     * @param $user_id int 报价的用户id
     * @param $pay_way int 支付方式，默认代理账户
     */
    public function baojia($offer_id,$price,$user_id,$pay_way=1)
    {
        $offerObj = new M('product_offer');

        //获取符合条件的报盘
        $res = $offerObj->where(array('id'=>$offer_id,'sub_mode'=>$this->jingjiaMode))->getObj();
        if(empty($res)){
            return tool::getSuccInfo(0,'该报盘不存在');
        }

        if($res['status']!=1){
            return tool::getSuccInfo(0,'该报盘已成交');
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
        //获取报价的基础价
        $minPrice = isset($baojiaData['max']) ? $baojiaData['max'] : $res['price_l'];
        if(!isset($baojiaData['max']) && $price<$res['price_l']){
            return tool::getSuccInfo(0,'您的报价低于卖方设置的最低价，请重新出价');
        }
        if(isset($baojiaData['max']) && $price <=$baojiaData['max']){
            return tool::getSuccInfo(0,'您的报价不能低于当前报价的最高价，请重新出价');
        }
        if($res['jing_stepprice']>0 && ($price-$minPrice)%$res['jing_stepprice']!=0){
            return tool::getSuccInfo(0,'报价必须按照'.$res['jing_stepprice'].'的倍数递增');
        }

        $offerObj->beginTrans();
        //对相应的竞价报盘行锁定，同一竞价的多个会话的报价必须串行执行
        $offerObj->where(array('id'=>$offer_id))->lock('update')->getObj();


        $fund = new \nainai\fund();

        //冻结该用户新的金额
        $fundObj = $fund->createFund($pay_way);
        $amount = bcmul($price,$res['max_num'],2);
        $payRes = $fundObj->freeze($user_id,$amount,'参加竞价交易报价冻结金额');
        if($payRes!==true){
            $offerObj->rollBack();
            return tool::getSuccInfo(0,'账户内资金不足，请充值后再报价');
        }
        //上一个报价冻结的金额进行释放，并将该记录更新为已释放
        $oldBaojia = $baojiaObj->where(array('offer_id'=>$offer_id,'is_freeze'=>0))->fields('*')->lock('update')->order('id desc')->getObj();
        if(isset($oldBaojia['price']) && $oldBaojia['price']>0){
            $oldfundObj = $fund->createFund($oldBaojia['pay_way']);
            $oldfundObj->freezeRelease($oldBaojia['user_id'],$oldBaojia['amount'],'释放参加竞价交易报价的金额');
            $baojiaObj->where(array('id'=>$oldBaojia['id']))->data(array('is_freeze'=>1))->update();
        }
      //插入报价数据
        $insertData = array(
            'user_id'=>$user_id,
            'offer_id'=>$offer_id,
            'price' => $price,
            'time' => time::getDateTime(),
            'is_freeze'=>0,
            'pay_way' => $pay_way,
            'amount'=>$amount
        );
        $insertRes = $baojiaObj->data($insertData)->add();
        if($insertRes){
            if($res['price_r']>0 && $price>=$res['price_r']){//报价高于设置的最高价，调用用户定义的mysql程序，更改offer状态
                $sql = 'CALL jingjiaHandle('.$offer_id.','.$user_id.','.$price.')';
                $offerObj->query($sql);
                $message = new \nainai\message($user_id);
                $message->jingjiaWin($res['pro_name']);

            }
            if($offerObj->commit()){
                return tool::getSuccInfo();
            }

        }
        $offerObj->rollBack();
        return tool::getSuccInfo(0,'报价失败');



    }

    /**
     * 创建到期自动执行的事件
     * @param $offer_id
     * @param string $end_time
     * @return bool
     */
    protected function createEvent($offer_id,$end_time='')
    {
        $event_name = 'autoStopJingjia_'.$offer_id;
        $jingjiaOffer = new M('product_offer');
        if($end_time==''){
            $end_time = $jingjiaOffer->where(array('id'=>$offer_id))->getField('end_time');
        }

        $sql = 'CREATE  EVENT IF NOT EXISTS `'.$event_name.'`  ON SCHEDULE AT "'.$end_time.'" ON COMPLETION NOT PRESERVE ENABLE DO
        CALL jingjiaHandle('.$offer_id.',0,0);';
        $res = $jingjiaOffer->query($sql);
        if($res){
            return true;
        }
        return false;
    }


    /**
     * 交易前判断是否满足交易的条件
     * @param $offer_id
     * @param $user_id
     * @return array
     */
    public function beforeTrade($offer_id,$user_id){
        $jingjiaOffer = new M('product_offer');
        $data = $jingjiaOffer->where(array('id'=>$offer_id))->fields('status,sub_mode')->getObj();
        if(empty($data))
            return tool::getSuccInfo(0,'该报盘不存在');
        if($data['status']!=self::OFFER_WAITINGTRADE || $data['sub_mode']!=1){
            return tool::getSuccInfo(0,'该状态不允许交易');
        }
        $baojiaObj = new M('product_jingjia');
        $baojiaData = $baojiaObj->where(array('offer_id'=>$offer_id,'win'=>1))->order('price desc')->getObj();
        if(!isset($baojiaData['user_id'])||$baojiaData['user_id']!=$user_id){
            return tool::getSuccInfo(0,'您不是胜出用户，不能交易');
        }
        return tool::getSuccInfo();

    }

    public function afterTrade($offer_id){

    }


}