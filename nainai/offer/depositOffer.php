<?php
/**
 * 保证金报盘管理
 * author: weipinglee
 * Date: 2016/5/7
 * Time: 23:16
 */
namespace nainai\offer;
use \Library\tool;
class depositOffer extends product{

    /**
     * 获取用户保证金比率
     */
    public function getDepositRate($user_id){
        $obj = new \nainai\member();
        $res=$obj->getUserGroup($user_id);
        return $res['caution_fee'];
    }

    /**
     * 提交报盘
     * @param array $productData  产品数据
     * @param array $offerData 报盘数据
     * @param int $offer_id 报盘id
     */
    public function doOffer($productData,$offerData,$offer_id=0){
        $offerData['mode'] = self::DEPOSIT_OFFER;
        $this->_productObj->beginTrans();
        if($offer_id){
            $this->delOffer($offer_id,$this->user_id);
        }

        $offerData['user_id'] = $this->user_id;
        $insert = $this->insertOffer($productData,$offerData);

        if(is_int($insert) && $insert>0){
            if($this->_productObj->commit()){
                return tool::getSuccInfo();
            }
            else return tool::getSuccInfo(0,$this->errorCode['server']['info']);
        }
        else{
            $this->_productObj->rollBack();
            $this->errorCode['dataWrong']['info'] = $insert;
            return tool::getSuccInfo(0,$this->errorCode['dataWrong']['info']);
        }

    }
}