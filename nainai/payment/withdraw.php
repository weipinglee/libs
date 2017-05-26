<?php
/**
 * 提现业务类
 * User: Administrator
 * Date: 2017/4/11 0011
 * Time: 下午 5:27
 */
namespace nainai\payment;
use Library\M;
use Library\searchQuery;
use Library\url;
use Library\tool;
class withdraw extends payment{


    protected $mainTable = 'user_withdraw';

    protected $rules = array(
        array('id','number','id错误',0,'regex'),
        array('user_id','number','',0,'regex'),
        array('bank_name','/^[a-z]{2,10}$/','格式错误'),
        array('amount','currency','金额格式错误',0,'regex'),
        array('request_no','/^[a-zA-Z_0-9]{5,50}$/','编码错误',0,'regex'),
        array('status','number','状态错误',0,'regex'),
        array('create_time','datetime','时间错误',0,'regex'),
        array('first_time','datetime','时间错误',0,'regex'),
        array('is_del','number','格式错误',0,'regex'),
    );

    CONST APPLY = 0;
    CONST FAIL  = 2;
    CONST SUCCESS = 1;

    public function getStatusText($status){
        switch($status){
            case self::APPLY :
                return '申请提现';
            break;
            case self::FAIL :
                return '提现被驳回';
            break;
            case self::SUCCESS :
                return '提现成功';
            break;
            default :
                return '未知';
        }

    }
    /**
     * 提现前操作（生成订单，提交到数据库等待后台审核）
     * @param array $argument
     * @return bool
     */
    public function payBefore(Array $argument=array())
    {
        $M = new M($this->mainTable);
        if($M->data($argument)->validate($this->rules)){
            $argument['bank_name'] = 'zx';
            $argument['create_time'] = \Library\time::getDateTime();
            $argument['request_no']  = 'zxtx'.self::createOrderNum();
            $argument['status'] = self::APPLY;
            if($this->payObj->getActive($argument['user_id']) < $argument['amount'])
                return tool::getSuccInfo(0,'可用余额不足');
            if($argument['amount']<=0)
                return tool::getSuccInfo(0,'提现金额不能为0');
            if($M->add()){
                return tool::getSuccInfo(1,'申请成功，等待后台处理');
            }
        }

            return tool::getSuccInfo(0,$M->getError());


    }

    /**
     * 提现操作,后台操作同意时调用
     * 调用第三方接口的出金操作，操作成功后修改申请状态
     * @param array $argument 字段id:出金申请的id
     * @return bool
     */
    public function payAfter(Array $argument=array())
    {
        $id = isset($argument['id']) ? $argument['id'] : 0;
        if($id){
            $M = new M($this->mainTable);
            $data = $M->where(array('id'=>$id))->getObj();
            if(!empty($data)){
                //判断可提现余额是否足够
                if($this->payObj->getActive($data['user_id']) < $data['amount'])
                    return tool::getSuccInfo(0,'可提现余额不足');
                //开始事务
                $M->beginTrans();
                //出金操作
                $res = $this->payObj->out(array('user_id'=>$data['user_id'],'num'=>$data['amount']));
                if($res['success']==1){//如果出金成功，更新字段状态
                    $M->data(array('status'=>self::SUCCESS))->where(array('id'=>$id))->update();
                }
                else{
                    return tool::getSuccInfo(0,$res['info']);
                }
                if($M->commit()){
                    return tool::getSuccInfo();
                }
            }
        }
        return tool::getSuccInfo(0,'操作错误');




    }

    /**
     * 出金拒绝操作
     * @param array $argument 字段id出金申请id
     */
    public function handleReject(Array $argument=array()){
        $id = isset($argument['id']) ? $argument['id'] : 0;
        if($id) {
            $M = new M($this->mainTable);
            if($M->where(array('id'=>$id))->data(array('status'=>self::FAIL))->update()){
                return tool::getSuccInfo();
            }
        }
        return tool::getSuccInfo(0,'操作失败');
    }

    /**
     * @brief 获取提现列表
     * @param string $type string all :所有 init:申请 fail:驳回 success:成功
     * @return array
     */
    public function getList($type='all')
    {
        $Q = new searchQuery($this->mainTable .' as uw');
        $Q->join = 'left join user as u on u.id = uw.user_id';
        $where = 'uw.bank_name = "zx"';
        if($type=='init'){
            $where .= ' and uw.status in ('.self::APPLY.')';
        }
        elseif($type=='fail'){
            $where .= ' and uw.status in ('.self::FAIL.')';
        }
        elseif($type=='success'){
            $where .= ' and uw.status in ('.self::SUCCESS.')';
        }
        $Q->where = $where;
       $Q->fields = 'uw.*,u.username,u.type,u.mobile,u.user_no';
        $list = $Q->find();
        foreach($list['list'] as $key=>$item){
            $list['list'][$key]['status_text'] = $this->getStatusText($item['status']);
        }
        return $list;
    }

    public function getDetails($id){
        $M = new M($this->mainTable);
        $data = $M->where(array('id'=>$id))->getObj();
        if(!empty($data)){
            $data['status_text'] = $this->getStatusText($data['status']);
        }
        return $data;
    }


}