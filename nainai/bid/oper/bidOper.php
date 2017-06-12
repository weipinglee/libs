<?php
/**
 * @copyright (c) 2017 nainaiwang.com
 * @file stateBase.php
 * @brief 招标初始化类
 * @author weipinglee
 * @date 2017-6-5
 * @version 1.0
 */

namespace nainai\bid\oper;
use \Library\M;
use \Library\tool;
use \Library\time;
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

    protected $replyRules = array();

    protected $replyPackageRules = array();
    public function __construct()
    {
        $this->bidModel = new M($this->bidTable);
        $this->succInfo = tool::getSuccInfo();
    }

    public function getSuccInfo(){
        return $this->succInfo;
    }

    public function beginTrans(){
        $this->bidModel->beginTrans();
    }


    /**
     * 事务提交，如果在操作中有错误消息，则回滚事务，否则正常提交
     * @return array
     */
    public function commit($id=0,$url=''){
        $res = $this->succInfo;
        if(!($res['success']==1 && $this->bidModel->commit())){
            $this->bidModel->rollBack();
            if($res['success']==1)
                $res = array('success'=>0,'info'=>'操作失败');
        }
        $res['id'] = $id;
        $res['url'] = $url;
        return $res;
    }

    /**
     * 根据传入的招标数据筛选出招标数据表存在的字段，并返回
     * @param $bidData array 招标数据
     * @return array
     */
    private function handleBidData($bidData){
        $fields = array(
            'id',
            'no' ,
            'mode' ,//招标类型，gk:公开，yq:邀请
            'user_id',//招标用户
            'doc' ,//标书地址
            'top_cate' ,//市场分类
            'pro_name' ,//项目名称
            'pro_address' ,//项目地址
            'begin_time' ,//开始时间
            'end_time' ,//结束时间
            'open_time',//开标时间
            'bid_require',//招标条件
            'pro_brief',//项目简介
            'bid_content' ,//招标内容
            'pack_type' ,//包件类型，1：分包、2：总包
            'eq' ,//投标企业资质，多条数据序列化
            'doc_begin',//标书销售开始时间
            'doc_price' ,//标书价格
            'supply_bail' ,//供方保证金
            'open_way' ,//开标方式
            'pay_way' ,//多种支付方式已逗号相隔
            'other'  ,//其他事项
            'bid_person',//招标人
            'cont_person',//联系人
            'cont_email' ,//联系邮箱
            'cont_address' ,//联系地址
            'cont_phone' ,//联系电话
            'cont_tax' ,//联系传真
            'agent',//代理机构
            'agent_person' ,//代理联系人
            'agent_address' ,//代理地址
            'agent_email'  ,//代理邮箱
            'agent_phone' ,//代理电话
            'agent_tax'  ,//代理传真
        );
        $newData = array();
        foreach($bidData as $key=>$val){
            if(in_array($key,$fields)){
                $newData[$key] = $val;
            }
        }
        return $newData;


    }

    /**
     * 上传招标文件
     * @return array
     */
    public function uploadBidDoc(){
        //获取上传招标文件
        $name = 'doc';
        $upload = new \Library\upload\commonUpload();
        $uploadDir = 'upload/bid/'.date('Y/m');
        $upload->setDir($uploadDir);
        $upload->setallowType(array('doc','docx'));
        $data = $upload->upload();
        return isset($data[$name]) ? $data[$name] : array();

    }

    /**
     * 生成一条新的招标数据
     * @param array $bidData 招标数据
     * @return array
     */
    public function createNewBid($bidData)
    {
        $newId = 0;
        if(!empty($bidData)){
            $newData = $this->handleBidData($bidData);
            $newData['no'] = $this->createBidNo();
            $newData['status'] = 0;
            $newData['bail'] = $this->getBidDeposit();
            $newData['create_time'] = time::getDateTime();

            if($this->bidModel->data($newData)->validate($this->bidRules)){
                if(!$newId = $this->bidModel->add()){
                    $this->succInfo = tool::getSuccInfo(0,'操作失败');
                }
            }
            else{
                $this->succInfo = tool::getSuccInfo(0,$this->bidModel->getError());
            }

        }
        return $newId;

    }

    /**
     * 招标更新
     * @param $bidData array 招标数据
     */
    public function updateBid($bidData)
    {
        if(!empty($bidData)){
            $updateData = $this->handleBidData($bidData);
            $updateData['status'] = 0;
            if($this->bidModel->data($updateData)->validate($this->bidRules)){
                if(!$this->bidModel->where(array('id'=>$bidData['id']))->update()){
                    $this->succInfo = tool::getSuccInfo(0,'更新失败');
                    return false;
                }
                if(isset($bidData['package'])){
                    $packObj = new M($this->bidPackageTable);
                    if(!$packObj->insertUpdates($bidData['package'],$bidData['package'])){
                        $this->succInfo = tool::getSuccInfo(0,'更新失败');
                        return false;
                    }
                }
                return true;
            }
            else{
                $this->succInfo = tool::getSuccInfo(0,$this->bidModel->getError());
                return false;
            }
        }
        $this->succInfo = tool::getSuccInfo(0,'操作失败');
    }

    /**
     * 给招标添加多个包件
     * @param $bid_id
     * @param $packageData
     */
    public function createNewPackage($bid_id,$packageData){
        $new_id = 0;
        if($bid_id && !empty($packageData)){
            $package = array();
            foreach($packageData as $key=>$val){
                $package[$key]['pack_no'] = $val['pack_no'];
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
     * 增加一个包件
     * @param $bid_id int 招标id
     */
    public function addPackage($bid_id,$packageData){
        if($bid_id && !empty($packageData)){
            $package = array(
                'pack_no' => $packageData['pack_no'],//包件号
                'product_name' => $packageData['product_name'],
                'brand' => $packageData['brand'],
                'spec' => $packageData['spec'],
                'tech_need' => $packageData['tech_need'],
                'unit' => $packageData['unit'],
                'num'=> $packageData['num'],
                'tran_date'=>$packageData['tran_date'],
                'bid_id'=> $bid_id
            );
            $packObj = new M($this->bidPackageTable);
            if($packObj->validate($this->packageRules,$package)){
                if(!$packObj->add()){
                    $this->succInfo = tool::getSuccInfo(0,'操作失败');
                }
            }
            else{
                $this->succInfo = tool::getSuccInfo(0,$packObj->getError());
            }
        }
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
           return true;
       }
       else{
           $this->succInfo = tool::getSuccInfo(0,$this->bidModel->getError());
        }
        return false;
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
        $statusNow = $this->bidModel->where($where)->getField('status');
        if($statusNow == $status){
            return true;
        }
        if(!$this->bidModel->where($where)->data($data)->update()){
            $this->succInfo = tool::getSuccInfo(0,'设置失败');
            return false;
        }
        return true;

    }

    /**
     * 设置投标状态
     * @param $reply_id
     * @param $status
     * @return bool
     */
    public function setReplyStatus($reply_id,$status){
        $where = array('id'=>$reply_id);
        $data = array();
        $data['status'] = $status;
        $replyObj = new M($this->bidReplyTable);
        if(!$replyObj->where($where)->data($data)->update()){
            $this->succInfo = tool::getSuccInfo(0,'设置失败');
            return false;
        }
        return true;
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
        return  'ZB'.date('YmdHis') . rand(100000, 999999);
    }

    public function cancleBid($bid_id)
    {
        if(is_int($bid_id) && $bid_id>0){
            $this->succInfo = tool::getSuccInfo(0,'操作错误');
            return false;
        }

        //获取招标数据
        $bidData = $this->bidModel->where(array('id'=>$bid_id))->getObj();

        if(!empty($bidData)){
            $bid_user_id = $bidData['user_id'];//招标用户id
            $bid_no = $bidData['no'];
            $fund = new \nainai\fund();

            $this->bidModel->rollBack();//每一个退款都在一个独立的事务里

            //退还招标方保证金
            if($bidData['bail']>0 && $bidData['bail_is_refund']==0){
                $this->bidModel->beginTrans();
                $fundObj = $fund->createFund($bidData['bail_pay_way']);
                $note  = '取消招标'.$bid_no.'退回保证金';
                $bailRes = $fundObj->freezeRelease($bidData['user_id'],$bidData['bail'],$note);
                if($bailRes===true){
                    $res1 = false;
                    while($res1!==true){
                        if($this->bidModel->where(array('id'=>$bid_id))->data(array('bail_is_refund'=>1))->update()){
                            $res1 = $this->bidModel->commit();
                        }
                    }
                }
                else{
                    $this->succInfo = tool::getSuccInfo(0,'退还保证金失败');
                    $this->bidModel->rollBack();
                    return false;
                }

            }


            $replyObj = new M($this->bidReplyTable);
            $replyData = $replyObj->where(array('bid_id'=>$bid_id))->select();

            //释放投标方保证金
            foreach($replyData as $key=>$item){
                if($item['bail_fee']>0 && $item['bail_fee_refund']==0){//保证金大于0 且没有释放
                    $replyObj->beginTrans();
                    $fundObj = $fund->createFund($item['bail_pay_way']);
                    $note = '招标编号为'.$bid_no.'的招标撤销释放投标方保证金';
                    $resRelease = $fundObj->freezeRelease($item['reply_user_id'],$item['bail_fee'],$note);
                    if($resRelease===true){
                        $res2 = false;
                        while($res2!==true){
                            if($replyObj->where(array('id'=>$item['id']))->data(array('bail_fee_refund'=>1))->update()){
                                $res2 = $this->bidModel->commit();
                            }
                        }
                    }
                    else{
                        $this->succInfo = tool::getSuccInfo(0,'释放卖方保证金失败');
                        $this->bidModel->rollBack();
                        return false;
                    }

                }
            }


            //退还投标标书购买费用
            foreach($replyData as $item){
                if($item['doc_fee']>0 && $item['doc_fee_refund']==0){//已支付标书费且未退还的情况下，退还标书费用

                    $replyObj->beginTrans();
                    $fundObj = $fund->createFund($item['doc_pay_way']);
                    $transfer = array(
                        'amount'=>$item['doc_fee'],
                        'note'=>'招标编号为'.$bid_no.'的招标撤销退回投标方标书费用'
                    );
                    if($fundObj->getActive($bid_user_id)<$item['doc_fee']){//如果招标方对应账户可用余额不足退还
                        $this->succInfo = tool::getSuccInfo(0,$fund::getFundName($item['doc_pay_way']).'余额不足，部分用户标书费用未退还，请充值后再继续退款');
                        return false;
                    }
                    $res = $fundObj->transfer($bid_user_id,$replyData['reply_user_id'],$transfer);
                    if($res===true){//退款成功，修改投保标书退款标示字段为‘已退款’
                        $ref = false;
                        while(!$ref){//退款后务必将是否退款的状态改为1
                            $replyObj->where(array('id'=>$item['id']))->data(array('doc_fee_refund'=>1))->update();
                            $ref = $replyObj->commit();
                        }

                    }else{
                        $this->succInfo = tool::getSuccInfo(0,'部分用户退款失败');
                        $replyObj->rollBack();
                        return false;
                    }
                }

            }

            return true;
        }
        return false;

    }

    /**
     * 招标取消通知投标方，在投标方保证金和标书费用都退还情况下通知
     * @param $bid_id int 招标id
     * @return bool
     */
    public function bidCancleNotify($bid_id)
    {
        $replyObj = new M($this->bidReplyTable);
        $replyData = $replyObj->where(array('bid_id'=>$bid_id))->select();
        $bid_no = $this->bidModel->where(array('id'=>$bid_id))->getField('no');
        if($bid_no){
            $message = new \nainai\message();
            foreach($replyData as $item){
                $message->setUserId($item['reply_user_id']);
                $message->send('bidCancle',$bid_no);
            }
            return true;
        }
        return false;

    }


 /******************投标功能开始*****************************/


    /**
     * 为指定的投保添加资质信息
     * @param $reply_id int 投标id
     * @param $certs array 证书数据
     * @return bool
     */
    public function addReplyCerts($reply_id,$certs)
    {
        if($reply_id && !empty($certs)){
            $replyObj = new M($this->bidReplyTable);
            if(empty($replyObj->where(array('id'=>$reply_id))->getObj())){
                $this->succInfo = tool::getSuccInfo(0,'投标不存在');
                return false;
            }
            $certObj = new M($this->bidReplyCertTable);
            $certData = array();
            if(isset($certs[0])){//多维数组
                foreach($certs as $key=>$cert){
                    $certData[$key]['cert_name'] = $cert['cert_name'];
                    $certData[$key]['cert_type'] = $cert['cert_type'];
                    $certData[$key]['cert_des'] = $cert['cert_des'];
                    $certData[$key]['cert_pic'] = $cert['cert_pic'];
                    $certData[$key]['create_time'] = time::getDateTime();
                    $certData[$key]['reply_id'] = $reply_id;
                }
                if(!$certObj->data($certData)->adds()){
                    $this->succInfo = tool::getSuccInfo(0,'添加失败');
                    return false;
                }
            }
            else{
                $certData['cert_name'] = $certs['cert_name'];
                $certData['cert_type'] = $certs['cert_type'];
                $certData['cert_des'] = $certs['cert_des'];
                $certData['cert_pic'] = $certs['cert_pic'];
                $certData['create_time'] = time::getDateTime();
                $certData['reply_id'] = $reply_id;
                if(!$certObj->data($certData)->add()){
                    $this->succInfo = tool::getSuccInfo(0,'添加失败');
                    return false;
                }
            }
            return true;
        }

        else{
            $this->succInfo = tool::getSuccInfo(0,'添加失败');
            return false;
        }

    }


    public function delReplyCerts($cert_id)
    {
        $certObj = new M($this->bidReplyCertTable);
        if(!$certObj->where(array('id'=>$cert_id))->delete()){
            $this->succInfo = tool::getSuccInfo(0,'删除失败');
            return false;
        }
        return true;
    }



    /**
     * 创建新的投标
     * @param $bid_id int 招标id
     * @param $user_id int 投标用户id
     * @return bool 失败返回false 成功返回新增的id
     */
    public function createNewBidreply($bid_id,$user_id)
    {
        //检查招标是否存在
        $bidData = $this->bidModel->where(array('id'=>$bid_id))->getObj();
        if(empty($bidData)){
            $this->succInfo = tool::getSuccInfo(0,'该招标不存在');
            return false;
        }
        //检查该用户是否可投标
        if(!$this->isInvite($user_id,$bidData['yq_user'])){
            $this->succInfo = tool::getSuccInfo(0,'非邀请用户，不能投标');
            return false;
        }
        //检查该用户是否已投过标
        $replyObj = new M($this->bidReplyTable);
        $replyData = $replyObj->where(array('bid_id'=>$bid_id,'reply_user_id'=>$user_id))->getObj();
        if(!empty($replyData)){
            //$this->succInfo = tool::getSuccInfo(0,'您已对该招标投过标，不能重复投标');
            return $replyData['id'];
        }

        $replyData = array(
            'reply_user_id'=>$user_id,
            'bid_user_id' => $bidData['user_id'],
            'bid_id' => $bid_id,
            'status' => 0,
            'create_time' => time::getDateTime()
        );
        if($replyObj->data($replyData)->validate($this->replyRules)){
            if(!$id = $replyObj->add()){
                $this->succInfo = tool::getSuccInfo(0,'投标失败');
            }
            return $id;
        }
        else{
            $this->succInfo = tool::getSuccInfo(0,$replyObj->getError());
            return false;
        }


    }

    /**
     * 更新上传投标书
     * @param $reply_id  int 投标id
     * @param $doc string 投标书
     * @return bool
     */
    public function addReplyDoc($reply_id,$doc)
    {
        $replyObj = new M($this->bidReplyTable);
        $replyData = $replyObj->where(array('id'=>$reply_id))->getObj();
        if(empty($replyData)){
            $this->succInfo = tool::getSuccInfo(0,'投标不存在');
            return false;
        }
        if($replyObj->where(array('id'=>$reply_id))->data(array('bid_doc'=>$doc))->update()){
            return true;
        }
        $this->succInfo = tool::getSuccInfo(0,'添加失败');
        return false;

    }

    /**
     * 支付标书费用
     * @param $reply_id Int 投保id
     * @param $pay_type object 支付对象
     * @return bool
     */
    public function payBidDoc($reply_id,$pay_type)
    {
        $replyObj = new M($this->bidReplyTable);
        $replyData = $replyObj->where(array('id'=>$reply_id))->getObj();
        if(empty($replyData)){
            $this->succInfo = tool::getSuccInfo(0,'投标不存在');
            return false;
        }
        $bidData = $this->bidModel->where(array('id'=>$replyData['bid_id']))->getObj();
        if(isset($bidData['doc_price']) && $bidData['doc_price']>0){
            $payData = array('amount'=>$bidData['doc_price'],'note'=>'支付标书费用');
            $fund = new \nainai\fund();
            $payType = $fund->createFund($pay_type);
            $payRes = $payType->transfer($replyData['reply_user_id'],$replyData['bid_user_id'],$payData);
            if(true!==$payRes){
                $this->succInfo = tool::getSuccInfo(0,'支付失败');
                return false;
            }
            $res = false;
            while(!$res){
                $res = $replyObj->data(array('doc_fee'=>$bidData['doc_price'],'doc_pay_way'=>$pay_type))->update();
            }

            return true;

        }
        if($bidData['doc_price']<=0)
            return true;
        $this->succInfo = tool::getSuccInfo(0,'招标不存在');
        return false;
    }


    /**
     * 供应方支付保证金
     * @param $reply_id int 投标id
     * @param $pay_type object 支付对象
     * @return bool
     */
    public function payBidReplyDeposit($reply_id,$pay_type)
    {
        $replyObj = new M($this->bidReplyTable);
        $replyData = $replyObj->where(array('id'=>$reply_id))->getObj();
        if(empty($replyData)){
            $this->succInfo = tool::getSuccInfo(0,'投标不存在');
            return false;
        }
        $bidData = $this->bidModel->where(array('id'=>$replyData['bid_id']))->getObj();
        $replyUser = $replyData['reply_user_id'];
        if(isset($bidData['supply_bail']) && $bidData['supply_bail']>0){
            $deposit = $bidData['supply_bail'];
            $res = $pay_type->freeze($replyUser,$deposit);
            if($res===true){
                return true;
            }
            $this->succInfo = tool::getSuccInfo(0,$res);

        }
        elseif(isset($bidData['supply_bail']) && $bidData['supply_bail']<=0){//保证金为0，表示不需缴纳保证金，返回true
            return true;
        }
        else{
            $this->succInfo = tool::getSuccInfo(0,'支付失败');
        }
        return false;

    }


    /**
     * 包件报价单提交
     * @param $reply_id int 投标id
     * @param $packageData array 包件报价单数据
     * @return bool
     */
    public function replyPackage($reply_id,$packageData)
    {
        $replyObj = new M($this->bidReplyTable);
        $replyData = $replyObj->where(array('id'=>$reply_id))->getObj();
        if(empty($replyData)){
            $this->succInfo = tool::getSuccInfo(0,'投标不存在');
            return false;
        }
        $bid_id = $replyData['bid_id'];//招标id
        //包件类型，1：分包，2：总包
        $pack_type = $this->bidModel->where(array('id'=>$bid_id))->getField('pack_type');
        $packObj = new M($this->bidPackageTable);
        $pack_ids = $packObj->where(array('bid_id'=>$bid_id))->getFields('id');//该招标的所有包件id
        $pack_ids_rev = array_reverse($pack_ids);
        $replyPackData = array();

        foreach($packageData as $key=>$item){
            if(isset($item['pack_id']) && in_array($item['pack_id'],$pack_ids)){
                $replyPackData[$key]['pack_id'] = $item['pack_id'];//包件id
                $replyPackData[$key]['reply_id'] = $reply_id;//投标id
                $replyPackData[$key]['brand'] = $item['brand'];//品牌
                $replyPackData[$key]['spec'] = $item['spec'];
                $replyPackData[$key]['tech_need'] = $item['tech_need'];
                $replyPackData[$key]['unit'] = $item['unit'];
                $replyPackData[$key]['num'] = $item['num'];
                $replyPackData[$key]['unit_price'] = $item['unit_price'];
                $replyPackData[$key]['freight_fee'] = $item['freight_fee'];
                $replyPackData[$key]['tran_days'] = $item['tran_days'];
                $replyPackData[$key]['note'] = $item['note'];
            }

            unset($pack_ids_rev[$item['pack_id']]);
        }
        if($pack_type==2 && !empty($pack_ids_rev)){//如果是总包并且还有包件未报价，返回错误
            $this->succInfo = tool::getSuccInfo(0,'请针对所有包件报价');
            return false;
        }

        $replyPackObj = new M($this->bidReplyPackTable);
        if($replyPackObj->data($replyPackData)->validate($this->replyPackageRules)){
            if(!$replyPackObj->adds()){
                $this->succInfo = tool::getSuccInfo(0,'报价失败');
                return false;
            }
            return true;
        }
        else{
            $this->succInfo = tool::getSuccInfo(0,$replyPackObj->getError());
        }
        return false;



    }












}