<?php
/**
 * 交易商认证管理类
 * author: weipinglee
 * Date: 2016/4/27 0027
 * Time: 下午 3:35
 */

namespace nainai\cert;
use \Library\M;
use \Library\Time;
use \Library\Query;
use \Library\Thumb;
use \Library\log;
use \Library\JSON;
class certVipTemp extends certificate{


    protected static $certType = 'vip_temp';
    //认证需要的字段,0个人用户，1企业用户
    protected static $certFields = array(
            'name',
            'address',
            'contact_person',
            'contact_phone',
    );

    /**
     * 验证规则：
     * array(字段，规则，错误信息，条件，附加规则，时间）
     * 条件：0：存在字段则验证 1：必须验证 2：不为空时验证
     *
     */
    private $rules = array(
        array('user_id','number','用户id错误'),//默认是正则
    );



    /**
     *获取认证资料
     * @param $user_id
     */
    public function getCertData($user_id=0){
        return $this->getCertDetail($user_id,self::$certType);


    }



    /**
     * 申请会员（只简单的插入一条数据，后续有其他要求再改）
     * @param array $certData 认证数据 （认证表的数据）
     */
    public function certApply($certData=array()){
        $memObj = new M(self::$certTable[self::$certType]);
        $certData['status'] = self::CERT_APPLY;
        $certData['type'] = 1;
        return $memObj->insertUpdate($certData,$certData);

    }

    public function certUpdate($certData=array()){
        $memObj = new M(self::$certTable[self::$certType]);
        $certData['status'] = self::CERT_UPDATE;
        $certData['type'] = 2;
        return $memObj->insertUpdate($certData,$certData);
    }

    //获取待认证列表
    public function certList($condition){
        return $this->getList($condition,self::CERT_APPLY);
    }

    //获取交易商已认证列表
    public function certedList($condition){
        return $this->getList($condition,self::CERT_INIT.','.self::CERT_SUCCESS.','.self::CERT_FAIL );
    }

    public function getList(){

    }

    /**
     * 获取认证详细信息
     */
    public function getDetail($id){
        return $this->getCertDetail($id,self::$certType);
    }

    /**
     * 审核
     * @param int $user_id 用户id
     * @param int $result 审核结果 1：通过，0：驳回
     * @param string $info 意见
     */
    public function verify($user_id,$result=1,$info=''){
        return $this->certVerify($user_id,$result,$info,self::$certType);
    }


}