<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file stateBase.php
 * @brief 招标初始化类
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\state;
use \Library\M;
use \Library\tool;
class bidOper extends \nainai\bid\bidBase
{

    protected $bidModel = null;

    //招标数据规则
    protected $bidRules = array(

    );

    //操作成功与否的信息，初始化为成功消息，操作中出错则设置为错误信息，客户端获取进行提交或回滚
    protected $succInfo = array();

    //包件规则
    protected $packageRules = array();
    public function __construct()
    {
        $this->bidModel = new M($this->bidTable);
        $this->succInfo = tool::getSuccInfo();
    }

    public function beginTrans(){
        $this->bidModel->beginTrans();
    }


    /**
     * 事务提交，如果在操作中有错误消息，则回滚事务，否则正常提交
     * @return array
     */
    public function commit(){
        $res = $this->succInfo;
        if(!($res['success']==1 && $this->bidModel->commit())){
            $this->bidModel->rollBack();
            if($res['success']==1)
                $res = array('success'=>0,'info'=>'操作失败');
        }
        return $res;
    }

    /**
     * 生成一条新的招标数据
     * @param array $bidData 招标数据
     * @return array
     */
    public function createNewBid($bidData=array())
    {
        $newData = array(
            'no' => $this->createBidNo(),
            'mode' => $bidData['mode'],//招标类型，gk:公开，yq:邀请
            'user_id' => $bidData['user_id'],//招标用户
            'doc' => $bidData['doc'],//标书地址
            'top_cate' => $bidData['top_cate'],//市场分类
            'pro_name' => $bidData['pro_name'],//项目名称
            'pro_address' => $bidData['pro_address'],//项目地址
            'begin_time' => $bidData['begin_time'],//开始时间
            'end_time' => $bidData['end_time'],//结束时间
            'open_time'=>$bidData['open_time'],//开标时间
            'bid_require' => $bidData['bid_require'],//招标条件
            'pro_brief' => $bidData['pro_brief'],//项目简介
            'bid_content' => $bidData['bid_content'],//招标内容
            'pack_type' => $bidData['pack_type'],//包件类型，1：分包、2：总包
            'eq' => $bidData['eq'],//投标企业资质，多条数据序列化
            'doc_begin' => $bidData['doc_begin'],//标书销售开始时间
            'doc_price' => $bidData['doc_price'],//标书价格
            'supply_bail' => $bidData['supply_bail'],//供方保证金
            'open_way' => $bidData['open_way'],//开标方式
            'pay_way' => $bidData['pay_way'],//多种支付方式已逗号相隔
            'other'   => $bidData['other'],//其他事项
            'bid_person' => $bidData['bid_person'],//招标人
            'cont_person' => $bidData['cont_person'],//联系人
            'cont_email' => $bidData['cont_email'],//联系邮箱
            'cont_address' => $bidData['cont_address'],//联系地址
            'cont_phone' => $bidData['cont_phone'],//联系电话
            'cont_tax' => $bidData['cont_tax'],//联系传真
            'agent'=> $bidData['agent'],//代理机构
            'agent_person' => $bidData['agent_person'],//代理联系人
            'agent_address' => $bidData['agent_address'],//代理地址
            'agent_email'  => $bidData['agent_email'],//代理邮箱
            'agent_phone' => $bidData['agent_phone'],//代理电话
            'agent_tax'   => $bidData['agent_tax'],//代理传真
            'create_time' => \Library\time::getDateTime(),//创建时间
            'bail'        => $this->getBidDeposit(),//获取保证金
            'status'      => 0


        );

        $newId = 0;
        if($this->bidModel->data($newData)->validate($this->bidRules)){
            if(!$newId = $this->bidModel->add()){
                $this->succInfo = tool::getSuccInfo(0,'操作失败');
            }
        }
        else{
            $this->succInfo = tool::getSuccInfo(0,$this->bidModel->getError());
        }
        return $newId;
    }

    /**
     * 给招标添加包件
     * @param $bid_id
     * @param $packageData
     */
    public function createNewPackage($bid_id,$packageData){
        $new_id = 0;
        if($bid_id && !empty($packageData)){
            $package = array();
            foreach($packageData as $key=>$val){
                $package[$key]['pack_id'] = $val['pack_id'];
                $package[$key]['product_name'] = $val['product_name'];
                $package[$key]['brand'] = $val['brand'];
                $package[$key]['spec'] = $val['spec'];
                $package[$key]['tech_need'] = $val['tech_need'];
                $package[$key]['unit'] = $val['unit'];
                $package[$key]['num'] = $val['num'];
                $package[$key]['tran_date'] = $val['tran_date'];
                $package[$key]['bid_id'] = $bid_id;
            }
            $packageObj = new M($this->bidPackageTable);
            if(!$new_id = $packageObj->adds($packageData)){
                $this->succInfo = tool::getSuccInfo(0,'操作失败');
            }
        }
        else{
            $this->succInfo = tool::getSuccInfo(0,'操作失败');
        }
        return $new_id;
    }

    /**
     * 删除一个包件
     * @param $pack_id int 包件的id，非包件号
     * @return mixed
     */
    public function delPackage($pack_id){
        $packageObj = new M($this->bidPackageTable);
        $where = array('id'=>$pack_id);
        return $packageObj->where($where)->delete();
    }

    /**
     * 冻结指定招标的保证金
     * @param $bid_id int 招标id
     * @param $pay_type  object 支付对象
     */
    public function payBidDeposit($bid_id,$pay_type)
    {
        $data = $this->bidModel->where(array('id'=>$bid_id))->fields('user_id,bail')->getObj();
        if(!empty($data) && $data['user_id'] && $data['bail']>0){
            $active = $pay_type->getActive();
            if($active<$data['bail'])
                $this->succInfo = tool::getSuccInfo(0,'账户可用余额不足');
            $res = $pay_type->freeze($data['user_id'],$data['bail'],'招投标支付保证金');
            if(true!==$res){//支付成功
                $this->succInfo =  tool::getSuccInfo(0,'支付保证金失败');
            }
        }
        else{
            $this->succInfo =  tool::getSuccInfo(0,'操作失败');
        }


    }

    /**
     * 招标发布审核
     * @param $bid_id int 招标id
     * @param $status int 审核状态，直接写入status字段
     * @param $message string 审核意见
     */
    public function verifyBid($bid_id,$status,$message='')
    {
        $where = array('id'=>$bid_id);
        $data = array();
        $data['status'] = $status;//传入的status参数直接写入数据库，status的值由客户端传入
        $data['admin_message'] = $message;

       if($this->bidModel->validate($this->bidRules,$data)) {
           if(!$this->bidModel->where($where)->data($data)->update()){
               $this->succInfo = tool::getSuccInfo(0,'审核失败，请重新操作');
           }
       }
       else{
            return tool::getSuccInfo(0,$this->bidModel->getError());
        }
    }

    /**
     * 设置状态
     * @param $bid_id
     * @param $status
     */
    public function setStatus($bid_id,$status)
    {
        $where = array('id'=>$bid_id);
        $data = array();
        $data['status'] = $status;
        if(!$this->bidModel->where($where)->data($data)->update()){
            $this->succInfo = tool::getSuccInfo(0,'设置失败');
        }

    }



    /**
     * 获取保证金数额
     */
    protected function getBidDeposit()
    {
        return 0;
    }

    /**
     * 生成招标号码
     * @return string
     */
    protected function createBidNo()
    {
        return  'zb'.date('YmdHis') . rand(100000, 999999);
    }

}